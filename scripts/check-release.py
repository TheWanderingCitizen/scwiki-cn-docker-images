"""Fail closed until deployment-specific validation has reviewable evidence."""
import json
from pathlib import Path

gates = json.loads((Path(__file__).resolve().parents[1] / 'build/release-gates.json').read_text())
required = ['productionDatabaseRehearsal', 'existingAdministratorTotpOrRecoveryLogin',
            'productionLuaApiuntoAndLargeQueries', 'productionOidcS3SmtpTls', 'backupRestoreAndMatchingRollback']
pending = [name for name in required if gates.get(name) is not True]
if pending or not gates.get('evidence'):
    raise SystemExit('Release blocked: ' + ', '.join(pending or ['reviewable evidence missing']))
print('Deployment-specific release gates satisfied')
