# MW 1.39 兼容升级与恢复上线方案

实施日期：2026-10-22。保留 MW 1.39；本地构建和验证，不连接线上数据库，不推送镜像、不自动部署。

## 实施结果与选择

- PHP 8.3.36 FPM Bookworm 固定 digest；构建及运行阶段同源。版本门禁、关闭 session 上传进度、FPM 配置检查同时保留。PHP2Shell 两项漏洞依靠 PHP 补丁修复，关闭上传进度是附加措施。
- MW 1.39.7 → 1.39.17，保留上游 tarball GPG 签名验证。Parsoid 固定 0.16.6；核心下载版本和生产 manifest 版本一致。
- 两个 PHP 阶段共同继承安装了 imagick 3.8.1 的基础阶段。PECL APCu 5.1.28、LuaSandbox 4.1.3、Redis 6.3.0、wikidiff2 1.14.2 固定版本；Composer 2.10.3 和扩展安装器固定 digest；Pygments 固定 2.21.0，并在 LocalSettings 显式选择 /usr/local/bin/pygmentize，避免仍然使用旧的内置副本。
- Guzzle 7.16.0、PSR7 2.13.2、promises 2.6.0、http-message 1.1、YAML 5.4.54、AWS SDK 3.401.5。Shellbox 保留 3.x，Monolog 保留 2.x。
- SMW 4.2.0、SESP **3.0.5**、SRF **5.2.0**、SemanticScribunto 2.3.3。SESP 4.0.0 虽声明支持 MW 1.39，但隔离回归中 `_LINKSTO` 未注册；按候选失败规则回退 3.0.5，随后自定义回调和用户属性验证通过。不要把声明兼容当成回归结果。
- S3 扩展 0.13.1、Citizen 2.40.2、Cldr 2026.09、TabberNeue 2.7.4 等兼容候选均固定源码 SHA。完整直接／间接包、旧约束、目标和源码提交见 [完整清单](dependency-versions.md)。原先开发分支没有锁文件，旧版本不能冒充线上真实安装版本。
- `REL1_39` 仍存在的官方扩展固定该分支 SHA。Echo 的旧分支已删除，后来的历史候选实际引用 MW 1.40 的 `PreferencesGetIconHook`，启动失败；回退并固定 2022-10-10 的 `4835b6b1cb1261f96f067fa68da1f9a4fbbe94c8`。这不是最新安全版认证，后续应迁移受支持核心并更新 Echo。
- 已禁用的 Graph 不进入新生产清单：原源码引用不可用。不替代其产品功能，也不启用其他已禁用扩展。
- CheckUser 的 JWT 约束显式覆盖到 ^7.0；使用真实 TokenManager 做加密／解密回归。SwiftMailer 库提升到 6.3，配套 `patches/swiftmailer-6.patch` 替换已删除的 newInstance 工厂，非 SMTP 路径改为使用 PHP 的 `sendmail_path` 创建 SendmailTransport；本地 SMTP sink 验证实际扩展发信。此补丁是明确的本地扩展改动，并非上游版本内容；实际邮件服务交付仍须部署前测试。

## 安全例外与延期

生产 manifest 的 `config.policy.advisories` 保留阻止策略，仅按已批准 ID 解除解析阻止。Composer 2.10.3 中 `on-block: true` 表示解析例外，`on-audit: false` 表示保留在审计输出。镜像构建没有全局关闭策略、版本别名或忽略平台要求。

- SMW 4.2.0：8 个已核查公告，后续方向为受支持 MW ≥1.43 与 SMW ≥7.3.1 的整站迁移或单独审核的安全回补。
- EmbedVideo 3.4.3：5 个 XSS 公告。用户明确批准本次临时放行；官方 4.1.0 要求 MW ≥1.43。
- DPL3 3.5.2：隐藏／被抑制用户名泄露 `PKSA-5fdy-91p7-w18s`。用户明确批准临时放行，需后续升级或审核回补。
- WebAuthn `PKSA-cncr-q695-v65g`：用户明确批准定向解析例外。解析器阻止过该 ID，但审计 API 未提供详情；继续追查，不能宣称已确认无风险。

