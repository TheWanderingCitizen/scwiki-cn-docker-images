# 完整依赖前后清单

旧库版本来自原 MW 1.39.7 发布包的 vendor 清单；扩展旧值是仓库原约束。仓库此前没有锁文件，不能据此推断线上实际版本。

新版本和提交均来自生产锁文件。`1.39.0` 是历史自定义包标签，兼容证据应看真实扩展声明及固定 SHA。

| 生产包 | 原版本／约束 | 新版本 | 源码提交 | 类型 |
|---|---|---|---|---|
| alpha5130/purge | 2.1.1 | 2.1.1 | `4d162fe732a971d21cd9d3d67593c86c23a505d3` | 直接 |
| aws/aws-crt-php | 原核心未包含；旧扩展传递版本未锁定 | v1.2.7 | `d71d9906c7bb63a28295447ba12e74723bd3730e` | 间接 |
| aws/aws-sdk-php | 原核心未包含；旧扩展传递版本未锁定 | 3.401.5 | `4d304660ba59e185e86a17946c6be6ab07bf8546` | 直接 |
| beberlei/assert | 原核心未包含；旧扩展传递版本未锁定 | v3.3.4 | `f193f4613c7d7fbcee2c05e4daff4061d49c040e` | 间接 |
| brick/math | 原核心未包含；旧扩展传递版本未锁定 | 0.9.3 | `ca57d18f028f84f777b2168cd1911b0dee2343ae` | 间接 |
| composer/installers | 原核心未包含；旧扩展传递版本未锁定 | v1.12.0 | `d20a64ed3c94748397ff5973488761b22f6d3f19` | 间接 |
| composer/semver | 3.3.2 | 3.3.2 | `3953f23262f2bff1919fc82183ad9acb13ff62c9` | 直接 |
| data-values/common | 原核心未包含；旧扩展传递版本未锁定 | 1.2.0 | `b21c2bd3b213d6233a645003df4f88956afc52f4` | 间接 |
| data-values/data-values | 原核心未包含；旧扩展传递版本未锁定 | 3.1.1 | `45aca708da1f7d39c4fca9e6c7373404627e083b` | 间接 |
| data-values/geo | 原核心未包含；旧扩展传递版本未锁定 | 4.6.0 | `ffcbb2a44338e2a75b9596e4a72a6e0dace1035b` | 间接 |
| data-values/interfaces | 原核心未包含；旧扩展传递版本未锁定 | 1.2.0 | `22573cd52a7b37416f28ed6a8d8706543b0d430a` | 间接 |
| data-values/validators | 原核心未包含；旧扩展传递版本未锁定 | 1.0.0 | `83dbac2c5e9442e8fa2119c8b941ecb88156abe8` | 间接 |
| doctrine/deprecations | 原核心未包含；旧扩展传递版本未锁定 | 1.1.6 | `d4fe3e6fd9bb9e72557a19674f44d8ac7db4c6ca` | 间接 |
| doctrine/lexer | 原核心未包含；旧扩展传递版本未锁定 | 2.1.1 | `861c870e8b75f7c8f69c146c7f89cc1c0f1b49b6` | 间接 |
| edwardspec/mediawiki-aws-s3 | v0.13.0 | v0.13.1 | `f85bb579b57f22175ff0cbca7664ad26cacdbb58` | 直接 |
| egulias/email-validator | 原核心未包含；旧扩展传递版本未锁定 | 3.2.6 | `e5997fa97e8790cdae03a9cbd5e78e45e3c7bda7` | 间接 |
| elasticsearch/elasticsearch | 原核心未包含；旧扩展传递版本未锁定 | v7.11.0 | `277cd5e182827c59c23e146a836a30470c0f879d` | 间接 |
| ezimuel/guzzlestreams | 原核心未包含；旧扩展传递版本未锁定 | 4.1.0 | `903161be81e9f497cc42fb7db982404a4e6441b0` | 间接 |
| ezimuel/ringphp | 原核心未包含；旧扩展传递版本未锁定 | 1.4.1 | `b97f46088940671100012482577eeb59f26a13b5` | 间接 |
| fgrosse/phpasn1 | 原核心未包含；旧扩展传递版本未锁定 | v2.5.0 | `42060ed45344789fb9f21f9f1864fc47b9e3507b` | 间接 |
| firebase/php-jwt | 原核心未包含；旧扩展传递版本未锁定 | v7.2.1 | `0501f5729b9e2448516674aff07c7d1727869b8e` | 间接 |
| guzzlehttp/guzzle | 7.4.5 | 7.16.0 | `ffb62ec787050ae4fa9449cdd6b80335db69bda2` | 直接 |
| guzzlehttp/promises | 1.5.1 | 2.6.0 | `3d88c829bd5f13d13847b39067dbf09a60af2154` | 直接 |
| guzzlehttp/psr7 | 2.4.5 | 2.13.2 | `adee536f95101929f262b8c59e8a61472f347d58` | 直接 |
| jeroen/file-fetcher | 原核心未包含；旧扩展传递版本未锁定 | 6.1.1 | `d9fadc0486ef690ce7dd9c6d32d6e5f2857bfd80` | 间接 |
| jumbojett/openid-connect-php | 原核心未包含；旧扩展传递版本未锁定 | v1.0.2 | `f327e7eb0626d55ddb6abc7b7c9e6ad3af4e5d51` | 间接 |
| justinrainbow/json-schema | 5.2.11 | 5.3.4 | `7df70ffaf31d98726801b4bc099e1fbdbe2e5e54` | 直接 |
| league/uri | 原核心未包含；旧扩展传递版本未锁定 | 6.8.0 | `a700b4656e4c54371b799ac61e300ab25a2d1d39` | 间接 |
| league/uri-interfaces | 原核心未包含；旧扩展传递版本未锁定 | 2.3.0 | `00e7e2943f76d8cb50c7dfdc2f6dee356e15e383` | 间接 |
| liuggio/statsd-php-client | v1.0.18 | v1.0.18 | `c42e6d6687b7b2d7683186ec7f4f03351cc3dbca` | 直接 |
| mediawiki/advanced-search | 1.39.0 | 1.39.0 | `07367cad8661906317b0fee57136ebfbb58b57da` | 直接 |
| mediawiki/babel | 2023.12 | 2023.12 | `0cd8f47583fad62e56aea6922d4c89a480b2cf97` | 直接 |
| mediawiki/checkuser | 1.39.0 | 1.39.0 | `11fbd4a0e78c32dbe83307b9a23bc3215c17c667` | 直接 |
| mediawiki/cirrus-search | 1.39.0 | 1.39.0 | `fa1f2403f35cfed1d992f6ed07097e8f824cff7d` | 直接 |
| mediawiki/citizen-skin | 2.16.0 | 2.40.2 | `524b174ab6c0fd6a640894f2f7e9adb740917b0f` | 直接 |
| mediawiki/cldr | 2023.12 | 2026.09 | `65b782dd845e6c4f9459e6350678803bee2b7a67` | 直接 |
| mediawiki/code-mirror | 1.39.0 | 1.39.0 | `6db41acfa9116eef8ed1029bef25898a46a299d1` | 直接 |
| mediawiki/commons-metadata | 1.39.0 | 1.39.0 | `98e8f221f48c168323b76127d920867fc441f674` | 直接 |
| mediawiki/cookie-warning | 1.39.0 | 1.39.0 | `1575b84436437bdc80591af50ac63147f35521ca` | 直接 |
| mediawiki/disambiguator | 1.39.0 | 1.39.0 | `6cef18f808335872801d82f404dea375ac610e0b` | 直接 |
| mediawiki/discord | 1.39.0 | 1.39.0 | `2a560d9acc4af06dcb5f1f7fd9d695f848209160` | 直接 |
| mediawiki/discussion-tools | 1.39.0 | 1.39.0 | `f6b06a030f746f72440983d1a7ddbdef936b825f` | 直接 |
| mediawiki/dismissablesitenotice | 1.39.0 | 1.39.0 | `1472645c7d3f305434131891c9c880f5e15bf6b4` | 直接 |
| mediawiki/echo | 1.39.0 | 1.39.0 | `4835b6b1cb1261f96f067fa68da1f9a4fbbe94c8` | 直接 |
| mediawiki/elastica | 1.39.0 | 1.39.0 | `4fb6424addd70e565c0dbd26d33086d4009550ba` | 直接 |
| mediawiki/http-request | 原核心未包含；旧扩展传递版本未锁定 | 2.0.0 | `1818e0731561621121fcc9da90f244a727ef0989` | 间接 |
| mediawiki/json-config | 1.39.0 | 1.39.0 | `11138226c0e5afa5662ef9380c6230058ed89f97` | 直接 |
| mediawiki/linter | 1.39.0 | 1.39.0 | `82f32ce524537a597b836c6d7e8b0df6766016f8` | 直接 |
| mediawiki/loops | 1.39.0 | 1.39.0 | `00e0c9e35ec13fe5f629ccb7f4d23c5f28033d34` | 直接 |
| mediawiki/mediasearch | 1.39.0 | 1.39.0 | `96e868b1e46aff1b97cb3a94c654a90132fab380` | 直接 |
| mediawiki/nativesvghandler | 1.39.0 | 1.39.0 | `2d5d95f31a5ea5f5e57e7ffa0dd0ce1a0399872c` | 直接 |
| mediawiki/page-view-info | 1.39.0 | 1.39.0 | `82e5a1f8e45e0632a41dbe5fc0a3b73e1ed2a201` | 直接 |
| mediawiki/parser-hooks | 原核心未包含；旧扩展传递版本未锁定 | 1.6.1 | `45660efef737bcf33abbbb12c1ddb049c4e713fe` | 间接 |
| mediawiki/popups | 1.39.0 | 1.39.0 | `95f341e41eac6ca049bf242c0b8ba0ec3d7eb37e` | 直接 |
| mediawiki/related-articles | 1.39.0 | 1.39.0 | `e5431a1c0bc3c8bfcce0b02d965324a51c5e4bc5` | 直接 |
| mediawiki/revisionslider | 1.39.0 | 1.39.0 | `2acd352760c3ab57d2f54b3d4c69b4a27a14f2a0` | 直接 |
| mediawiki/rss | 1.39.0 | 1.39.0 | `2da8200963089d4dbae4c9947dcadebe933ed9ae` | 直接 |
| mediawiki/sandbox-link | 1.39.0 | 1.39.0 | `d153d515c2babbbfefece585d13d86b1857b07e2` | 直接 |
| mediawiki/semantic-drilldown | 1.39.0 | 1.39.0 | `a55c844476bca53e295cd91f62e707bceebfa320` | 直接 |
| mediawiki/semantic-extra-special-properties | ~3.0 | 3.0.5 | `37d3bd4769f14d211a24c49ecc8a11ee1c97cc1c` | 直接 |
| mediawiki/semantic-media-wiki | ~4.0 | 4.2.0 | `0a8af7546b81f6dcab3f336f14023c8e7aea3805` | 直接 |
| mediawiki/semantic-result-formats | ~4.0 | 5.2.0 | `a2682d7841c442e82533360fd0f35e2b40c17d68` | 直接 |
| mediawiki/semantic-scribunto | ~2.2 | 2.3.3 | `af2e61df59f4dca2384636345d182e2897067189` | 直接 |
| mediawiki/shortdescription | 1.39.0 | 1.39.0 | `9a103cce76d43c6f4bcdf314314904da7e951b88` | 直接 |
| mediawiki/swiftmailer | 1.39.0 | 1.39.0 | `531d029701ee8e5b4e131ab78e3efe64fd855d11` | 直接 |
| mediawiki/tabber-neue | 1.39.0 | 2.7.4 | `6e31b391f2d9c38863f959cdf176302252aae188` | 直接 |
| mediawiki/template-styles | 1.39.0 | 1.39.0 | `bc7dd326920dd3342a1931a79b73c3ba1a2f85e0` | 直接 |
| mediawiki/thanks | 1.39.0 | 1.39.0 | `e693b7bdb15d909207336c6380df2050c778bab2` | 直接 |
| mediawiki/twocolconflict | 1.39.0 | 1.39.0 | `3a1ea91df930dff4c50349d9fa2aa88db2ec74b8` | 直接 |
| mediawiki/universal-language-selector | 2023.12 | 2023.12 | `d1f42080b72421b1ae20d50f794c2a04f43932e2` | 直接 |
| mediawiki/upload-wizard | 1.39.0 | 1.39.0 | `70ebc5daf42322ab92443a6e241c6c11c8c06fe1` | 直接 |
| mediawiki/variables | 1.39.0 | 1.39.0 | `3efd415ccdcd1607d54ff771103a1b23333e9245` | 直接 |
| mediawiki/webauthn | 1.39.0 | 1.39.0 | `c28d80d5f2fb09b75f3039aa7a6f3400e5c2831a` | 直接 |
| monolog/monolog | 2.2.0 | 2.11.1 | `bf2403f591c5a431b6e0e1170cbadd3d64499564` | 直接 |
| mtdowling/jmespath.php | 原核心未包含；旧扩展传递版本未锁定 | 2.9.2 | `2157c5e50e813ec6a96c1eed3be7f64a20fb32a8` | 间接 |
| nicmart/tree | 原核心未包含；旧扩展传递版本未锁定 | 0.4.0 | `e992740d9ee7975c3fc324ddaafaf63b21e17704` | 间接 |
| nyholm/dsn | 原核心未包含；旧扩展传递版本未锁定 | 2.0.1 | `9445621b426bac8c0ca161db8cd700da00a4e618` | 间接 |
| octfx/multipurge | dev-master | 1.2.1 | `da1a7cd0728df9c120085fbd55017f99cdcf9cd9` | 直接 |
| octfx/plausible | dev-master | 1.4.0 | `d36aa56da250f8e0ffeaaa8d7bdf9c8ac5d1e220` | 直接 |
| octfx/template-styles-extender | dev-master | 1.2.2 | `5b97d8f4481b03e148782cad0b52ae55457a24ba` | 直接 |
| octfx/webp | dev-develop | 2.0.0 | `7b056bcba8d82893fa2d105606079b326522c4de` | 直接 |
| octfx/wiki-seo | dev-REL1_39 | v2.7.0 | `b204ec4c66f773265495c658484e288d109f4486` | 直接 |
| onoi/blob-store | 原核心未包含；旧扩展传递版本未锁定 | 1.2.1 | `c3e1f15214977e904fc0e91e0480175a464977ce` | 间接 |
| onoi/cache | 原核心未包含；旧扩展传递版本未锁定 | 1.2.0 | `ecc999186aab7c8db411aedd892b2e5fe5a0b422` | 间接 |
| onoi/callback-container | 原核心未包含；旧扩展传递版本未锁定 | 2.0.0 | `cf2f4dda1b2479bc786985fdb5554af528d03e52` | 间接 |
| onoi/event-dispatcher | 原核心未包含；旧扩展传递版本未锁定 | 1.1.0 | `2af64e3997fc59b6d1e1f8f77e65fd6311c37109` | 间接 |
| onoi/message-reporter | 原核心未包含；旧扩展传递版本未锁定 | 1.4.2 | `ead8ef8f2868ccee6881e471295ebbaf8428c96c` | 间接 |
| oojs/oojs-ui | v0.44.5 | v0.44.5 | `49dffe151f28aa1556adca063d4faa82b2634822` | 直接 |
| paragonie/constant_time_encoding | 原核心未包含；旧扩展传递版本未锁定 | v3.1.3 | `d5b01a39b3415c2cd581d3bd3a3575c1ebbd8e77` | 间接 |
| param-processor/param-processor | 原核心未包含；旧扩展传递版本未锁定 | 1.13.1 | `1b697b2e69bd1c47e375d2776b943470330adb9a` | 间接 |
| pear/console_getopt | v1.4.3 | v1.4.3 | `a41f8d3e668987609178c7c4a9fe48fecac53fa0` | 间接 |
| pear/mail | v1.5.1 | v1.6.0 | `4fda3292ac6bf226d8841cfd8ee3cf4e28395c01` | 直接 |
| pear/mail_mime | 1.10.11 | 1.10.13 | `cda56082cc1a3722d6929caa66abfe95b7406e12` | 直接 |
| pear/net_smtp | 1.10.0 | 1.12.2 | `76173b3ee542a683127ab5429612b6ea519db64d` | 直接 |
| pear/net_socket | v1.2.2 | v1.2.2 | `bbe6a12bb4f7059dba161f6ddd43f369c0ec8d09` | 间接 |
| pear/net_url2 | v2.2.2 | v2.2.3 | `c1f2b316ed9b05e881cdb494f7550ddf817c76c8` | 间接 |
| pear/pear-core-minimal | v1.10.11 | v1.10.18 | `c7b55789d01de0ce090d289b73f1bbd6a2f113b1` | 间接 |
| pear/pear_exception | v1.0.2 | v1.0.2 | `b14fbe2ddb0b9f94f5b24cf08783d599f776fff0` | 间接 |
| phpseclib/phpseclib | 原核心未包含；旧扩展传递版本未锁定 | 3.0.57 | `d17e0ddaeaf6f22f7e007cbb437d78792fe2a0e4` | 间接 |
| pleonasm/bloom-filter | 1.0.3 | 1.0.3 | `57c8cf69148fea2327dcf96b21ce50df7ede1dfc` | 间接 |
| psr/container | 1.1.1 | 1.1.1 | `8622567409010282b7aeebe4bb841fe98b58dcaf` | 直接 |
| psr/http-client | 1.0.1 | 1.0.3 | `bb5906edc1c324c9a05aa0873d40117941e5fa90` | 间接 |
| psr/http-factory | 1.0.1 | 1.1.0 | `2b4765fddfe3b508ac62f829e852b1501d3f6e8a` | 间接 |
| psr/http-message | 1.0.1 | 1.1 | `cb6ce4845ce34a8ad9e68117c10ee90a29919eba` | 直接 |
| psr/log | 1.1.4 | 1.1.4 | `d49695b909c3b7628b6289db5479a1c204601f11` | 直接 |
| ralouphie/getallheaders | 3.0.3 | 3.0.3 | `120b605dfeb996808c31b6477290a714d356e822` | 直接 |
| ramsey/collection | 原核心未包含；旧扩展传递版本未锁定 | 2.1.1 | `344572933ad0181accbf4ba763e85a0306a8c5e2` | 间接 |
| ramsey/uuid | 原核心未包含；旧扩展传递版本未锁定 | 4.9.4 | `75d73f48d02797c2c285a7e9f348fadc0102ffe2` | 间接 |
| react/promise | 原核心未包含；旧扩展传递版本未锁定 | v3.3.0 | `23444f53a813a3296c1368bb104793ce8d88f04a` | 间接 |
| ruflin/elastica | 原核心未包含；旧扩展传递版本未锁定 | 7.1.5 | `72a4598544e3f99b5dd8cacb05d009ee75c2a701` | 间接 |
| seld/jsonlint | 原核心未包含；旧扩展传递版本未锁定 | 1.12.1 | `9a90eb5d32d5a500296bf43f946d60246444d5f7` | 间接 |
| serialization/serialization | 原核心未包含；旧扩展传递版本未锁定 | 4.1.0 | `4403fbab83e0187791d35caef4eef4395811e58d` | 间接 |
| spomky-labs/base64url | 原核心未包含；旧扩展传递版本未锁定 | v2.0.4 | `7752ce931ec285da4ed1f4c5aa27e45e097be61d` | 间接 |
| spomky-labs/cbor-php | 原核心未包含；旧扩展传递版本未锁定 | v2.1.0 | `28e2712cfc0b48fae661a48ffc6896d7abe83684` | 间接 |
| starcitizenwiki/apiunto | dev-develop | 2.0.0 | `dcde187199658c0d383335762c5a5163669eac6a` | 直接 |
| starcitizenwiki/embedvideo | dev-develop | 3.4.3 | `5c0f542d29c59fe57b020868093d38e99c3bc95b` | 直接 |
| starcitizenwiki/picture-html-support | dev-master | 1.0.0 | `3a8fca91e24b6efb7b7f33d94cfaf9c5c96d251a` | 直接 |
| swiftmailer/swiftmailer | 原核心未包含；旧扩展传递版本未锁定 | v6.3.0 | `8a5d5072dca8f48460fce2f4131fcc495eec654c` | 间接 |
| symfony/css-selector | 原核心未包含；旧扩展传递版本未锁定 | v5.4.45 | `4f7f3c35fba88146b56d0025d20ace3f3901f097` | 间接 |
| symfony/deprecation-contracts | v2.5.1 | v3.7.1 | `f3202fa1b5097b0af062dc978b32ecf63404e31d` | 间接 |
| symfony/filesystem | 原核心未包含；旧扩展传递版本未锁定 | v7.4.21 | `fd0e3462be6d32a0d130e8423e4940a7688162d8` | 间接 |
| symfony/polyfill-iconv | 原核心未包含；旧扩展传递版本未锁定 | v1.43.0 | `254724d95fe01b679cd97d88786e9623ee00bfb7` | 间接 |
| symfony/polyfill-intl-idn | 原核心未包含；旧扩展传递版本未锁定 | v1.43.0 | `533d68cf99c8f8ee2e8893c59ed59192e92cfd74` | 间接 |
| symfony/polyfill-intl-normalizer | 原核心未包含；旧扩展传递版本未锁定 | v1.43.0 | `ebd57ccc7edb93d632e184efbe7693d2517bab3b` | 间接 |
| symfony/polyfill-php73 | 原核心未包含；旧扩展传递版本未锁定 | v1.37.0 | `0f68c03565dcaaf25a890667542e8bd75fe7e5bb` | 间接 |
| symfony/polyfill-php80 | v1.28.0 | v1.43.0 | `9c6a5d6b01ca51d486c813e9a9c0ed55f208bb74` | 直接 |
| symfony/polyfill-php81 | v1.28.0 | v1.38.1 | `6bfb9c766cacffbc8e118cb87217d08ed84e5cd7` | 直接 |
| symfony/polyfill-php82 | v1.28.0 | v1.43.0 | `843bbe8b2a7934307b749751eede0f111f5d1cfc` | 直接 |
| symfony/polyfill-php83 | v1.28.0 | v1.43.0 | `80ccff923a8d61f73ebe9da0aa94e25232f004ce` | 直接 |
| symfony/process | 原核心未包含；旧扩展传递版本未锁定 | v5.4.54 | `485d7b3f15ae329058d2407c9772c57ed4f564de` | 间接 |
| symfony/yaml | v5.4.23 | v5.4.54 | `f27a7131106e8cb792d434e8faea7f3ffb615263` | 直接 |
| thecodingmachine/safe | 原核心未包含；旧扩展传递版本未锁定 | v1.3.3 | `a8ab0876305a4cdaef31b2350fcb9811b5608dbc` | 间接 |
| universal-omega/dynamic-page-list3 | dev-main | 3.5.2 | `f6a5bb468a6b808a5316d41c6fb6fdb50014ec27` | 直接 |
| web-auth/cose-lib | 原核心未包含；旧扩展传递版本未锁定 | v3.3.12 | `efa6ec2ba4e840bc1316a493973c9916028afeeb` | 间接 |
| web-auth/metadata-service | 原核心未包含；旧扩展传递版本未锁定 | v3.3.12 | `ef40d2b7b68c4964247d13fab52e2fa8dbd65246` | 间接 |
| web-auth/webauthn-lib | 原核心未包含；旧扩展传递版本未锁定 | v3.3.12 | `5ef9b21c8e9f8a817e524ac93290d08a9f065b33` | 间接 |
| wikimedia/assert | v0.5.1 | v0.5.1 | `27c983fddac1197dc37f6a7cec00fc02861529cd` | 直接 |
| wikimedia/at-ease | v2.1.0 | v2.1.0 | `e8ebaa7bb7c8a8395481a05f6dc4deaceab11c33` | 直接 |
| wikimedia/base-convert | v2.0.2 | v2.0.2 | `aa997185e0b42c1f61a11f3e1980cad144175111` | 直接 |
| wikimedia/cdb | 2.0.0 | 2.1.0 | `4da3dd417fa5be38706a5f08411e4366ceab6046` | 直接 |
| wikimedia/cldr-plural-rule-parser | v2.0.0 | v2.0.0 | `83d78cb8018d5c0f66fd6d0efff6a8ae2de92d36` | 直接 |
| wikimedia/common-passwords | v0.4.0 | v0.4.0 | `e16d64009724401d8ab870724322b88d11baa75c` | 直接 |
| wikimedia/composer-merge-plugin | v2.0.1 | v2.1.0 | `a03d426c8e9fb2c9c569d9deeb31a083292788bc` | 直接 |
| wikimedia/css-sanitizer | 原核心未包含；旧扩展传递版本未锁定 | v4.0.1 | `e8cd197f9c47da649fff3dad4c74c3d85276c208` | 间接 |
| wikimedia/cssjanus | 原核心未包含；旧扩展传递版本未锁定 | v2.3.1 | `cf9bcb64add5970c35d71e6d799482116f83d390` | 直接 |
| wikimedia/display-title | 1.39.0 | 1.39.0 | `c94bb8f3b74a2b84bf4971d3e78550b2fce13fff` | 直接 |
| wikimedia/html-formatter | 4.0.3 | 4.1.0 | `c2fdae55da33feb6d44d34ddc66c4b007981bcf2` | 直接 |
| wikimedia/idle-dom | v1.0.0 | v1.0.0 | `631555d5868bf29427cb015906e666faf09abeea` | 间接 |
| wikimedia/ip-utils | 4.0.0 | 5.0.0 | `119e0ed321e967b4f5748a404fa8a3eb5dad4aa3` | 直接 |
| wikimedia/less.php | v3.1.0 | v4.4.1 | `7d66ab6d38af72f80a0c0a3598d7feac04bbc7b7` | 直接 |
| wikimedia/lucene-explain-parser | 原核心未包含；旧扩展传递版本未锁定 | 2.0.0 | `82a90019468290a6009a9f61010bd0ffd7e8fc10` | 间接 |
| wikimedia/minify | 2.3.0 | 2.12.0 | `fdf9a3f31f21e17fddfbe079a9c62bafb96f3265` | 直接 |
| wikimedia/normalized-exception | v1.0.1 | v1.0.1 | `ed9fc13d75f65c80dc1a95d4792658c97fd782e6` | 直接 |
| wikimedia/object-factory | v4.0.0 | v4.0.0 | `20d19657cfbedb1e7bd61c6696e8a16ade232e5c` | 直接 |
| wikimedia/open-i-d-connect | 1.39.0 | 1.39.0 | `9b349a0c4b289074530dcc687067d192e852955e` | 直接 |
| wikimedia/parsoid | v0.16.3 | v0.16.6 | `622dab8dacffd08ec092071ee78499c62e936391` | 直接 |
| wikimedia/php-session-serializer | v2.0.1 | v3.0.2 | `b14ae6e10b31c387299e57822e231484b1b7ad24` | 直接 |
| wikimedia/pluggable-auth | 1.39.0 | 1.39.0 | `f7ba03299b82780ebc792c79780f46b4dc0cec37` | 直接 |
| wikimedia/poem | 1.39.0 | 1.39.0 | `a3eaff7d3608f39148e978afaffedf7f84ba919f` | 直接 |
| wikimedia/purtle | v1.0.8 | v1.0.8 | `8f106f38ff811906853511a934d7f5e86dce3d20` | 直接 |
| wikimedia/relpath | 3.0.0 | 3.0.0 | `b237d203c820cd1000f2c5ecad25de9fa7165612` | 直接 |
| wikimedia/remex-html | 3.0.3 | 3.0.3 | `0997340924fb60a15590fc5001b6091c24fef1a3` | 直接 |
| wikimedia/request-timeout | 1.2.0 | 1.2.0 | `e306a7cb1fb3a1ca3ce93992c56efb62b537c4bc` | 直接 |
| wikimedia/running-stat | v2.1.0 | v2.2.0 | `644c87162e01989cdff07aab3244e54c82a4cdb6` | 直接 |
| wikimedia/scoped-callback | v4.0.0 | v4.0.0 | `dd522a6cfb4c0180b0a39d12fc57046a347c4d81` | 直接 |
| wikimedia/services | 3.0.0 | 3.0.0 | `c63d949c363378a5f435cfd7a81932aa6f712462` | 直接 |
| wikimedia/shellbox | 3.0.0 | 3.0.0 | `6b5abcd4a467b27b5ba4fc28f7730ea5d01367a1` | 直接 |
| wikimedia/templatesandbox | 1.39.0 | 1.39.0 | `fc1143264ec8a683ec10eb348a697bab5c2a31b4` | 直接 |
| wikimedia/textcat | 原核心未包含；旧扩展传递版本未锁定 | 2.0.0 | `dfdfb1c41bb016814a9d4da3aa68b62437b54a82` | 间接 |
| wikimedia/timestamp | v4.1.1 | v4.2.0 | `e7b9e56fe00f3d41283e360f5a81d83ecbea921e` | 直接 |
| wikimedia/usergroups | 1.39.0 | 1.39.0 | `003eac8f360a977d2997702c012e7c9826e07774` | 直接 |
| wikimedia/utfnormal | 3.0.2 | 3.0.2 | `e690d29487a6ee346bcf4cbf5a6fd89170fa2306` | 直接 |
| wikimedia/wait-condition-loop | v2.0.2 | v2.0.2 | `9bb0894e8c5195d43b2f2babbe4cc8f36bd5be0e` | 直接 |
| wikimedia/wikipeg | 2.0.6 | 2.0.6 | `9a92384ae11e1a3b7ecfe0feef1809b1af73889b` | 间接 |
| wikimedia/wrappedstring | v4.0.1 | v4.1.0 | `bf0622c326cd46b373a385ba0bd1ff8d459a7579` | 直接 |
| wikimedia/xmp-reader | 0.8.6 | 0.9.4 | `0c13a18f322c3fdd4013148d5a647c1670ad82d3` | 直接 |
| wikimedia/zest-css | 2.0.2 | 2.0.2 | `423c867462801fda08a1c31009ec19d91a68b410` | 间接 |
| zordius/lightncandy | v1.2.6 | v1.2.6 | `b451f73e8b5c73e62e365997ba3c993a0376b72a` | 直接 |

