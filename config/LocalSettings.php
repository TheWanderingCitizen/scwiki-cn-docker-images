<?php
/**
 * Star Citizen Wiki 
 * https://starcitizen.tools
 *
 * MediaWiki settings file
 */

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
  exit;
}

#error_reporting( -1 );
#ini_set( 'display_errors', 1 );
#ini_set( 'display_startup_errors', 1 );

/* DEBUG ONLY */
$wgShowExceptionDetails = true;
#$wgDebugDumpSql = true;
#$wgDebugComments = true;
#Maintenance
#$wgReadOnly = 'Maintenance is underway. Website is on read-only mode';

# General Settings
$wgSitename = $_ENV["SiteName"];
$wgServer = $_ENV["Server"];
# TODO: We should change this to "Star_Citizen_Wiki" at some point
$wgMetaNamespace = "Star_Citizen_Wiki";
# Force HTTPS
$wgForceHTTPS = true;
# Main page is served as the domain root
$wgMainPageIsDomainRoot = true;
# Allow MediaWiki:Citizen.css to load on all pages
$wgAllowSiteCSSOnRestrictedPages = true;
# Enable CJK Fonts
$wgCitizenEnableCJKFonts = true;
# Use HTML5 encoding with minimal escaping
$wgFragmentMode = [ 'html5' ];
# Use Parsoid media HTML structure
$wgParserEnableLegacyMediaDOM = false;
$wgLocaltimezone = "UTC";
$wgMaxShellMemory = 0;
$wgLanguageCode = "zh";

$wgSecretKey = $_ENV["SecretKey"];
$wgUpgradeKey = $_ENV["UpgradeKey"];

$wgAuthenticationTokenVersion = "1";

# InstantCommons allows wiki to use images from https://commons.wikimedia.org
$wgUseInstantCommons = false;

# Database settings
$wgDBtype = $_ENV["DbType"];
$wgDBserver = $_ENV["DbServer"];
$wgDBname = $_ENV["DbName"];
$wgDBuser = $_ENV["DbUser"];
$wgDBpassword = $_ENV["DbPassword"];
$wgDBprefix = "cnwiki_";

## The URL base path to the directory containing the wiki;
## defaults for all runtime URL paths are based off of this.
## For more information on customizing the URLs
## (like /w/index.php/Page_title to /wiki/Page_title) please see:
## https://www.mediawiki.org/wiki/Manual:Short_URL
$wgScriptPath = "";
$wgScriptExtension = "$wgScriptPath/index.php";
$wgRedirectScript   = "$wgScriptPath/redirect.php";
$wgArticlePath = "/$1";
$wgUsePathInfo = true;
	
# Sitemap
$wgSitemapNamespaces = array(0, 6, 12, 14, 3000, 3006, 3008, 3016);

# Cloudflare CDN
# IP range: https://www.cloudflare.com/ips/
$wgUsePrivateIPs = true;
$wgUseCdn = true;
$wgCdnServersNoPurge = [
	'194.195.247.40', # Linode Loadbalancer
	'10.0.0.0/8',
	'173.245.48.0/20',
	'103.21.244.0/22',
	'103.22.200.0/22',
	'103.31.4.0/22',
	'141.101.64.0/18',
	'108.162.192.0/18',
	'190.93.240.0/20',
	'188.114.96.0/20',
	'197.234.240.0/22',
	'198.41.128.0/17',
	'162.158.0.0/15',
	'104.16.0.0/13',
	'104.24.0.0/14',
	'172.64.0.0/13',
	'131.0.72.0/22',
	'2400:cb00::/32',
	'2606:4700::/32',
	'2803:f800::/32',
	'2405:b500::/32',
	'2405:8100::/32',
	'2a06:98c0::/29',
	'2c0f:f248::/32',
	'2405:b500::/32'
];

## Content Security Policy
## Flickr API is required for UploadWizard
## nonces have limited support and removed in MW 1.41
$wgCSPHeader = [
	'useNonces' => false,
	'script-src' => [ 
		'\'self\'',
		'https://citizenwiki.cn',
		'https://new.citizenwiki.cn',
		'https://www.clarity.ms',
		'https://*.clarity.ms',
		'https://t.clarity.ms',
		'https://hm.baidu.com',
		'https://files.citizenwiki.cn',
	],
	'default-src' => [ 
		'\'self\'',
		'https://citizenwiki.cn',
		'https://new.citizenwiki.cn',
		'https://api.flickr.com',
		'https://www.clarity.ms',
		'https://*.clarity.ms',
		'https://t.clarity.ms',
		'https://hm.baidu.com',
		'https://files.citizenwiki.cn',
	],
	'style-src' => [ '\'self\'',  ],
	'object-src' => [ 
		'\'none\'',
	],
];