已知审计快照包含 SMW 8、EmbedVideo 5、DPL3 1，共 14 条；WebAuthn ID 可能不出现在审计快照。完整标题、严重度、链接和 15 个解析例外见清单及 `build/audit.json`。新公告不自动获批。`check-audit.php` 按 ID + 包名 + 精确版本拒绝未知项目，不仅判断 Composer 退出码。审计不能覆盖所有未收录的上游漏洞，MW 1.39 和多项旧组件仍是临时方案。

弃用包保留审计：fgrosse/phpasn1、swiftmailer/swiftmailer、web-auth/metadata-service。替换为维护中的实现需要独立兼容评估，不在本次应急恢复中改写产品功能。

## 解析、锁定与复现

`composer.local.json` 是固定扩展目录；`build/composer.json` 是完整生产清单，已显式纳入每个扩展运行时 require / autoload。取消运行时合并插件和构建期间删除锁文件／无约束 update。上游核心精确依赖通过生成清单显式覆盖，Parsoid 例外保持精确版本，没有模糊替换核心源码。

更新操作与镜像构建分开：

```sh
python scripts/resolve-dependencies.py
docker build --target source -t scwiki-dependency-source:local .
docker run --rm -v "$PWD:/workspace" scwiki-dependency-source:local sh /workspace/scripts/update-lock.sh
python scripts/report-dependencies.py
docker build --pull -t scwiki-mediawiki:php2shell-fixed .
docker run --rm -v "$PWD:/workspace:ro" scwiki-mediawiki:php2shell-fixed sh /workspace/scripts/verify-image.sh
pwsh -NoProfile -File tests/run.ps1
```

解析器仅预筛 MW/SMW 最低声明，最终以完整平台检查和业务回归为准。新维护轮应同时检查真正的 PHP 范围、扩展依赖、版本变更说明；不能依赖自定义 `1.39.0` 标签。

独立开发工具使用 `tools/composer.json` / lock：codesniffer 48.0.1、PHPCS 3.13.6，7 个包；与生产 Parsoid 的 semver 3.3.2 隔离。保持上游核心格式，不为新编码规范重写上游代码。

固定锁文件确保 PHP 包版本和源码提交复现，GPG 校验核心压缩包；Bookworm apt 安全包仍随软件源更新，不声称镜像逐字节复现。两次构建应检查全部 installed version / source reference 与锁一致，并核对本地兼容补丁。没有切换 Debian 大版本。

## 上线前门禁

[本地验证记录](verification.md) 列出已经通过的检查及限制。本地使用隔离数据库，无法代替真实数据库副本、现有页面／Lua、真实 OIDC 和 S3 回归。

在数据库和持久化卷副本上复演升级，记录更新时间、SMW 表更新、任务积压和典型页面耗时。部署配置应补充：OIDC 回调／账户关联／退出、S3 上传／读取／删除／缩略图／路径和签名、外部 APIunto 请求、Discord／MultiPurge、真实邮件服务。未通过的候选应连同其联动依赖回退，但不得回退 PHP2Shell 补丁。

## 处理 /var/www 数据卷覆盖代码

当前 Compose 的 `mediawiki-data:/var/www` 包含旧应用代码。新镜像启动时，已有卷会遮蔽镜像中的核心、vendor、扩展和皮肤，`up --force-recreate` 不能刷新它们。**禁止删除原卷，也禁止直接把旧应用整目录复制到新卷。**

建议用新的、有发布版本名称的应用卷进行原子切换，保留原卷作备份；以下是操作步骤，需由部署负责人在真实环境执行，本次未执行：

