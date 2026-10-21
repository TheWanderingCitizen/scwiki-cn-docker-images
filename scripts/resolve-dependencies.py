"""Refresh pinned MW 1.39 extension metadata; run explicitly, never during image builds."""
import concurrent.futures
import copy
import json
import pathlib
import re
import subprocess
import time
import urllib.request
import urllib.error

ROOT = pathlib.Path(__file__).resolve().parents[1]
OUT = ROOT / 'build'
OUT.mkdir(exist_ok=True)
CACHE = OUT / '.metadata-cache'
CACHE.mkdir(exist_ok=True)

def fetch(url, optional=False):
    key = CACHE / (__import__('hashlib').sha256(url.encode()).hexdigest() + '.json')
    immutable = re.search(r'/[0-9a-f]{40}/', url) is not None
    if key.exists() and (immutable or time.time() - key.stat().st_mtime < 900):
        return json.loads(key.read_text(encoding='utf-8'))
    try:
        with urllib.request.urlopen(urllib.request.Request(url, headers={'User-Agent': 'scwiki-maintenance'}), timeout=40) as r:
            value = json.load(r)
        key.write_text(json.dumps(value), encoding='utf-8')
        return value
    except urllib.error.HTTPError as error:
        if optional and error.code == 404:
            key.write_text('null', encoding='utf-8')
            return None
        raise

def versions(name):
    data = fetch('https://repo.packagist.org/p2/' + name + '.json', True)
    if not data:
        return []
    result, state = [], {}
    for diff in data['packages'][name]:
        state.update(diff)
        state = {k: v for k, v in state.items() if v != '__unset'}
        result.append(copy.deepcopy(state))
    return result

def repo_path(url):
    return url.removesuffix('.git').split('github.com/')[-1]

def compatible(manifest):
    if not manifest:
        return False
    needs = manifest.get('requires', {})
    mw = needs.get('MediaWiki', '')
    minimum = re.search(r'>=\s*(\d+)\.(\d+)', mw)
    if minimum and tuple(map(int, minimum.groups())) > (1, 39):
        return False
    smw = needs.get('extensions', {}).get('SemanticMediaWiki', '')
    minimum = re.search(r'>=\s*(\d+)', smw)
    return not minimum or int(minimum[1]) <= 4

local = json.loads((ROOT / 'composer.local.json').read_text(encoding='utf-8'))
baseline = json.loads(subprocess.check_output(['git', 'show', '795f615:composer.local.json'], cwd=ROOT, text=True))
local['require'].pop('mediawiki/graph', None)  # Already disabled for security; upstream source is unavailable.
for repository in local['repositories']:
    if repository.get('type') == 'package':
        source = repository['package'].get('source', {})
        if source.get('url', '').endswith('/releases.git'):
            source['url'] = source['url'].replace('/releases.git', '.git')
custom = {r['package']['name']: r['package'] for r in local['repositories'] if r.get('type') == 'package'}
expected_names = {name.lower(): name for name in re.findall(r"wfLoad(?:Extension|Skin)\(\s*'([^']+)'", (ROOT/'config/LocalSettings.php').read_text(encoding='utf-8'))}
targets = {'mediawiki/semantic-media-wiki': '4.2.0', 'mediawiki/semantic-extra-special-properties': '3.0.5',
           'mediawiki/semantic-result-formats': '5.2.0', 'mediawiki/semantic-scribunto': '2.3.3'}