# Set X-Frame-Options to DENY
$wgBreakFrames = true;

## Cookies policy
## Strict - Cookies for me and not for thee
$wgCookieSameSite = 'Strict';
## Only send over HTTPS
$wgCookieSecure = true;

## Referrer policy
$wgReferrerPolicy = array('strict-origin-when-cross-origin', 'strict-origin');

## Output a canonical meta tag on every page
$wgEnableCanonicalServerLink = true;

# Preconnect to the media subdomain
$wgImagePreconnect = true;

## The URL path to static resources (images, scripts, etc.)
$wgResourceBasePath = $wgScriptPath;

## The URL path to the logo.  Make sure you change this from the default,
## or else you'll overwrite your logo when you upgrade!
$wgLogos = [
	'svg' => "$wgResourceBasePath/resources/assets/sitelogo.svg",
];

# Restore old config to look for favicon.ico until someone look into the redirect issue
#$wgFavicon = '/favicon.svg';
$wgFavicon = false;

## UPO means: this is also a user preference option
$wgEnableEmail = false;
$wgEnableUserEmail = true; # UPO

$wgEmergencyContact = "";
$wgPasswordSender = "";

$wgEnotifUserTalk = false; # UPO
$wgEnotifWatchlist = false; # UPO
$wgEmailAuthentication = true;

#$wgSMTP = [
#  'host' => 'mail.methean.com',
#  'IDHost' => 'starcitizen.tools',
#  'port' => 2525,
#  'auth' => true,
#  'username' => 'no-reply@starcitizen.tools',
#  'password' => $_ENV['SMTP_PASSWORD']
#];

## Allow logged-in users to set a preference whether or not matches 
## in search results should force redirection to that page.
$wgSearchMatchRedirectPreference = true;

# Disable the real name field
$wgHiddenPrefs[] = 'realname';

# Use argon2 to hash user password (MW default: 'pbkdf2')
$wgPasswordDefault = 'argon2';

# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=utf8";

## Set $wgCacheDirectory to a writable directory on the web server
## to make your wiki go slightly faster. The directory should not
## be publically accessible from the web.
$wgCacheDirectory = "$IP/cache";

## Shared memory settings
$wgMainCacheType = 'redis';
$wgSessionCacheType = 'redis';
$wgMemCachedServers = array();

# Extend parser cache to 3 days
$wgParserCacheExpireTime = 259200;

$wgEnableSidebarCache = true;
$wgUseLocalMessageCache = true;

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads = true;
#$wgGenerateThumbnailOnParse = false;
#$wgThumbnailScriptPath = "{$wgScriptPath}/thumb.php";
$wgUseImageMagick = true;
$wgThumbnailEpoch = "20190815000000";
$wgIgnoreImageErrors = true;

$wgMaxImageArea = 6.4e7;

# Gallery settings
$wgGalleryOptions = [
  'mode' => 'packed-overlay', // One of "traditional", "nolines", "packed", "packed-hover", "packed-overlay", "slideshow" (1.28+)
];

## If you want to use image uploads under safe mode,
## create the directories images/archive, images/thumb and
## images/temp, and make them all writable. Then uncomment
## this, if it's not already uncommented:
#$wgHashedUploadDirectory = false;

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
$wgRightsPage = ""; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "https://creativecommons.org/licenses/by-sa/4.0/";
$wgRightsText = "知识共享署名-相同方式共享";
$wgRightsIcon = "$wgResourceBasePath/resources/assets/licenses/cc-by-sa.png";

# The following permissions were set based on your choice in the installer
$wgAllowUserCss = true;

#SVG Support
$wgFileExtensions[] = 'svg';
$wgAllowTitlesInSVG = true;
$wgSVGConverter = 'ImageMagick';

#Open external link in new tab/window
$wgExternalLinkTarget = '_blank';

#Enable native lazyloading
$wgNativeImageLazyLoading = true;