## 扩展兼容声明

打包阶段同时保留 MW 1.39.17 发布包内未由 Composer 管理的扩展，其版本随核心发布包固定。以下是另外解析的全部扩展和皮肤：

| 包 | 上游 requires |
|---|---|
| alpha5130/purge | `{"MediaWiki": ">= 1.35.0"}` |
| edwardspec/mediawiki-aws-s3 | `{"MediaWiki": ">= 1.35.0"}` |
| mediawiki/advanced-search | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/babel | `{"MediaWiki": ">= 1.37.0"}` |
| mediawiki/checkuser | `{"MediaWiki": ">= 1.39.0", "platform": {"ext-openssl": "*"}}` |
| mediawiki/cirrus-search | `{"MediaWiki": ">= 1.39.0", "extensions": {"Elastica": ">= 6.0.1"}}` |
| mediawiki/citizen-skin | `{"MediaWiki": ">= 1.39.4"}` |
| mediawiki/cldr | `{"MediaWiki": ">= 1.34.0"}` |
| mediawiki/code-mirror | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/commons-metadata | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/cookie-warning | `{"MediaWiki": ">= 1.35.0"}` |
| mediawiki/disambiguator | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/discord | `{"MediaWiki": ">= 1.38.0"}` |
| mediawiki/discussion-tools | `{"MediaWiki": ">= 1.39.0", "extensions": {"VisualEditor": ">= 0.1.2", "Linter": "*"}}` |
| mediawiki/dismissablesitenotice | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/echo | `{"MediaWiki": ">= 1.37.0"}` |
| mediawiki/elastica | `{"MediaWiki": ">= 1.39.0", "platform": {"ext-curl": "*"}}` |
| mediawiki/json-config | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/linter | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/loops | `{"MediaWiki": ">=1.29"}` |
| mediawiki/mediasearch | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/nativesvghandler | `{"MediaWiki": ">= 1.35.0"}` |
| mediawiki/page-view-info | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/popups | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/related-articles | `{"MediaWiki": ">= 1.31.0"}` |
| mediawiki/revisionslider | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/rss | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/sandbox-link | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/semantic-drilldown | `{"MediaWiki": ">= 1.39", "platform": {"php": ">= 7.4"}}` |
| mediawiki/semantic-extra-special-properties | `{"MediaWiki": ">= 1.35", "extensions": {"SemanticMediaWiki": "*"}}` |
| mediawiki/semantic-media-wiki | `{"MediaWiki": ">= 1.35"}` |
| mediawiki/semantic-result-formats | `{"MediaWiki": ">= 1.39", "extensions": {"SemanticMediaWiki": ">= 4.2"}}` |
| mediawiki/semantic-scribunto | `{"MediaWiki": ">= 1.39", "extensions": {"Scribunto": "*", "SemanticMediaWiki": ">= 4.1"}}` |
| mediawiki/shortdescription | `{"MediaWiki": ">= 1.39.4"}` |
| mediawiki/swiftmailer | `{"MediaWiki": ">= 1.29.0"}` |
| mediawiki/tabber-neue | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/template-styles | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/thanks | `{"MediaWiki": ">= 1.39.0", "extensions": {"Echo": "*"}}` |
| mediawiki/twocolconflict | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/universal-language-selector | `{"MediaWiki": ">= 1.37.0"}` |
| mediawiki/upload-wizard | `{"MediaWiki": ">= 1.39.0"}` |
| mediawiki/variables | `{"MediaWiki": ">= 1.29.0"}` |
| mediawiki/webauthn | `{"MediaWiki": ">= 1.39.0", "extensions": {"OATHAuth": ">= 0.4.4"}}` |
| octfx/multipurge | `{"MediaWiki": ">= 1.39.0", "platform": {"php": ">=8.0"}}` |
| octfx/plausible | `{"MediaWiki": ">= 1.39.0", "platform": {"php": ">=8.0"}}` |
| octfx/template-styles-extender | `{"MediaWiki": ">= 1.39.0", "platform": {"php": ">=8.0"}, "extensions": {"TemplateStyles": ">= 1.0"}}` |
| octfx/webp | `{"MediaWiki": ">= 1.39.0", "extensions": {"PictureHtmlSupport": "*"}}` |
| octfx/wiki-seo | `{"MediaWiki": ">= 1.39.0", "platform": {"php": ">=7.4.0"}}` |
| starcitizenwiki/apiunto | `{"MediaWiki": ">= 1.39.0", "extensions": {"Scribunto": "*"}, "platform": {"php": ">=7.3.19"}}` |
| starcitizenwiki/embedvideo | `{"MediaWiki": ">= 1.39.0", "platform": {"php": ">=8.0"}}` |
| starcitizenwiki/picture-html-support | `{"MediaWiki": ">= 1.39.0"}` |
| universal-omega/dynamic-page-list3 | `{"MediaWiki": ">= 1.39.0"}` |
| wikimedia/display-title | `{"MediaWiki": ">= 1.39.0"}` |
| wikimedia/open-i-d-connect | `{"MediaWiki": ">= 1.39.0", "extensions": {"PluggableAuth": ">= 7.0"}}` |
| wikimedia/pluggable-auth | `{"MediaWiki": ">= 1.35.0"}` |
| wikimedia/poem | `{"MediaWiki": ">= 1.39.0"}` |
| wikimedia/templatesandbox | `{"MediaWiki": ">= 1.39.0"}` |
| wikimedia/usergroups | `{"MediaWiki": ">= 1.38.0"}` |