def resolve(pair):
    name, constraint = pair
    existing = custom.get(name)
    if name == 'mediawiki/echo':
        # REL1_39 was deleted upstream. The later manifest-compatible candidate
        # uses PreferencesGetIconHook (MW 1.40) and fails to boot on MW 1.39.
        existing = copy.deepcopy(existing)
        existing['source']['reference'] = '4835b6b1cb1261f96f067fa68da1f9a4fbbe94c8'
    official = existing and existing.get('source', {}).get('url', '').startswith('https://github.com/wikimedia/')
    candidates = versions(name) if not official else []
    selected = None
    for item in candidates:
        version = item['version'].lstrip('v')
        if name in targets and version != targets[name]:
            continue
        if not re.fullmatch(r'\d+\.\d+(?:\.\d+)?', version):
            continue
        source = item.get('source', {})
        if 'github.com/' not in source.get('url', ''):
            continue
        path = repo_path(source['url'])
        manifest = fetch(f'https://raw.githubusercontent.com/{path}/{source["reference"]}/extension.json', True)
        if manifest is None:
            manifest = fetch(f'https://raw.githubusercontent.com/{path}/{source["reference"]}/skin.json', True)
        if compatible(manifest):
            selected = item
            break
    if selected is None and existing:
        selected = copy.deepcopy(existing)
        path = repo_path(selected['source']['url'])
        ref = 'REL1_39' if official and name != 'mediawiki/echo' else selected['source']['reference']
        result = subprocess.run(['git', 'ls-remote', selected['source']['url'], ref, 'refs/heads/' + ref, 'refs/tags/' + ref, 'refs/tags/' + ref + '^{}'], capture_output=True, text=True, timeout=60, check=True) if not re.fullmatch('[0-9a-f]{40}', ref) else None
        lines = result.stdout.splitlines() if result else [ref + '\tcommit']
        if not lines:
            # Some EOL branches have been removed upstream. Recover a compatible
            # historical manifest commit, then validate the resulting extension.
            history = fetch(f'https://api.github.com/repos/{path}/commits?path=extension.json&per_page=100')
            for entry in history:
                historical = fetch(f'https://raw.githubusercontent.com/{path}/{entry["sha"]}/extension.json', True)
                if compatible(historical):
                    lines = [entry['sha'] + '\thistorical-compatible-manifest']
                    break
            if not lines:
                raise RuntimeError(f'{name}: no compatible source history for {ref}')
        selected['source']['reference'] = lines[-1].split()[0]
        manifest = fetch(f'https://raw.githubusercontent.com/{path}/{selected["source"]["reference"]}/extension.json', True)
        if manifest is None:
            manifest = fetch(f'https://raw.githubusercontent.com/{path}/{selected["source"]["reference"]}/skin.json', True)
        if not compatible(manifest):
            tags = subprocess.run(['git', 'ls-remote', '--tags', selected['source']['url']], capture_output=True, text=True, timeout=60, check=True)
            tag_commits = {}
            for line in tags.stdout.splitlines():
                commit, tag = line.split()
                tag = tag.removeprefix('refs/tags/').removesuffix('^{}')
                if re.fullmatch(r'v?\d+\.\d+(?:\.\d+)?', tag):
                    tag_commits[tag] = commit
            tagged = [(tuple(map(int, tag.lstrip('v').split('.'))), tag, commit) for tag, commit in tag_commits.items()]
            for _, tag, commit in sorted(tagged, reverse=True):
                for filename in ['extension.json', 'skin.json']:
                    candidate = fetch(f'https://raw.githubusercontent.com/{path}/{commit}/{filename}', True)
                    if compatible(candidate):
                        manifest = candidate
                        selected['version'] = tag.lstrip('v')
                        selected['source']['reference'] = commit
                        break
                if compatible(manifest):
                    break
            if not compatible(manifest):
                history = fetch(f'https://api.github.com/repos/{path}/commits?path=extension.json&per_page=100')
                for entry in history:
                    candidate = fetch(f'https://raw.githubusercontent.com/{path}/{entry["sha"]}/extension.json', True)
                    if compatible(candidate):
                        manifest = candidate
                        selected['source']['reference'] = entry['sha']
                        break
                if not compatible(manifest):
                    raise RuntimeError(f'{name}: no compatible source')
    if selected is None:
        # Named development packages may have no stable releases. Resolve the branch and pin it.
        data = fetch('https://repo.packagist.org/p2/' + name + '~dev.json')
        selected = next(copy.deepcopy(p) for p in data['packages'][name] if p['version'] == constraint)
        path = repo_path(selected['source']['url'])
        manifest = fetch(f'https://raw.githubusercontent.com/{path}/{selected["source"]["reference"]}/extension.json', True)
        if not compatible(manifest):
            raise RuntimeError(f'{name}: development branch incompatible')
    path = repo_path(selected['source']['url'])
    sha = selected['source']['reference']
    source_composer = fetch(f'https://raw.githubusercontent.com/{path}/{sha}/composer.json', True) or {}
    package = {k: v for k, v in source_composer.items() if k in ['require', 'autoload', 'extra', 'conflict', 'provide', 'replace', 'suggest', 'license', 'description']}
    package.update({'name': name, 'version': selected['version'], 'type': selected.get('type', 'mediawiki-extension'),
                    'source': selected['source'], 'dist': {'type': 'zip', 'url': f'https://api.github.com/repos/{path}/zipball/{sha}', 'reference': sha}})
    package.setdefault('extra', {})['installer-name'] = expected_names.get(manifest['name'].lower(), manifest['name'])
    directory_overrides = {'mediawiki/rss': 'RSS', 'wikimedia/open-i-d-connect': 'OpenIDConnect', 'mediawiki/upload-wizard': 'UploadWizard'}
    if name in directory_overrides:
        package['extra']['installer-name'] = directory_overrides[name]
    if name == 'mediawiki/checkuser':
        package['require']['firebase/php-jwt'] = '^7.0'
    if name == 'mediawiki/swiftmailer':
        package['require']['swiftmailer/swiftmailer'] = '^6.3'
    return package, {'package': name, 'previous': baseline['require'].get(name), 'selected': selected['version'], 'commit': sha, 'requires': manifest.get('requires', {})}

