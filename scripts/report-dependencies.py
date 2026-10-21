"""Generate the complete review tables from committed manifests and locks."""
import json
from pathlib import Path

root = Path(__file__).resolve().parents[1]
def read(path):
    return json.loads((root / path).read_text(encoding='utf-8'))

manifest = read('build/composer.json')
baseline = read('build/baseline-core-versions.json')
extensions = {p['package']: p for p in read('build/extension-inventory.json')}
lines = ['# 完整依赖前后清单', '',
         '旧库版本来自原 MW 1.39.7 发布包的 vendor 清单；扩展旧值是仓库原约束。仓库此前没有锁文件，不能据此推断线上实际版本。', '',
         '新版本和提交均来自生产锁文件。`1.39.0` 是历史自定义包标签，兼容证据应看真实扩展声明及固定 SHA。', '',
         '| 生产包 | 原版本／约束 | 新版本 | 源码提交 | 类型 |', '|---|---|---|---|---|']
for p in sorted(read('build/composer.lock')['packages'], key=lambda p: p['name']):
    name = p['name']
    old = extensions[name]['previous'] if name in extensions else baseline.get(name, '原核心未包含；旧扩展传递版本未锁定')
    sha = p.get('source', {}).get('reference', p.get('dist', {}).get('reference', '—'))
    kind = '直接' if name in manifest['require'] else '间接'
    lines.append(f'| {name} | {old} | {p["version"]} | `{sha}` | {kind} |')
lines += ['', '## 扩展兼容声明', '',
          '打包阶段同时保留 MW 1.39.17 发布包内未由 Composer 管理的扩展，其版本随核心发布包固定。以下是另外解析的全部扩展和皮肤：', '',
          '| 包 | 上游 requires |', '|---|---|']
for name, p in sorted(extensions.items()):
    lines.append(f'| {name} | `{json.dumps(p["requires"], ensure_ascii=False)}` |')
lines += ['', '## 核心发布包内的其他扩展和皮肤', '', '| 名称 | 原发布包版本 | 当前发布包版本 | 路径 |', '|---|---|---|---|']
for p in read('build/bundled-extensions.json'):
    lines.append(f'| {p["name"]} | {p["previous"]} | {p["version"]} | `{p["path"]}` |')
lines += ['', '这些包由经签名验证的 MW 1.39.17 tarball 固定，不额外追踪浮动分支。未自动启用原先禁用的功能。', '',
          '## 独立开发工具', '', '| 包 | 锁定版本 |', '|---|---|']
for p in read('tools/composer.lock')['packages']:
    lines.append(f'| {p["name"]} | {p["version"]} |')
lines += ['', '开发工具不进入生产依赖图：避免新 codesniffer 所需 semver 与旧 Parsoid 精确约束冲突。', '',
          '## 已批准的临时安全例外', '',
          '这些设置仅解除解析阻止，`on-audit: false` 保留审计输出。未知公告继续阻止交付。WebAuthn ID 仅由解析器报告，审计 API 暂未返回详情；不把它解释为已修复或无风险。', '',
          '| 公告 ID | 原因和后续方向 |', '|---|---|']
for advisory, options in manifest['config']['policy']['advisories']['ignore-id'].items():
    lines.append(f'| {advisory} | {options["reason"]} |')
lines += ['', '## 当前审计详情', '', '| 包／公告 | 严重度 | 标题 |', '|---|---|---|']
audit = read('build/audit.json')
for name, advisories in audit['advisories'].items():
    for item in advisories:
        lines.append(f'| {name}: [{item["advisoryId"]}]({item["link"]}) | {item.get("severity", "unknown")} | {item["title"]} |')
lines += ['', '## 弃用包', '', '| 包 | 建议替代 |', '|---|---|']
for name, replacement in audit['abandoned'].items():
    lines.append(f'| {name} | {replacement or "无上游替代建议"} |')
(root / 'docs/dependency-versions.md').write_text('\n'.join(lines) + '\n', encoding='utf-8', newline='\n')