## 核心发布包内的其他扩展和皮肤

| 名称 | 原发布包版本 | 当前发布包版本 | 路径 |
|---|---|---|---|
| Abuse Filter | bundled | bundled | `extensions/AbuseFilter/extension.json` |
| CategoryTree | bundled | bundled | `extensions/CategoryTree/extension.json` |
| Cite | bundled | bundled | `extensions/Cite/extension.json` |
| CiteThisPage | bundled | bundled | `extensions/CiteThisPage/extension.json` |
| CodeEditor | bundled | bundled | `extensions/CodeEditor/extension.json` |
| ConfirmEdit | 1.6.0 | 1.6.0 | `extensions/ConfirmEdit/extension.json` |
| Gadgets | bundled | bundled | `extensions/Gadgets/extension.json` |
| ImageMap | bundled | bundled | `extensions/ImageMap/extension.json` |
| InputBox | 0.3.0 | 0.3.0 | `extensions/InputBox/extension.json` |
| Interwiki | 3.2 | 3.2 | `extensions/Interwiki/extension.json` |
| Math | 3.0.0 | 3.0.0 | `extensions/Math/extension.json` |
| MultimediaViewer | bundled | bundled | `extensions/MultimediaViewer/extension.json` |
| Nuke | bundled | bundled | `extensions/Nuke/extension.json` |
| OATHAuth | 0.5.0 | 0.5.0 | `extensions/OATHAuth/extension.json` |
| PageImages | bundled | bundled | `extensions/PageImages/extension.json` |
| ParserFunctions | 1.6.1 | 1.6.1 | `extensions/ParserFunctions/extension.json` |
| PDF Handler | bundled | bundled | `extensions/PdfHandler/extension.json` |
| Renameuser | bundled | bundled | `extensions/Renameuser/extension.json` |
| Replace Text | 1.7 | 1.7 | `extensions/ReplaceText/extension.json` |
| Scribunto | bundled | bundled | `extensions/Scribunto/extension.json` |
| SecureLinkFixer | bundled | bundled | `extensions/SecureLinkFixer/extension.json` |
| SpamBlacklist | bundled | bundled | `extensions/SpamBlacklist/extension.json` |
| SyntaxHighlight | 2.0 | 2.0 | `extensions/SyntaxHighlight_GeSHi/extension.json` |
| TemplateData | 0.1.2 | 0.1.2 | `extensions/TemplateData/extension.json` |
| TextExtracts | bundled | bundled | `extensions/TextExtracts/extension.json` |
| TitleBlacklist | 1.5.0 | 1.5.0 | `extensions/TitleBlacklist/extension.json` |
| VisualEditor | 0.1.2 | 0.1.2 | `extensions/VisualEditor/extension.json` |
| WikiEditor | 0.5.3 | 0.5.3 | `extensions/WikiEditor/extension.json` |
| MinervaNeue | bundled | bundled | `skins/MinervaNeue/skin.json` |
| MonoBook | bundled | bundled | `skins/MonoBook/skin.json` |
| Timeless | 0.9.1 | 0.9.1 | `skins/Timeless/skin.json` |
| Vector | 1.0.0 | 1.0.0 | `skins/Vector/skin.json` |

