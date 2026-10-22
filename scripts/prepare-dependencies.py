"""Explicitly refresh pinned extension metadata; never run during image builds."""
import concurrent.futures
import hashlib
import json
from pathlib import Path
import re
import subprocess
import time
import urllib.error
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
CACHE = ROOT / 'build/.cache'
CACHE.mkdir(parents=True, exist_ok=True)


def fetch(url, optional=False):
    cache = CACHE / (hashlib.sha256(url.encode()).hexdigest() + '.json')
    immutable = re.search(r'/[a-f0-9]{40}/', url)
    if cache.exists() and (immutable or time.time() - cache.stat().st_mtime < 900):
        return json.loads(cache.read_text(encoding='utf-8'))
    try:
        with urllib.request.urlopen(urllib.request.Request(url, headers={'User-Agent': 'scwiki-maintenance'}), timeout=40) as response:
            value = json.load(response)
    except urllib.error.HTTPError as error:
        if not optional or error.code != 404:
            raise
        value = None
    cache.write_text(json.dumps(value), encoding='utf-8')
    return value


def refs(url, *patterns):
    result = subprocess.run(['git', 'ls-remote', url, *patterns], check=True, capture_output=True, text=True, timeout=60)
    return [line.split()[0] for line in result.stdout.splitlines()]


settings = (ROOT / 'config/LocalSettings.php').read_text(encoding='utf-8')
directories = {name.lower(): name for name in re.findall(r"wfLoad(?:Extension|Skin)\(\s*'([^']+)'", settings)}
overrides = {'mediawiki/rss': 'RSS', 'mediawiki/openidconnect': 'OpenIDConnect', 'mediawiki/upload-wizard': 'UploadWizard'}


def pin(package):
    name, source = package['name'], package['source'].copy()
    path = source['url'].removesuffix('.git').split('github.com/')[-1]
    reference = source['reference']
    official = path.startswith('wikimedia/')
    if official:
        branch = refs(source['url'], 'refs/heads/REL1_43')
        if branch:
            reference = branch[0]
    if not re.fullmatch('[a-f0-9]{40}', reference):
        # Prefer a real stable tag when the former input floated a branch.
        tagged = refs(source['url'], 'refs/tags/' + package['version'], 'refs/tags/' + package['version'] + '^{}',
                      'refs/tags/v' + package['version'], 'refs/tags/v' + package['version'] + '^{}')
        resolved = tagged or refs(source['url'], 'refs/heads/' + reference, 'refs/tags/' + reference, 'refs/tags/' + reference + '^{}')
        if not resolved:
            raise RuntimeError(f'Unavailable source: {name}: {reference}')
        reference = resolved[-1]
    source['reference'] = reference
    base = f'https://raw.githubusercontent.com/{path}/{reference}/'
    manifest = fetch(base + 'extension.json', True) or fetch(base + 'skin.json', True)
    if not manifest:
        raise RuntimeError('Missing manifest: ' + name)
    minimum = re.search(r'>=\s*(\d+)\.(\d+)', manifest.get('requires', {}).get('MediaWiki', ''))
    if minimum and tuple(map(int, minimum.groups())) > (1, 43):
        raise RuntimeError('Incompatible MediaWiki requirement: ' + name)
    composer = fetch(base + 'composer.json', True) or {}
    # Audit the upstream identity, never a manually invented package alias.
    name = composer.get('name', name)
    metadata = {k: v for k, v in composer.items() if k in ['require', 'autoload', 'extra', 'conflict', 'provide', 'replace', 'license', 'description']}
    version = manifest.get('version')
    if not version or not re.fullmatch(r'\d+\.\d+(?:\.\d+)?(?:[-+][\w.]+)?', version):
        version = 'dev-REL1_43' if official else package['version']
    # A tagged independent release is authoritative even if its manifest lags.
    if not official and re.fullmatch('[a-f0-9]{40}', package['source']['reference']):
        version = package['version']
    metadata.update(name=name, version=version, type=package['type'], source=source,
                    dist={'type': 'zip', 'url': f'https://api.github.com/repos/{path}/zipball/{reference}', 'reference': reference})
    directory = overrides.get(name, directories.get(manifest['name'].lower(), manifest['name']))
    metadata.setdefault('extra', {})['installer-name'] = directory
    inventory = {'package': name, 'version': version, 'manifestVersion': manifest.get('version'), 'directory': directory,
                 'commit': reference, 'requires': manifest.get('requires', {}), 'source': source['url']}
    return metadata, inventory


inputs = json.loads((ROOT / 'dependencies/extensions.json').read_text(encoding='utf-8'))
with concurrent.futures.ThreadPoolExecutor(max_workers=7) as executor:
    results = list(executor.map(pin, inputs))
packages, inventory = zip(*results)
production = fetch('https://raw.githubusercontent.com/wikimedia/mediawiki/1.43.11/composer.json')
for key in ['require-dev', 'autoload-dev', 'scripts', 'extra']:
    production.pop(key, None)
production['version'] = '1.43.11'
production['minimum-stability'] = 'dev'
production['repositories'] = [{'type': 'package', 'package': package} for package in packages]
production['require'].update({package['name']: package['version'] for package in packages})
managed = {entry['directory'].lower() for entry in inventory}
bundled = json.loads((ROOT / 'dependencies/bundled-composer.json').read_text(encoding='utf-8'))
for filename, composer in bundled.items():
    if filename.split('/')[1].lower() in managed:
        continue
    for name, constraint in composer.get('require', {}).items():
        if name == 'composer/installers':
            # ImageMap requires 1.x; Vector requires >1.0.12. 1.12.0
            # satisfies both and is checked again against installed metadata.
            production['require'][name] = '1.12.0'
            continue
        if name in production['require'] and production['require'][name] != constraint:
            raise RuntimeError(f'Explicit constraint intersection required: {filename}: {name}')
        production['require'][name] = constraint
production['require'].update({'guzzlehttp/guzzle': '7.16.0', 'guzzlehttp/psr7': '2.13.2', 'guzzlehttp/promises': '2.6.0',
                              'psr/http-message': '1.1', 'symfony/yaml': '5.4.54', 'aws/aws-sdk-php': '3.401.5'})
production['config'].pop('audit', None)
production['config']['policy'] = {'advisories': {'block': True}}
production['config']['allow-plugins'] = {'composer/installers': True, 'wikimedia/composer-merge-plugin': False}
for path, value in [('build/composer.json', production), ('build/extension-inventory.json', inventory),
                    ('composer.local.json', {'require': {p['name']: p['version'] for p in packages}, 'repositories': production['repositories']})]:
    (ROOT / path).write_text(json.dumps(value, ensure_ascii=False, indent=2) + '\n', encoding='utf-8', newline='\n')
print(f'Prepared {len(packages)} pinned extension/skin inputs, including bundled extension requirements')
