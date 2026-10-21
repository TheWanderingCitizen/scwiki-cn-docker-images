# 本地验证记录

日期：2026-10-22（Asia/Shanghai）。全部业务验证使用本机一次性 MariaDB、Redis、Nginx、代码卷；没有接触线上数据库、发布镜像或部署服务。

## 已通过

| 检查 | 结果 |
|---|---|
| 最终完整应用镜像构建 | 成功；包括 MW、vendor、扩展、皮肤和生产配置 |
| MW 下载验证 | 1.39.17 tarball 的 GPG 签名校验成功 |
| Composer validate | 生产 manifest / lock 一致 |
| 完整平台要求 | 通过；没有 --ignore-platform-reqs |
| 生产 installed 清单 | 178 个包的版本、源码引用与锁逐项一致 |
| 核心内其他扩展／皮肤 | 32 个 manifest 与发布清单一致 |
| 再次构建 | 以 --no-cache-filter builder 重新安装全部锁定依赖；178 个版本和源码引用复查一致 |
| 固定扩展输入 | source SHA、dist reference 和 zip URL 的提交一致 |
| 本地 SwiftMailer 兼容补丁 | git apply --reverse --check 成功，确认补丁已进入产物 |
| PHP / FPM | 8.3.36，FPM 配置检查成功；真实 FastCGI 请求确认 fpm-fcgi、有效 UID 33（www-data）和 upload progress 关闭 |
| PECL | APCu 5.1.28、Redis 6.3.0、LuaSandbox 4.1.3、wikidiff2 1.14.2、imagick 3.8.1 实际加载 |
| Pygments | 2.21.0；LocalSettings 显式使用固定路径，SyntaxHighlight 实际渲染通过 |
| 隔离安装与更新 | maintenance/install.php、maintenance/update.php --quick 成功 |
| 业务 | 页面读取、API 本地登录、编辑、Citizen 页面渲染通过 |
| 语义功能 | SMW 写入／查询、自定义 _LINKSTO SQL 回调、SESP 用户注册／编辑计数属性通过 |
| SRF / Lua | SRF 表格、sum 统计、SemanticScribunto mw.smw.ask 通过 |
| 后台任务 | 隔离数据库 runJobs --maxjobs 30 成功；不代表生产积压已清空 |
| HTTP / PSR / YAML | PSR 流读取／rewind、Guzzle 同步／异步请求、YAML round trip 通过 |
| WebP | Imagick 实际编码并验证 RIFF WebP 内容通过 |
| CheckUser / JWT | JWT 7 API 和真实 CheckUser TokenManager encode/decode 通过 |
| SwiftMailer | 实际扩展调用向本地 SMTP sink 发信成功；非 SMTP transport 读取 PHP sendmail_path 通过 |
| S3 | AWS SDK 离线预签名请求包含 X-Amz-Signature，通过 |
| 生产安全审计 | 仅 14 项已登记公告：SMW 8、EmbedVideo 5、DPL3 1；WebAuthn 解析例外 ID 未出现在审计响应中 |
| 审计拒绝路径 | 未批准 ID、错包名、错版本、不完整响应全部被拒绝 |
| 独立开发工具 | 7 个包实际安装、validate、平台检查成功，audit 无已知公告 |
| Git 与生成文件 | staged diff 空白检查、37 个文件的敏感令牌模式扫描及临时产物排除检查通过；PHP / Python / CI YAML 语法通过 |
| Compose 结构 | config --no-env-resolution --quiet 成功；本地没有真实 .env，未验证线上环境值 |

业务脚本最终输出 `ALL ISOLATED INTEGRATION CHECKS PASSED`，默认清理其自建容器／网络／卷。前期保留的调试资源也已清理；完整应用与复查镜像留在本机。

生产锁文件 SHA256：`2726a7728f0684ee5304ae7e01ebedd7a194ac611f423d47cdb22e7fb4c8f765`。
开发工具锁文件 SHA256：`01b63c307d85f02fb87fe8730174931054b2e7c4dcfc40bf2d56dcf4d8aa91cb`。
本地完整镜像：`scwiki-mediawiki:php2shell-fixed`，image ID `sha256:eaa58139f7932c2f73979ebc4b79b99e9486f3b3941d70e79d55991159d1ee84`。这是本地 image ID，不能冒充远程 registry manifest digest。

## 已发现并处理的兼容变化

- Echo 较晚历史候选依赖 MW 1.40 的 PreferencesGetIconHook，无法启动。固定较早源码，维护更新和最终业务回归通过。
- SESP 4.0.0 在此组合中未注册 _LINKSTO；回退 3.0.5 后自定义属性、SQL 回调与用户属性通过。SRF 5.2 可保留。
- RSS、OpenIDConnect、UploadWizard 的上游名称带空格，不能直接当目录名；已固定安装目录与 LocalSettings 对应。
- SwiftMailer 6 删除 newInstance 与 MailTransport；显式扩展补丁及实际 SMTP 回归覆盖这些变化。
- 测试环境使用 HTTP，必须在测试配置关闭 Secure cookie；无密码 Redis 必须用 null，不能发送 AUTH 空字符串。生产配置仍使用原有部署值。
- 原生产 LocalSettings 的外部服务项在本地隔离时排除 AWS、OIDC／PluggableAuth、Discord、MultiPurge，并移除对应 S3 backend 配置。离线 S3 签名验证单独执行。

## 限制与部署前验证

- 真实 OIDC 登录／账户关联、S3 上传／下载／删除／缩略图、真实邮件服务器、Discord／MultiPurge 和外部 Apiunto 接口未验证；需要实际部署配置。
- 没有线上数据库副本和全部现有页面／Lua，不宣称整站无 breaking changes。当前测试是新装隔离站点加维护更新，不等价于真实生产旧 schema 的升级演练。
- Variables、SESP 3、SwiftMailer 在 PHP 8.3 上仍有弃用日志；未因此关闭 error_reporting，也未重写上游核心。需后续替换／迁移。
- 本机 Docker 曾发生虚拟磁盘 I/O 错误并只读，验证暂停；用户恢复 Docker 后重新执行最终构建验证和完整隔离测试通过。故障期间的失败不被计入通过结果。
- CI 工作流已更新并固定 Actions SHA，但尚未在远程 GitHub runner 执行。没有 push、远程发布或线上部署。

部署和回滚必须遵循 [升级方案](upgrade-plan.md)：处理 /var/www 旧卷覆盖代码，备份数据库与卷，替换全部 PHP worker，回滚产物也必须修复 PHP2Shell。