这些包由经签名验证的 MW 1.39.17 tarball 固定，不额外追踪浮动分支。未自动启用原先禁用的功能。

## 独立开发工具

| 包 | 锁定版本 |
|---|---|
| composer/semver | 3.5.1 |
| composer/spdx-licenses | 1.5.10 |
| dealerdirect/phpcodesniffer-composer-installer | v1.2.1 |
| mediawiki/mediawiki-codesniffer | v48.0.1 |
| phpcsstandards/phpcsextra | 1.4.0 |
| phpcsstandards/phpcsutils | 1.2.3 |
| squizlabs/php_codesniffer | 3.13.6 |

开发工具不进入生产依赖图：避免新 codesniffer 所需 semver 与旧 Parsoid 精确约束冲突。

## 已批准的临时安全例外

这些设置仅解除解析阻止，`on-audit: false` 保留审计输出。未知公告继续阻止交付。WebAuthn ID 仅由解析器报告，审计 API 暂未返回详情；不把它解释为已修复或无风险。

| 公告 ID | 原因和后续方向 |
|---|---|
| PKSA-8k3f-637m-ptt7 | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-rt5h-3dwq-vdnx | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-5kcn-7vbr-1pdw | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-k3v8-z9j2-2f38 | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-stqx-crkb-215f | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-2trz-n7bz-xg2h | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-6xyn-p8dg-1kgj | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-q8ty-xz99-jhhj | Emergency MW 1.39 / SMW 4.2.0 exception; report in audit; migrate to MW 1.43 and SMW 7.3.1. |
| PKSA-1f7b-5f9b-ggt9 | User-authorized emergency EmbedVideo 3.4.3 exception; fixed EmbedVideo 4.1 requires MW 1.43. Keep visible in audit. |
| PKSA-wg3k-7dyt-r1n5 | User-authorized emergency EmbedVideo 3.4.3 exception; fixed EmbedVideo 4.1 requires MW 1.43. Keep visible in audit. |
| PKSA-17vj-28c7-d53v | User-authorized emergency EmbedVideo 3.4.3 exception; fixed EmbedVideo 4.1 requires MW 1.43. Keep visible in audit. |
| PKSA-bvqp-6135-khxc | User-authorized emergency EmbedVideo 3.4.3 exception; fixed EmbedVideo 4.1 requires MW 1.43. Keep visible in audit. |
| PKSA-fnny-x6d4-t1qw | User-authorized emergency EmbedVideo 3.4.3 exception; fixed EmbedVideo 4.1 requires MW 1.43. Keep visible in audit. |
| PKSA-5fdy-91p7-w18s | User-authorized emergency DPL3 3.5.2 exception: hidden/suppressed username disclosure; migrate or backport fix. |
| PKSA-cncr-q695-v65g | User-authorized emergency WebAuthn exception. Resolver reports this ID but audit API supplies no details; investigation remains required. |

