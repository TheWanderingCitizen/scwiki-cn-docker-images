# Composer 安全阻止与升级兼容性评估

检查日期：2026-10-22。实施环境：PHP 8.3.36、Composer 2.10.3、MediaWiki 1.39.17。原始评估基线是 MW 1.39.7。

## 应急实施后的结论

完整生产清单和锁文件已生成，完整镜像能够构建。Guzzle、YAML 等存在兼容修复的包已升级；生产解析没有全局关闭安全策略。最终组合、全部版本、源码提交及验证状态见 [升级方案](upgrade-plan.md)、[版本与公告清单](dependency-versions.md)、[验证记录](verification.md)。

保留 MW 1.39 时，SMW 4.2.0 仍有已知漏洞。用户明确批准按公告 ID 临时解除 SMW、EmbedVideo 3.4.3、DPL3 3.5.2 和 WebAuthn 的解析阻止；风险继续显示在审计中。该决定用于应急恢复，不代表漏洞已修复。WebAuthn PKSA-cncr-q695-v65g 尚缺审计详情，需继续核查。

SESP 4 实际回归未能注册自定义属性，回退 3.0.5；SRF 5.2 和 SemanticScribunto 2.3.3 保留。SwiftMailer 6.x API 不兼容通过明确扩展补丁处理。Echo 历史候选使用 MW 1.40 接口而失败，已固定更早提交。后续仍应迁移到受支持的 MW 和 SMW 组合。

以下保留最初评估证据；“当前约束”表示升级前基线，已实施结果以上述链接为准。

## 结论

**不能把当前所有被阻止的依赖无缝更新后直接部署。**

- Symfony YAML 可以在 5.4 分支内进行低风险安全升级。
- Guzzle 可以保留 7.x，但必须同时调整 PSR 接口约束、更新 promises，并验证 HTTP 行为变化。
- PHP_CodeSniffer 本体有 3.x 安全补丁，但其父依赖及 Parsoid 的精确版本约束阻止直接替换。
- Semantic MediaWiki 是主要迁移障碍：保留 MediaWiki 1.39 无法使用当前官方安全版本组合，需要升级核心及配套语义扩展，并迁移数据库和自定义功能。

本次已经修改生产依赖、锁文件并新增隔离业务验证；未修改线上数据库或站点内容。PHP2Shell 修复始终保留。

## 范围与证据

从当前 Dockerfile 构建依赖解析前的 builder 快照，导出下载的核心 composer.json 和源码；在一次性容器中重现 Composer 失败，查询 Packagist 公告与版本元数据，并核对上游版本源码和发行说明。

初始解析的 8 组问题实际对应 **4 个直接受安全公告阻止的包**：`guzzlehttp/guzzle`、`symfony/yaml`、`mediawiki/semantic-media-wiki`、`squizlabs/php_codesniffer`。Shellbox、Apiunto、SESP 和 AWS 出现在错误中主要是依赖链原因，不能把每条错误都当成一个独立漏洞。

核心压缩包自带的版本为 Guzzle 7.4.5、PSR7 2.4.5、promises 1.5.1、YAML 5.4.23。它们不代表线上镜像中实际安装的版本。仓库没有完整应用锁文件；以下安全版本是本次核查时的候选，不是未来持续安全的保证。

## 直接受阻包

| 包 | 当前约束/版本 | 安全升级候选 | 是否可无缝更新 |
|---|---|---|---|
| guzzlehttp/guzzle | 核心精确锁定 7.4.5 | 已知公告最低修复到 7.15.2；建议核验 7.16.0 | 有条件；需连带依赖调整和 HTTP 回归 |
| symfony/yaml | 核心精确锁定 5.4.23 | 5.4.54 | 低风险；需要更改核心精确约束，极深 YAML 会被拒绝 |
| mediawiki/semantic-media-wiki | ~4.0，即允许 4.x | 7.3.1 | 否；需要 MediaWiki ≥1.43 和配套迁移 |
| squizlabs/php_codesniffer | codesniffer 38.0.0 精确要求 3.6.1 | 3.13.6 | 本体可留在 3.x，当前整套依赖不能直接替换 |

### Guzzle 与 HTTP 依赖

7.4.5 命中本次初始解析中的 9 项较新公告。7.15.2 覆盖这些已知修复；7.16.0 是核查时的最新 7.x 候选。没有必要为这次修复切换 Guzzle 8。

