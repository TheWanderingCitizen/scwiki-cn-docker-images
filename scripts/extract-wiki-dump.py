"""Extract only citizenwiki from a local multi-database dump; never print rows.

The .gz suffix is not reliable for Coolify downloads. Database import must use
a disposable account granted privileges on the rehearsal database only.
"""
import argparse
import gzip
from pathlib import Path

parser = argparse.ArgumentParser()
parser.add_argument('dump', type=Path)
parser.add_argument('--output', type=Path, default=Path('production-rehearsal/citizenwiki.sql'))
args = parser.parse_args()
with args.dump.open('rb') as probe:
    compressed = probe.read(2) == b'\x1f\x8b'
opener = gzip.open if compressed else open
args.output.parent.mkdir(parents=True, exist_ok=True)
active = False
found = False
tables = 0
with opener(args.dump, 'rb') as source, args.output.open('wb') as destination:
    # Preserve binary column bytes exactly; decoding/re-encoding a dump is unsafe.
    destination.write(b'SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n')
    for line in source:
        if line.startswith(b'-- Current Database:'):
            active = line.strip() == b'-- Current Database: `citizenwiki`'
            found |= active
        if not active or line.startswith((b'CREATE DATABASE', b'USE ')):
            continue
        if line.startswith(b'CREATE TABLE'):
            tables += 1
        if line.startswith(b'/*!50013 DEFINER='):
            # Rehearsal-only view ownership; never import a production root definer.
            line = b'/*!50013 DEFINER=CURRENT_USER SQL SECURITY DEFINER */\n'
        destination.write(line)
    destination.write(b'SET FOREIGN_KEY_CHECKS=1;\n')
if not found or not tables:
    args.output.unlink()
    raise SystemExit('citizenwiki section missing or empty')
print(f'Extracted citizenwiki only: {tables} tables; no database credentials or rows printed')