## 当前审计详情

| 包／公告 | 严重度 | 标题 |
|---|---|---|
| mediawiki/semantic-media-wiki: [PKSA-8k3f-637m-ptt7](https://github.com/advisories/GHSA-jr78-w6w5-m8f8) | high | Semantic MediaWiki'a missing authorization in the smwtask API module allows unauthenticated access to admin-only maintenance tasks |
| mediawiki/semantic-media-wiki: [PKSA-rt5h-3dwq-vdnx](https://github.com/advisories/GHSA-9rcc-pmj8-ffhr) | medium | Semantic MediaWiki's Special:FacetedSearch cstate hidden inputs enable reflected XSS (residual of CVE-2025-10354) |
| mediawiki/semantic-media-wiki: [PKSA-5kcn-7vbr-1pdw](https://github.com/advisories/GHSA-q5fm-9mx6-44f4) | medium | Semantic MediaWiki has a query debug output XSS (`DebugFormatter`) |
| mediawiki/semantic-media-wiki: [PKSA-k3v8-z9j2-2f38](https://github.com/advisories/GHSA-hw3m-8j5x-94ff) | medium | Semantic MediaWiki has an open redirect in Special:URIResolver |
| mediawiki/semantic-media-wiki: [PKSA-stqx-crkb-215f](https://github.com/advisories/GHSA-7xv3-gf2g-498h) | medium | Semantic MediaWiki affected by Special:Ask table `sep` parameter reflected XSS |
| mediawiki/semantic-media-wiki: [PKSA-2trz-n7bz-xg2h](https://github.com/advisories/GHSA-59xw-qv23-j3rc) | medium | Semantic MediaWiki has reflected XSS in `Special:SearchByProperty` (`property` and `value` parameters) |
| mediawiki/semantic-media-wiki: [PKSA-6xyn-p8dg-1kgj](https://github.com/advisories/GHSA-3jp5-3h47-28qf) | medium | Semantic MediaWiki has reflected XSS in Special:Ask plain table headers |
| mediawiki/semantic-media-wiki: [PKSA-q8ty-xz99-jhhj](https://github.com/advisories/GHSA-hg8h-557g-q8pp) | high | Semantic MediaWiki vulnerable to stored XSS through wikitext via improper use of non-reserved data attributes |
| starcitizenwiki/embedvideo: [PKSA-1f7b-5f9b-ggt9](https://github.com/advisories/GHSA-v65j-hff3-753c) | high | Mediawiki EmbedVideo Extension has stored XSS via malformed src url with $wgEmbedVideoRequireConsent disabled |
| starcitizenwiki/embedvideo: [PKSA-wg3k-7dyt-r1n5](https://github.com/advisories/GHSA-5c7p-g73q-rpg5) | high | StarCitizenWiki Extension Embed Video: Stored XSS via malformed src url with $wgEmbedVideoRequireConsent enabled |
| starcitizenwiki/embedvideo: [PKSA-17vj-28c7-d53v](https://github.com/advisories/GHSA-7h5p-637f-jfr7) | high | StarCitizenWiki Extension Embed Video: Stored XSS via unsanitized class passed to template |
| starcitizenwiki/embedvideo: [PKSA-bvqp-6135-khxc](https://github.com/advisories/GHSA-c29q-5xm7-5p62) | high | StarCitizenWiki Extension Embed Video: Stored XSS via unsanitized service name in exception text |
| starcitizenwiki/embedvideo: [PKSA-fnny-x6d4-t1qw](https://github.com/advisories/GHSA-4j5h-mvj3-m48v) | high | Star Citizen  EmbedVideo Extension Stored XSS through wikitext caused by usage of non-reserved data attributes |
| universal-omega/dynamic-page-list3: [PKSA-5fdy-91p7-w18s](https://github.com/advisories/GHSA-7pgw-q3qp-6pgq) | high | DynamicPageList3 vulnerability exposes hidden/suppressed usernames |

## 弃用包

| 包 | 建议替代 |
|---|---|
| fgrosse/phpasn1 | 无上游替代建议 |
| swiftmailer/swiftmailer | symfony/mailer |
| web-auth/metadata-service | web-auth/webauthn-lib |