with concurrent.futures.ThreadPoolExecutor(max_workers=8) as executor:
    results = list(executor.map(resolve, local['require'].items()))
packages, inventory = zip(*results)
production = fetch('https://raw.githubusercontent.com/wikimedia/mediawiki/1.39.17/composer.json')
production['version'] = '1.39.17'
production.pop('require-dev', None)
production.pop('autoload-dev', None)
production.pop('scripts', None)
production.pop('extra', None)
production['repositories'] = [{'type': 'package', 'package': p} for p in packages]
production['require'].update({p['name']: p['version'] for p in packages})
for library, constraint in list(production['require'].items()):
    if library not in local['require'] and library != 'wikimedia/parsoid' and re.fullmatch(r'\d+\.\d+\.\d+', constraint):
        production['require'][library] = ('~' if constraint.startswith('0.') else '^') + constraint
production['require'].update({'guzzlehttp/guzzle': '7.16.0', 'guzzlehttp/psr7': '2.13.2', 'guzzlehttp/promises': '2.6.0',
                              'psr/http-message': '1.1', 'symfony/yaml': '5.4.54', 'aws/aws-sdk-php': '3.401.5'})
production['config']['allow-plugins'] = {'composer/installers': True, 'wikimedia/composer-merge-plugin': False, 'composer/package-versions-deprecated': True}
ids = ['PKSA-8k3f-637m-ptt7', 'PKSA-rt5h-3dwq-vdnx', 'PKSA-5kcn-7vbr-1pdw', 'PKSA-k3v8-z9j2-2f38', 'PKSA-stqx-crkb-215f', 'PKSA-2trz-n7bz-xg2h', 'PKSA-6xyn-p8dg-1kgj', 'PKSA-q8ty-xz99-jhhj']
ids += ['PKSA-1f7b-5f9b-ggt9', 'PKSA-wg3k-7dyt-r1n5', 'PKSA-17vj-28c7-d53v', 'PKSA-bvqp-6135-khxc', 'PKSA-fnny-x6d4-t1qw']
ids += ['PKSA-5fdy-91p7-w18s', 'PKSA-cncr-q695-v65g']
production['config']['policy'] = {'advisories': {'block': True, 'ignore-id': {i: {'on-block': True, 'on-audit': False, 'reason': 'Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1.'} for i in ids}}}
for advisory in ids[8:13]:
    production['config']['policy']['advisories']['ignore-id'][advisory]['reason'] = 'User-authorized emergency EmbedVideo 3.4.3 exception; fixed EmbedVideo 4.1 requires MW 1.43. Keep visible in audit.'
production['config']['policy']['advisories']['ignore-id'][ids[13]]['reason'] = 'User-authorized emergency DPL3 3.5.2 exception: hidden/suppressed username disclosure; migrate or backport fix.'
production['config']['policy']['advisories']['ignore-id'][ids[14]]['reason'] = 'User-authorized emergency WebAuthn exception. Resolver reports this ID but audit API supplies no details; investigation remains required.'
for filename, value in [('composer.json', production), ('extension-inventory.json', inventory)]:
    (OUT / filename).write_text(json.dumps(value, ensure_ascii=False, indent=2) + '\n', encoding='utf-8', newline='\n')
local['require'] = {p['name']: p['version'] for p in packages}
local['repositories'] = production['repositories']
local.pop('extra', None)
(ROOT / 'composer.local.json').write_text(json.dumps(local, ensure_ascii=False, indent=2) + '\n', encoding='utf-8', newline='\n')
print(f'Resolved {len(packages)} extension/skin packages')