#Fix double redirects after a page move
$wgFixDoubleRedirects = true;

# Redirects Setting
$wgDisplayTitleFollowRedirects = true;
$wgSearchDefaultRedirects = true;

#=============================================== Extension Load ===============================================
#wfLoadExtension( 'AdvancedSearch' );
wfLoadExtension( 'Apiunto' );
wfLoadExtension( 'AWS' );
wfLoadExtension( 'Babel' );
wfLoadExtension( 'CategoryTree' );
wfLoadExtension( 'CheckUser' );
#wfLoadExtension( 'CirrusSearch' );
wfLoadExtension( 'Cite' );
wfLoadExtension( 'CiteThisPage' );
wfLoadExtension( 'Cldr' );
wfLoadExtension( 'CodeEditor' );
wfLoadExtension( 'CodeMirror' );
wfLoadExtension( 'CommonsMetadata' );
wfLoadExtensions([ 'ConfirmEdit', 'ConfirmEdit/QuestyCaptcha' ]);
wfLoadExtension( 'CookieWarning' );
wfLoadExtension( 'Disambiguator' );
wfLoadExtension( 'Discord' );
wfLoadExtension( 'DiscussionTools' );
wfLoadExtension( 'DismissableSiteNotice' );
wfLoadExtension( 'DynamicPageList3' );
#wfLoadExtension( 'DisplayTitle' );
wfLoadExtension( 'Echo' );
wfLoadExtension( 'Elastica' );
wfLoadExtension( 'EmbedVideo' );
wfLoadExtension( 'Gadgets' );
#wfLoadExtension( 'Graph' ); -- Disabled due to security issue
wfLoadExtension( 'ImageMap' );
wfLoadExtension( 'InputBox' );
#wfLoadExtension( 'intersection' ); --20240506 Disabled for testing
wfLoadExtension( 'Interwiki' );
wfLoadExtension( 'JsonConfig' );
wfLoadExtension( 'Linter' );
wfLoadExtension( 'Loops' );
wfLoadExtension( 'MediaSearch' );
wfLoadExtension( 'MultimediaViewer' );
wfLoadExtension( 'MultiPurge' );
wfLoadExtension( 'NativeSvgHandler' );
wfLoadExtension( 'Nuke' );
wfLoadExtension( 'OATHAuth' );
wfLoadExtension( 'OpenIDConnect' );
wfLoadExtension( 'PageImages' );
#wfLoadExtension( 'PageViewInfo' ); -- Disabled with Extension:Plausible
wfLoadExtension( 'ParserFunctions' );
wfLoadExtension( 'PdfHandler' );
wfLoadExtension( 'PictureHtmlSupport' );
wfLoadExtension( 'PluggableAuth' );
#wfLoadExtension( 'Plausible' ); -- Disabled to allocate more resources to MW
wfLoadExtension( 'Poem' );
wfLoadExtension( 'Popups' );
wfLoadExtension( 'Purge' );
#wfLoadExtension( 'RelatedArticles' );
wfLoadExtension( 'Renameuser' );
wfLoadExtension( 'ReplaceText' );
wfLoadExtension( 'RevisionSlider' );
wfLoadExtension( 'RSS' );
wfLoadExtension( 'SandboxLink' );
wfLoadExtension( 'SemanticDrilldown' );
wfLoadExtension( 'SemanticExtraSpecialProperties' );
wfLoadExtension( 'SemanticMediaWiki' );
wfLoadExtension( 'SemanticResultFormats' );
wfLoadExtension( 'SemanticScribunto' );
wfLoadExtension( 'Scribunto' );
wfLoadExtension( 'ShortDescription' );
wfLoadExtension( 'SwiftMailer' );
wfLoadExtension( 'SyntaxHighlight_GeSHi' );
wfLoadExtension( 'TabberNeue' );
wfLoadExtension( 'TemplateData' );
wfLoadExtension( 'TemplateSandbox' );
wfLoadExtension( 'TemplateStyles' );
wfLoadExtension( 'TemplateStylesExtender' );
wfLoadExtension( 'TitleBlacklist' );
wfLoadExtension( 'TextExtracts' );
wfLoadExtension( 'Thanks' );
wfLoadExtension( 'TwoColConflict' );
wfLoadExtension( 'UniversalLanguageSelector' );
#wfLoadExtension( 'UploadWizard' );
wfLoadExtension( 'UserGroups' );
wfLoadExtension( 'Variables' );
wfLoadExtension( 'VisualEditor' );
wfLoadExtension( 'WebP' );
wfLoadExtension( 'WebAuthn' );
wfLoadExtension( 'WikiEditor' );
wfLoadExtension( 'WikiSEO' );

