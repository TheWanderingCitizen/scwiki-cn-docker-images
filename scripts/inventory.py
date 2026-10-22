"""Compare the published 1.43.3 bundle/declarations with the complete recovery lock.

The baseline is repository evidence, not an assertion about deployed volumes.
"""
import json
import subprocess
from pathlib import Path

root = Path(__file__).resolve().parents[1]
baseline = json.loads((root / 'build/baseline-installed.json').read_text())
declared = json.loads(subprocess.check_output(['git', 'show', '7cb68f1:composer.local.json'], cwd=root))
before = {p['name']: p for p in baseline['packages']}
for repository in declared['repositories']:
    package = repository.get('package')
    if package:
        before[package['name']] = {'name': package['name'], 'version': package['version'],
                                  'reference': package.get('source', {}).get('reference'), 'declaredOnly': True}
declared_before = before.copy()
image_before = json.loads((root / 'build/deployed-image-inventory.json').read_text())
before.update({p['name']: p for p in image_before['packages']})
renames = {'chariz/details': 'chariz/mediawiki-extension-details', 'mediawiki/cirrus-search': 'mediawiki/cirrussearch', 'mediawiki/nativesvghandler': 'mediawiki/native-svg-handler', 'universal-omega/dynamic-page-list3': 'universal-omega/dynamic-page-list4', 'wikimedia/openidconnect': 'mediawiki/openidconnect', 'wikimedia/pluggable-auth': 'mediawiki/pluggable-auth', 'alistair3149/apiunto': 'starcitizentools/apiunto'}
for old_name,new_name in renames.items():
    if old_name in before:
        before[new_name] = before.pop(old_name)
after = {p['name']: p for p in json.loads((root / 'build/composer.lock').read_text())['packages']}
rows = []
for name in sorted(before.keys() | after.keys()):
    old, new = before.get(name), after.get(name)
    rows.append({'name': name, 'before': old, 'after': None if new is None else {
        'version': new['version'], 'reference': new.get('source', {}).get('reference'),
        'abandoned': new.get('abandoned', False)},
        'status': 'removed' if new is None else 'added' if old is None else
        'version unchanged; source pinned/verified' if old['version'] == new['version'] else 'updated'})
report = {'baselineCommit': '7cb68f167425b0cbe0aa17c1a7937e314c3f559c',
          'baselineScope': 'Published bundle, repository declarations and inspected deployed image tag main-25.09.12.40; mounted application volume still unverified',
          'productionPackages': rows,
          'developmentPackages': [{'name': p['name'], 'version': p['version'],
                                   'reference': p.get('source', {}).get('reference'), 'abandoned': p.get('abandoned', False)}
                                  for p in json.loads((root / 'tools/composer.lock').read_text())['packages']]}
(root / 'dependencies/inventory.json').write_text(json.dumps(report, indent=2) + '\n', newline='\n')
print(f'Inventoried {len(rows)} before/after entries and {len(report["developmentPackages"])} development packages')