Guzzle 7.16.0 要求 `guzzlehttp/psr7 ^2.13.2` 和 `guzzlehttp/promises ^2.6`。PSR7 2.13.2 又要求 `psr/http-message ^1.1 || ^2.0`，与核心精确锁定的 1.0.1 冲突。建议联动候选为：

```text
guzzlehttp/guzzle     7.16.0
guzzlehttp/psr7       2.13.2
guzzlehttp/promises   2.6.0
psr/http-message     1.1
```

保留 http-message 1.x，避免引入 2.x 的接口返回类型迁移。当前打包的 PSR7 已是 2.x；真正跨主版本的是 promises 1.5.1 → 2.6.0。promises 2 删除了 `GuzzleHttp\Promise` 命名空间函数并加入类型声明，扩展调用旧的 `promise_for()`、`all()`、`settle()` 等函数需要迁移到对应类方法。不能仅看 Guzzle 本体仍为 7.x 就认为没有 breaking changes。

安全修复还会收紧 Host/URI 校验、Cookie 域匹配、Cookie 数量/大小、代理认证和重定向 Referer 行为。此前被接受的非规范 URL 或依赖宽松 Cookie 行为的集成可能失败，需要验证 Apiunto、外部 HTTP、S3、代理及认证重定向路径。

依据：[Guzzle 7.16 更新记录](https://raw.githubusercontent.com/guzzle/guzzle/7.16.0/CHANGELOG.md)、[promises 2.6 更新记录](https://raw.githubusercontent.com/guzzle/promises/2.6.0/CHANGELOG.md)、[PSR7 2.13.2 更新记录](https://raw.githubusercontent.com/guzzle/psr7/2.13.2/CHANGELOG.md)。

### Symfony YAML

5.4.54 修复 CVE-2026-109413；5.4.52 包含另外三项本次命中的解析器安全修复。因此只升到 5.4.52 不够。无需跳到 Symfony 6/7/8，尤其当前 Symfony 8.x 的 PHP 要求不适合 PHP 8.3。

5.4.54 的解析 API 保留，新增安全边界会拒绝过深的嵌套或过量的集合别名展开。源码默认嵌套深度和集合别名上限均为 128。正常站点配置风险较低，但超大 YAML 应作为预期行为变化验证。

已用新版本成功解析核心 `resources/lib/foreign-resources.yaml` 和 `docs/config-schema.yaml`；深度 1000 的内联集合会抛出 ParseException。

依据：[Symfony CVE-2026-109413 公告](https://symfony.com/blog/cve-2026-109413-unbounded-recursion-in-the-yaml-inline-lexer-lets-a-single-document-crash-the-process)、[5.4.52 安全发布](https://symfony.com/blog/symfony-5-4-52-released)、[5.4.54 Parser 源码](https://raw.githubusercontent.com/symfony/yaml/v5.4.54/Parser.php)。

### PHP_CodeSniffer 与开发依赖

命中 CVE-2026-67434，安全版本为 3.13.6 或 4.0.2。建议保持 3.x；切到 4.x 会扩大迁移范围。

但当前关系是：

```text
MediaWiki 1.39.7 require-dev
  └─ mediawiki/mediawiki-codesniffer 38.0.0
      └─ squizlabs/php_codesniffer 3.6.1（精确锁定）
```

核查到支持 3.13.6 的候选包括 mediawiki-codesniffer 48.0.1；它要求 `composer/semver ^3.4.2`。同时核心与 Parsoid 0.16.3 精确要求 semver 3.3.2，解析实验确认冲突。仅在根清单把 semver 改到 3.4.2 无法解决 Parsoid 的精确锁定。

38 → 48 的编码规范会新增、修改、删除 sniffs，CI 检查结果可能变化，不能视为无感替换。不要用版本别名伪装兼容性，也不要仅为生产构建强行更新 Parsoid。可选路径是随核心升级同步更新开发工具，或者对旧规范包制作仅放宽 PHPCS 约束的受测补丁。生产构建若要独立解析运行依赖，应生成明确不包含开发工具的生产清单/锁文件，并保留单独的开发检查；单独传 `--no-dev` 不会让 Composer 忽略根 require-dev 的解析冲突。

依据：[PHPCS 安全公告](https://github.com/PHPCSStandards/PHP_CodeSniffer/security/advisories/GHSA-hmqg-cxww-wqhq)、[codesniffer 48.0.1 依赖](https://raw.githubusercontent.com/wikimedia/mediawiki-tools-codesniffer/v48.0.1/composer.json)、[Parsoid 0.16.3 依赖](https://raw.githubusercontent.com/wikimedia/parsoid/v0.16.3/composer.json)。

## Semantic MediaWiki 与配套扩展

SMW 4.x 没有本次核查可用的官方安全补丁版本，升级到 4.2.0 或 5.x 仍不能覆盖当前公告。候选应至少使用 7.3.1：Packagist 当前已收录的公告并非全部最新上游公告，例如上游还发布了修复于 7.3.1 的 GHSA-2gfw-3hj2-wfq5。因此不能只以一次 Composer audit 的结果选择 7.3.0。

SMW 7.3.1 的 extension.json 要求 MediaWiki ≥1.43。这是 MediaWiki 扩展加载器校验，不完全体现在 Composer 依赖解析中；Composer 能选出版本不代表站点能启动。

| 配套包 | 当前范围 | 可评估的现代组合 | 必须注意 |
|---|---|---|---|
| semantic-extra-special-properties | ~3.0 | 5.0.2 | 当前 3.x 限制 SMW 3/4；新版要求 MW ≥1.43、SMW ≥7 |
| semantic-result-formats | ~4.0 | 6.0.0 | 当前 4.x 限制 SMW 3/4；新版要求 MW ≥1.43、SMW ≥7.2、PHP ≥8.2 |
| semantic-scribunto | ~2.2，包含 2.3.3 | 评估 2.3.3 或随新栈迁移到 3.0.0 | 不可把整个 2.x 判成 Composer 不兼容；2.3.3 已去掉旧 SMW 主版本上限，但仍需运行时/API 检查 |
| semantic-drilldown | REL1_39 自定义包 | 与目标核心/SMW 匹配的版本 | Composer 中人工声明的 1.39.0 不能证明与 SMW 7 兼容，需另外选型和测试 |

此组合只是迁移候选，未完成整站兼容认证。主要变化：

- SemanticScribunto 的 `~2.2` 会允许 2.3.3；其 composer.json 没有运行时 SMW 3/4 上限，extension.json 要求 MW ≥1.39、SMW ≥4.1。不能仅凭 2.2.0 的旧约束认定必须改为 3.x。若考虑留在 2.3.3，需要核对仍使用的旧 Scribunto 基类和 SMW 兼容别名并做 Lua 回归；以下查询上限变化特指选择 3.0.0 的方案。[2.3.3 Composer 元数据](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticScribunto/2.3.3/composer.json)、[2.3.3 扩展约束](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticScribunto/2.3.3/extension.json)。

- SMW 5/7 删除历史类和服务 API；旧扩展、自定义 PHP 需要逐项核对。SMW 7 的安装状态由 `.smw.json` 迁移到数据库，表结构和维护流程也有变化。从 4.x 升级不能套用“7.3.0 → 7.3.1 无需迁移”的说明。
- 当前配置的 `$smwgConfigFileDir = '/usr/local/smw'` 应保留至旧安装状态迁移完成。已有 `wfLoadExtension('SemanticMediaWiki')` 后的 `enableSemantics()` 在新版属于弃用调用；常量配置改为字符串不应误说成所有旧常量在 7.x 立即失效。
- **项目内确定的破坏点**：`config/LocalSettings.php` 的 `_LINKSTO` 回调直接查询 `pagelinks.pl_title` 和 `pl_namespace`。这两列仅存在到 MW 1.42；升级到 1.43 必须关联 `linktarget` 获取 `lt_title/lt_namespace`，或改用相应查询服务，否则该功能会产生 SQL 错误。新版 SESP 已提供内置 `_LINKSTO`，但替换自定义回调前必须验证目前仅针对模板/模块命名空间的过滤语义。
- SemanticScribunto 3 的 `mw.smw.ask/getQueryResult` 默认上限由特殊页的 10000 改为内联查询的 500；无效 Lua 参数改为抛错。需检查页面中的 Lua 模块，仓库未包含其内容。
- SRF 6 的 `jqplotseries cursor` 改为 `chartcursor`，部分统计格式的结果修正，`srfFilteredConfig` JS 结构变化；使用这些查询和小工具的页面需要调整。

依据：[SMW 7.3.1 发行说明](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticMediaWiki/7.3.1/docs/releasenotes/RELEASE-NOTES-7.3.1.md)、[SMW 7.0 迁移说明](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticMediaWiki/7.3.1/docs/migration/7.0.md)、[SMW 7.0 发行说明](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticMediaWiki/7.0.0/docs/releasenotes/RELEASE-NOTES-7.0.0.md)、[尚未全部反映在 Packagist 的上游公告示例](https://github.com/SemanticMediaWiki/SemanticMediaWiki/security/advisories/GHSA-2gfw-3hj2-wfq5)、[pagelinks 表结构](https://www.mediawiki.org/wiki/Manual:Pagelinks_table)、[SESP 5.0.2 说明](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticExtraSpecialProperties/5.0.2/RELEASE-NOTES.md)、[SRF 6 说明](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticResultFormats/6.0.0/RELEASE-NOTES.md)、[SemanticScribunto 3 说明](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticScribunto/3.0.0/RELEASE-NOTES.md)。

## 被依赖链牵连的其他包

- **wikimedia/shellbox 3.0.0**：允许 Guzzle ^7.2.0，可以保留版本来适配 7.16.0。不要机械切到 Shellbox 4.x；现代 4.x 还会牵涉 Monolog 3 等升级。
- **starcitizenwiki/apiunto dev-develop**：当前元数据允许 Guzzle ^7.4.0，并没有独立要求必须升级到另一个主版本。由于开发分支浮动且没有锁定提交，仍需端到端检查实际 API/Lua 调用，不能承诺无缝。
- **edwardspec/mediawiki-aws-s3 v0.13.0**：允许 AWS SDK ^3.67，当前可选择安全的较新 3.x。AWS SDK 的公告也会排除旧候选，但初始构建中主要阻碍是核心 Guzzle 7.4.5 锁定。AWS SDK 3.401.5 允许 Guzzle ^7.8.2，能与 7.16.0 共存；不必为此自动切换 S3 扩展 0.14。升级仍需验证 S3 兼容存储的上传、删除、路径及预签名 URL，离线签名测试不能覆盖实际服务行为。

## 最初评估阶段的验证与限制

1. 初始清单解析失败，复现原有安全阻止。
2. 临时将 Guzzle/YAML 更新为 7.16.0/5.4.54：发现 http-message 1.0.1 冲突；改到 1.1 后 HTTP/YAML 约束冲突消失，但 SMW/PHPCS 阻止仍在。
3. 临时将开发规范更新到 48.0.1、semver 更新到 3.4.2：复现 Parsoid 0.16.3 的精确约束冲突。
4. 为单独检查 HTTP/YAML 依赖，在一次性实验清单中移除语义包和根开发依赖；其余包可完成 dry-run。该清单只用于定位依赖关系，不是建议删除站点语义功能。
5. 上述全清单实验还发现 `octfx/webp dev-develop` 要求 `ext-imagick`，而项目只安装 ImageMagick 命令行程序，没有 PHP imagick 扩展。隔离此项环境问题时只使用 `--ignore-platform-req=ext-imagick`，未关闭安全策略。最终构建需补足该扩展。
6. 另用**不忽略平台要求、不关闭安全策略**的最小 HTTP/YAML 清单实际安装 Guzzle 7.16.0、YAML 5.4.54、http-message 1.1、AWS SDK 3.401.5、Shellbox 3.0.0；`composer audit --locked` 返回无已知公告。此结论仅适用于该隔离清单。
7. 该隔离环境通过：核心 MWCallbackStream 与新 PSR7 的类加载及写入、Guzzle 同步请求/异步 Promise、核心两个 YAML 文件解析、超深 YAML 拒绝、S3 离线请求签名、Shellbox Client 加载。

最初评估尚未构建完整应用；后续实施已经完整构建并新增隔离业务回归，最新结果见验证记录。仍未取得真实部署数据库副本、现有站点页面/Lua 和 S3/OIDC 配置；本地通过不等同于整站认证或无 breaking changes。

## 后续平台迁移方向（不阻止已批准的应急例外）

1. 将 PHP 8.3.36 运行时补丁与应用迁移分开处理。若使用既有应用产物紧急重打镜像，需要先取得实际产物并确认路径、依赖和扩展，不能在此仓库凭空生成“原生产锁文件”。
2. 应用维护升级优先选择受支持的 MW 1.43 LTS 安全补丁版本作为评估基线，统一更新各 REL1_39 扩展、Parsoid 和语义组件；目标版本仍需逐扩展验证。
3. 先迁移 `_LINKSTO` SQL、自定义 SMW 扩展接口、Lua 查询和 SRF 页面用法，再在数据库副本上执行核心/SMW 更新并评估耗时与回滚。
4. 生成并保留完整依赖锁文件与开发分支提交，完成 Composer 审计和整站回归后发布。本次保留 MW 1.39/SMW 4，按用户批准的定向例外应急构建；例外不修复漏洞。后续仍需安全回补或迁移，不能靠忽略公告证明安全。