enableSemantics( 'citizenwiki.cn' );
#=============================================== Extension Config ===============================================
# Apiunto 
$wgApiuntoKey = ''; 
$wgApiuntoUrl = 'https://api.star-citizen.wiki';
$wgApiuntoTimeout = '10'; // 5 seconds
$wgApiuntoDefaultLocale = 'zh_CN'; 

# AWS
$wgAWSCredentials = [
  'key' => $_ENV['S3Key'],
  'secret' => $_ENV['S3Secret'],
  'token' => false
];
$wgAWSBucketName = $_ENV["S3BucketName"];
$wgAWSBucketDomain = $_ENV["S3BucketDomain"];
$wgAWSRepoHashLevels = '2';
$wgAWSRepoDeletedHashLevels = '3';
$wgFileBackends['s3']['endpoint'] = $_ENV["S3Endpoint"];
$wgAWSRegion = $_ENV["S3Region"];
$wgAWSBucketTopSubdirectory = ""; # leading slash is required
$wgResponsiveImages = false;

# CirrusSearch
# $wgCirrusSearchIndexBaseName = 'scw_prod';
# $wgSearchType = 'CirrusSearch';
# $wgCirrusSearchUseCompletionSuggester = 'yes';
# $wgCirrusSearchClusters = [
#     'default' => ['elasticsearch-es-elasticsearch.default.svc.cluster.local'],
# ];
# $wgCirrusSearchCompletionSuggesterSubphrases = [
#     'build'  => true,
#     'use' => true,
#     'type' => 'anywords',
#     'limit' => 5,
# ];

# CleanChanges
#$wgCCTrailerFilter = true;
#$wgCCUserFilter = false;
#$wgDefaultUserOptions['usenewrc'] = 1;

# Code Editor
$wgDefaultUserOptions['usebetatoolbar'] = 1; // user option provided by WikiEditor extension

# CookieWarning
$wgCookieWarningEnabled = true;

# ConfirmEdit
#$wgHCaptchaSiteKey = "{$_ENV['HCAPTCHA_SITEKEY']}";
#$wgHCaptchaSecretKey = "{$_ENV['HCAPTCHA_SECRETKEY']}";
$wgCaptchaTriggers['edit'] = true;
$wgCaptchaTriggers['create'] = true;

# Discord
# $wgDiscordWebhookURL = ["{$_ENV['DISCORD_WEBHOOKURL']}"];

# DismissableSiteNotice
$wgDismissableSiteNoticeForAnons = true;

# DynamicPageList3
$wgDplSettings['recursiveTagParse'] = true;
$wgDplSettings['allowUnlimitedResults'] = true;

# Echo
$wgAllowHTMLEmail = true;

# LocalicationUpdate
// $wgLocalisationUpdateDirectory = "$IP/cache";

# MultimediaViewer
$wgMediaViewerEnableByDefault = true;
$wgMediaViewerEnableByDefaultForAnonymous = true;

# MultiPurge
$wgMultiPurgeEnabledServices = [
	'cloudflare'
];
$wgMultiPurgeServiceOrder = [
	'cloudflare'
];
$wgMultiPurgeCloudFlareZoneId = $_ENV["wgMultiPurgeCloudFlareZoneId"];
$wgMultiPurgeCloudflareApiToken = $_ENV["wgMultiPurgeCloudFlareApiToken"];
$wgMultiPurgeStaticPurges = [
  'Load Script' => 'load.php?lang=de&modules=startup&only=scripts&raw=1&skin=citizen'
];
$wgMultiPurgeRunInQueue = true;

# PageImages
$wgPageImagesNamespaces = array( 'NS_MAIN','NS_UPDATE', 'NS_GUIDE', 'NS_COMMLINK', 'NS_ORG' );
$wgPageImagesOpenGraphFallbackImage = "$wgResourceBasePath/resources/assets/sitelogo.svg";