1. 冻结写入／开启维护窗口，停止旧 Web 写入入口和全部 job 服务；记录所有副本、旧镜像 digest、应用版本、挂载、数据库连接和任务队列。
2. 生成一致的数据库备份，验证能恢复；对 `mediawiki-data`、其他上传／缓存／配置卷做快照或只读归档。另备份容器内 `/usr/local/smw` 的状态文件及实际 LocalSettings／环境配置，备份应放在受控位置，不能进入 Git。数据库与卷备份必须属于同一维护时间点。
3. 给旧应用重新封装已修复的 PHP 8.3.36，作为回滚产物，并在副本验证。没有这套产物之前，不能把未修复旧镜像当安全回滚方案。
4. 导入本次完整镜像并核对 digest；创建**全新且为空**的 `mediawiki-code-<release>` 卷，由该镜像挂载 `/var/www` 初始化。核对卷内 `includes/Defines.php` 的 MW_VERSION 为 1.39.17，composer.lock 的 SHA256 与发布清单一致，检查已装包与锁一致。
5. 清点旧卷内真实持久数据，只迁移已确认的上传文件／自定义站点资源；S3 对象保持原存储服务。不要迁移旧 core、vendor、extensions、skins、生成的 autoload 文件或旧缓存。`/usr/local/smw` 的状态单独持久化，并在数据库副本验证其升级行为。确认 www-data 有必要的写权限，Nginx 仅需读。
6. 用 Compose override 将 mediawiki 和 mediawiki-web 两个服务的 `/var/www` 同时指向新的同名 release 卷；PHP 用本次完整应用镜像，挂载真实 LocalSettings，并保留真实环境变量。对 override 运行 `docker compose ... config --quiet`，确认最终配置没有旧代码卷覆盖或意外匿名卷。
7. 在备份／副本验证成功后，保持维护窗口，使用新代码在目标数据库执行 `php maintenance/update.php --quick`，检查 SMW 状态和任务队列。需要更新数据库时，变更前再次确认匹配备份可用。不要先让一部分 worker 使用新代码、另一部分使用旧代码。
8. 用 `up --no-build --force-recreate` 切换完整服务，并替换所有 PHP worker、副本、独立 job 容器。OPcache 不验证时间戳，不能只覆盖文件而保留旧 worker。核对每个容器 PHP 8.3.36、MW 1.39.17、锁文件 SHA、源码引用、FPM www-data 和 upload progress Off；确认运行容器实际读取新卷。
9. 执行真实登录／编辑／上传／语义查询／Lua／后台任务／OIDC／S3／邮件回归，确认反向代理缓存和缩略图；再逐步恢复入口并观察错误率和延迟。保留旧卷与备份，勿执行 `docker compose down -v`。

示意 override（先按上述步骤初始化新卷，不直接照抄 release 名）：

```yaml
services:
  mediawiki:
    image: registry.example.invalid/wiki@sha256:REPLACE_WITH_VERIFIED_DIGEST
    volumes:
      - release-code:/var/www
      - smw-state:/usr/local/smw
  mediawiki-web:
    volumes:
      - release-code:/var/www:ro
volumes:
  release-code:
    external: true
    name: mediawiki-code-REPLACE_WITH_RELEASE
  smw-state:
    external: true
    name: mediawiki-smw-REPLACE_WITH_RELEASE
```

Compose 的 `/var/www` 挂载按目标路径合并覆盖；实际 `config` 输出是准据。LocalSettings 的独立 bind mount 仍需检查。新卷初始化应使用发布镜像一次性容器；不能用 Nginx 初始化空代码卷。

## 回滚与提交

若已经执行数据库更新：暂停写入与任务，恢复与旧应用匹配的数据库和持久化快照，切换到**已修复 PHP 的上一套应用产物**及对应旧应用卷，替换全部 worker 并重复关键验证。不要只回退镜像保留新 schema，也不要恢复未修复 PHP 的旧镜像。

最终本地分支 `codex/compatible-dependency-upgrades` 一次提交方案、锁文件、审计、验证脚本与 PHP 修复：`fix: patch PHP2Shell and upgrade compatible MediaWiki dependencies`。不自动 push。CI 的 smw 发布分支在验证通过后沿用原 Docker Hub 标签；本地实施不触发发布。