# Parsoid
# Need to load Parsoid explicitly to make Linter work
# @see https://github.com/StarCitizenWiki/WikiDocker/commit/ea149d74daba5cc13594cee57db70dab099e214d
#wfLoadExtension( 'Parsoid', "$IP/vendor/wikimedia/parsoid/extension.json" );
#$wgParsoidSettings = [
#    'useSelser' => true,
#    'linting' => true,
#];
# This belongs to VE but this is more relevant here
#$wgVisualEditorParsoidAutoConfig = false;
#$wgVirtualRestConfig['modules']['parsoid'] = [
#	// URL to the Parsoid instance - use port 8142 if you use the Debian package - the parameter 'URL' was first used but is now deprecated (string)
#	'url' => 'https://starcitizen.tools/rest.php',
#	// Parsoid "domain" (string, optional) - MediaWiki >= 1.26
#	'domain' => 'starcitizen.tools',
#  'restbaseCompat' => false,
#  'timeout' => 30,
#];

# PluggableAuth
$wgPluggableAuth_EnableAutoLogin = false;
$wgPluggableAuth_EnableLocalLogin = true;
$wgOpenIDConnect_MigrateUsersByEmail = true;
$wgOpenIDConnect_MigrateUsersByUserName = true;
$wgOpenIDConnect_SingleLogout = true;
$wgPluggableAuth_Config[] = [
    'plugin' => 'OpenIDConnect',
	'buttonLabelMessage' => '[维修中]42KIT 注册/登录',
    'data' => [
        'providerURL' => $_ENV["OpenIDConnectProviderUrl"],
        'clientID' => $_ENV["OpenIDConnectClientID"],
        'clientsecret' => $_ENV["OpenIDConnectClientSecret"],
    ]
];

# Plausible
#$wgPlausibleDomain = 'https://analytics.starcitizen.tools';
#$wgPlausibleDomainKey = 'starcitizen.tools';
#$wgPlausibleHonorDNT = true;
#$wgPlausibleTrackLoggedIn = true;
#$wgPlausibleTrackOutboundLinks = true;
#$wgPlausibleIgnoredTitles = [ '/Special:*' ];
#$wgPlausibleEnableCustomEvents = true;
#$wgPlausibleTrack404 = true;
#$wgPlausibleTrackSearchInput = true;
#$wgPlausibleTrackEditButtonClicks = true;
#$wgPlausibleTrackCitizenSearchLinks = true;
#$wgPlausibleTrackCitizenMenuLinks = true;
#$wgPlausibleApiKey = "{$_ENV['PLAUSIBLE_APIKEY']}";

# Popups
# Reference Previews are enabled for all users by default
$wgPopupsReferencePreviewsBetaFeature = false;

# Questy Catpcha
$wgCaptchaQuestions = [
  "本站的名字是?" => [ 'citizenwiki', '星际公民中文百科', 'SC中文百科', '公民中文百科' ],
  "开发该游戏的公司名称是什么？" => [ 'cig', 'rsi', 'cloud imperium', 'cloud imperium games', 'robert space industries', 'roberts space industries', '云帝国游戏'],
  "谁是游戏开发商的联合创始人、首席执行官、总监？" => ['chris roberts','chris robert', '克里斯·罗伯特', '克里斯罗伯特', '萝卜'],
  "游戏的单人部分的名称是什么？" => ['squadron 42', 'sq42', 'squadron42', '42中队']
];

# RelatedArticles 
#$wgRelatedArticlesFooterWhitelistedSkins = [ 'citizen' ];
#$wgRelatedArticlesUseCirrusSearchApiUrl = '/api.php';
#$wgRelatedArticlesDescriptionSource = 'wikidata';
#$wgRelatedArticlesUseCirrusSearch = true;
#$wgRelatedArticlesOnlyUseCirrusSearch = true;

# Semantic Mediawiki
# Use Redis to cache SMW query result
$smwgQueryResultCacheType = 'redis';
# Enable tracking and storing of dependencies of embedded queries
$smwgEnabledQueryDependencyLinksStore = true;
# Duplicate query conditions should be removed from computing query results
$smwgQFilterDuplicates = true;
$smwgConfigFileDir = "/usr/local/smw";
$smwgPDefaultType = '_txt';
# Enable SMW in the following namespaces
# Template namespace
$smwgNamespacesWithSemanticLinks[NS_TEMPLATE] = true;
# Module namespace
$smwgNamespacesWithSemanticLinks[828] = true;

# Semantic Extra Special Properties
$sespgUseFixedTables = true;
# Required by Module:DependencyList
$sespgLocalDefinitions['_LINKSTO'] = [
    'id'    => '_LINKSTO',
    'type'  => '_wpg',
    'alias' => 'sesp-property-links-to',
    'desc' => 'sesp-property-links-to-desc',
    'label' => 'Links to',
    'callback'  => static function(\SESP\AppFactory $appFactory, \SMW\DIProperty $property, \SMW\SemanticData $semanticData ) {
        $page = $semanticData->getSubject()->getTitle();

        // The namespaces where the property will be added
        $targetNS = [ 10, 828 ];

        if ( $page === null || !in_array( $page->getNamespace(), $targetNS, true ) ) {
            return;
        }

        /** @var \Wikimedia\Rdbms\DBConnRef $con */
        $con = $appFactory->getConnection();

        $where = [];
        $where[] = sprintf('pl.pl_from = %s', $page->getArticleID() );
        $where[] = sprintf('pl.pl_title != %s', $con->addQuotes( $page->getDBkey() ) );

        if ( !empty( $targetNS ) ) {
            $where[] = sprintf( 'pl.pl_namespace IN (%s)', implode(',', $targetNS ) );
        }

        $res = $con->select(
            [ 'pl' => 'pagelinks', 'page' ],
            [ 'sel_title' => 'pl.pl_title', 'sel_ns' => 'pl.pl_namespace' ],
            $where,
            __METHOD__,
            [ 'DISTINCT' ],
            [ 'page' => [ 'JOIN', 'page_id=pl_from' ] ]
        );

        foreach( $res as $row ) {
            $title = Title::newFromText( $row->sel_title, $row->sel_ns );
            if ( $title !== null && $title->exists() ) {
                $semanticData->addPropertyObjectValue( $property,\SMW\DIWikiPage::newFromTitle( $title ) );
            }
        }
    }
];

$sespgEnabledPropertyList = [
	'_USERREG',
	'_USEREDITCNT',
	'_PAGEIMG',
	'_LINKSTO'
];

# Scribunto
$wgScribuntoDefaultEngine = 'luasandbox';
$wgScribuntoEngineConf['luasandbox']['memoryLimit'] = 50 * 1024 * 1024; # 50 MB
$wgScribuntoEngineConf['luasandbox']['cpuLimit'] = 10; # Seconds

# SyntaxHighlight
# $wgPygmentizePath = '/usr/lib/python3/dist-packages/pygments';

# TemplateStyles
$wgTemplateStylesAllowedUrls = [
  "audio" => [
    "<^https://citizenwiki\\.cn/>",
    "<^https://files\\.citizenwiki\\.cn/>"
  ],
  "image" => [
    "<^https://citizenwiki\\.cn/>",
    "<^https://files\\.citizenwiki\\.cn/>"
  ],
  "svg" => [
    "<^https://citizenwiki\\.cn/[^?#]*\\.svg(?:[?#]|$)>",
    "<^https://files\\.citizenwiki\\.cn/[^?#]*\\.svg(?:[?#]|$)>"
  ],
  "font" => [
    "<^https://citizenwiki\\.cn/>"
  ],
  "namespace" => [
      "<.>"
  ],
  "css" => []
];

# TextExtracts
$wgExtractsRemoveClasses = ['dd','dablink', 'translate', 'figcaption', 'li'];

# TwoColConflict
$wgTwoColConflictBetaFeature = false;

# Universal Language Selector
# Disable language detection as some message fallback are broken
# Copyright notice and footer does not appear
$wgULSLanguageDetection = false;
# Disable IME
$wgULSIMEEnabled = false;

# UploadWizard
$wgApiFrameOptions = 'SAMEORIGIN';
$wgAllowCopyUploads = true;
#$wgCopyUploadsDomains = array( '*.flickr.com', '*.staticflickr.com' );
$wgUploadNavigationUrl = '/Special:UploadWizard';
#$wgUploadWizardConfig = array(
#  'flickrApiKey' => "{$_ENV['FLICKR_APIKEY']}",
#  );
$wgUploadWizardConfig = array(
  'debug' => false,
  'altUploadForm' => 'Special:Upload',
  'fallbackToAltUploadForm' => false,
  'alternativeUploadToolsPage' => false,
  'enableFormData' => true,
  'enabl