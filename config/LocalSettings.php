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

/* DEBUG ONLY */
$wgShowExceptionDetails = true;
$wgDebugDumpSql = true;
$wgDebugComments = true;
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
# Use HTML5 encoding with minimal escaping
$wgFragmentMode = [ 'html5' ];
# Use Parsoid media HTML structure
$wgParserEnableLegacyMediaDOM = false;
$wgLocaltimezone = "UTC";
$wgMaxShellMemory = 0;
$wgLanguageCode = "zh-cn";

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
		'\'self\''
	],
	'default-src' => [ 
		'\'self\'',
		'https://api.flickr.com'
	],
	'style-src' => [ '\'self\'',  ],
	'object-src' => [ '\'none\'' ],
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
wfLoadExtension( 'AdvancedSearch' );
wfLoadExtension( 'Apiunto' );
wfLoadExtension( 'AWS' );
wfLoadExtension( 'Babel' );
wfLoadExtension( 'CategoryTree' );
wfLoadExtension( 'CheckUser' );
wfLoadExtension( 'CirrusSearch' );
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
wfLoadExtension( 'RelatedArticles' );
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
$wgApiuntoDefaultLocale = 'en_EN'; 

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
	'buttonLabelMessage' => '[建议使用]42KIT 注册/登录',
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
  "What the name of site?" => [ 'sct', 'star citizen wiki', 'star citizen tools', 'starcitizen.tools' ],
  "What is the name of the company that is developing the game?" => [ 'cig', 'rsi', 'cloud imperium', 'cloud imperium games', 'robert space industries', 'roberts space industries'],
  "Who is the co-founder, CEO, director of the game's developer" => ['chris roberts','chris robert'],
  "What is the single player part of the game named?" => ['squadron 42', 'sq42', 'squadron42'],
  "Who is the in-lore manufacturer of the <a href='https://starcitizen.tools/Talon'> Talon</a>? " => ['esperia', 'espr', 'esperia (espr)']
];

# RelatedArticles 
$wgRelatedArticlesFooterWhitelistedSkins = [ 'citizen' ];
$wgRelatedArticlesUseCirrusSearchApiUrl = '/api.php';
$wgRelatedArticlesDescriptionSource = 'wikidata';
$wgRelatedArticlesUseCirrusSearch = true;
$wgRelatedArticlesOnlyUseCirrusSearch = true;

# Semantic Mediawiki
# Use Redis to cache SMW query result
$smwgQueryResultCacheType = 'redis';
# Enable tracking and storing of dependencies of embedded queries
$smwgEnabledQueryDependencyLinksStore = true;
# Duplicate query conditions should be removed from computing query results
$smwgQFilterDuplicates = true;
$smwgConfigFileDir = "/usr/local/smw";
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
$wgCopyUploadsDomains = array( '*.flickr.com', '*.staticflickr.com' );
$wgUploadNavigationUrl = '/Special:UploadWizard';
$wgUploadWizardConfig = array(
  'flickrApiKey' => "{$_ENV['FLICKR_APIKEY']}",
  );
$wgUploadWizardConfig = array(
  'debug' => false,
  'altUploadForm' => 'Special:Upload',
  'fallbackToAltUploadForm' => false,
  'alternativeUploadToolsPage' => false,
  'enableFormData' => true,
  'enableMultipleFiles' => true,
  'enableMultiFileSelect' => false,
  'tutorial' => array(
    'skip' => true
  ),
  'maxUploads' => 15,
  'fileExtensions' => $wgFileExtensions,
  'flickrApiUrl' => 'https://api.flickr.com/services/rest/?',
  'licenses' => array(
    # Cloud Imperium license
    'rsilicense' => array(
      # HACK: Add custom license message
      # Edit MediaWiki:mwe-upwiz-license-pd-usgov to the text you wanted
      'msg' => 'mwe-upwiz-license-pd-usgov',
      #'msg' => 'mwe-upwiz-license-rsi',
      'templates' => array('RSIlicense')
    ),
    # CC-BY-NC-SA-2.0 required by Flickr
    # Note that this need to be added to mw.FlickrChecker.js every time it is updated
    'cc-by-nc-sa-2.0' => array(
      'msg' => 'mwe-upwiz-license-cc-by-nc-sa-2.0',
      'templates' => array('cc-by-nc-sa-2.0'),
      #'icons' => array('cc-by','cc-nc','cc-sa'), NC icon is missing
      'url' => '//creativecommons.org/licenses/by-nc-sa/2.0/',
      'languageCodePrefix' => 'deed.'
    ),
    # CC-BY-NC-2.0 required by Flickr
    # Note that this need to be added to mw.FlickrChecker.js every time it is updated
    'cc-by-nc-2.0' => array(
      'msg' => 'mwe-upwiz-license-cc-by-nc-2.0',
      'templates' => array('cc-by-nc-2.0'),
      #'icons' => array('cc-by','cc-nc'), NC icon is missing
      'url' => '//creativecommons.org/licenses/by-nc/2.0/',
      'languageCodePrefix' => 'deed.'
    ),
  ),
  # License selection page
  'licensing' => array(
    'thirdParty' => array(
      'type' => 'or',
      'defaults' => 'rsilicense',
      'licenseGroups' => array(
        array(
	  # Cloud Imperium license
	  # HACK: Add custom license header
          # Edit MediaWiki:mwe-upwiz-license-usgov-head to the text you wanted
          # We have to use this because this message is loaded by UploadWizard and we don't use it
	  'head' => 'mwe-upwiz-license-usgov-head',
          #'head' => 'mwe-upwiz-license-sc-head',
          'licenses' => array(
            'rsilicense'
          )
        ),
        array(
          # This should be a list of all CC licenses we can reasonably expect to find around the web
          'head' => 'mwe-upwiz-license-cc-head',
          'subhead' => 'mwe-upwiz-license-cc-subhead',
          'licenses' => array(
            'cc-by-sa-4.0',
            'cc-by-sa-3.0',
            'cc-by-sa-2.5',
            'cc-by-4.0',
            'cc-by-3.0',
            'cc-by-2.5',
            'cc-zero'
          )
        ),
        array(
          # Flickr still uses CC 2.0
          'head' => 'mwe-upwiz-license-flickr-head',
          'subhead'=> 'mwe-upwiz-license-flickr-subhead',
          'licenses'=> array(
            'cc-by-nc-sa-2.0',
            'cc-by-nc-2.0',
            'cc-by-sa-2.0',
            'cc-by-2.0'
          )
        ),
        array(
          'head' => 'mwe-upwiz-license-custom-head',
          'special' => 'custom',
          'licenses' => array( 'custom' ),
        ),
        array(
          'head' => 'mwe-upwiz-license-none-head',
          'licenses' => array( 'none' )
        ),
      )
    )
  )
);

# Variables
$egVariablesDisabledFunctions = [ 'var_final' ];

# Visual Editor
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgDefaultUserOptions['visualeditor-editor'] = "visualeditor";
$wgDefaultUserOptions['visualeditor-newwikitext'] = 1;
$wgPrefs[] = 'visualeditor-enable';
$wgVisualEditorEnableWikitext = true;
$wgVisualEditorEnableDiffPage = true;
$wgVisualEditorUseSingleEditTab = true;
$wgVisualEditorEnableVisualSectionEditing = true;

# WebP 
$wgWebPAutoFilter = true;
$wgWebPConvertInJobQueue = true;
$wgWebPEnableConvertOnUpload = true;
$wgWebPCompressionQuality = 95;

# WikiEditor
$wgWikiEditorRealtimePreview = true;

# WikiSEO
$wgTwitterSiteHandle = 'ToolsWiki';
$wgWikiSeoDefaultLanguange = 'en-us';
$wgWikiSeoEnableSocialImages = true;
#Disable wgLogo as fallback image
$wgWikiSeoDisableLogoFallbackImage = true;
#TextExtracts description for SEO
$wgWikiSeoEnableAutoDescription = true;
$wgWikiSeoTryCleanAutoDescription = true;

#=============================================== Skin ===============================================

# Set Citizen to the default skin
$wgDefaultSkin = 'citizen';

# Citizen needs to be loaded after extensions to display correct icons for extensions
wfLoadSkin( 'Citizen' );

# Use REST API search endpoint
$wgCitizenSearchGateway = 'mwRestApi';
# Search description source
$wgCitizenSearchDescriptionSource = 'wikidata';
# Number of search results in suggestion
$wgCitizenMaxSearchResults = 10;
# Default to dark theme
$wgCitizenThemeDefault = 'dark';

# Job Queue
/** @see RedisBagOStuff for a full explanation of these options. **/
$wgObjectCaches['redis'] = [
    'class'                => 'RedisBagOStuff',
    'servers'              => [ $_ENV["RedisAddress"] ],
    'connectTimeout'    => 30,
    'persistent'        => false,
    'password'          => $_ENV["RedisPassword"],
    'automaticFailOver' => true,
];

$wgJobTypeConf = [
	'default' => [ 'class' => JobQueueDB::class, 'order' => 'random', 'claimTTL' => 3600 ],
];

# $wgJobQueueAggregator = [
#	'class'       => 'JobQueueAggregatorRedis',
#	'redisServer' => $_ENV["RedisAddress"],
#];

#$wgMessageCacheType = 'redis';
#$wgParserCacheType = 'redis';
#$wgLanguageConverterCacheType = 'redis';

$wgJobRunRate = 0;

#=============================================== Namespaces ===============================================
define("NS_COMMLINK", 3000);
define("NS_COMMLINK_TALK", 3001);
$wgExtraNamespaces[NS_COMMLINK] = "Comm-Link";
$wgExtraNamespaces[NS_COMMLINK_TALK] = "Comm-Link_talk";
$wgNamespacesWithSubpages[NS_COMMLINK] = true;
$wgNamespacesToBeSearchedDefault[NS_COMMLINK] = true;

define("NS_PROJMGMT", 3002);
define("NS_PROJMGMT_TALK", 3003);
$wgExtraNamespaces[NS_PROJMGMT] = "ProjMGMT";
$wgExtraNamespaces[NS_PROJMGMT_TALK] = "ProjMGMT_talk";
$wgNamespacesWithSubpages[NS_PROJMGMT] = true;

define("NS_ISSUE", 3004);
define("NS_ISSUE_TALK", 3005);
$wgExtraNamespaces[NS_ISSUE] = "Issue";
$wgExtraNamespaces[NS_ISSUE_TALK] = "Issue_talk";
$wgNamespacesWithSubpages[NS_ISSUE] = true;

define("NS_GUIDE", 3006);
define("NS_GUIDE_TALK", 3007);
$wgExtraNamespaces[NS_GUIDE] = "Guide";
$wgExtraNamespaces[NS_GUIDE_TALK] = "Guide_talk";
$wgNamespacesWithSubpages[NS_GUIDE] = true;
$wgNamespacesToBeSearchedDefault[NS_GUIDE] = true;

define("NS_ORG", 3008);
define("NS_ORG_TALK", 3009);
$wgExtraNamespaces[NS_ORG] = "ORG";
$wgExtraNamespaces[NS_ORG_TALK] = "ORG_talk";
$wgNamespacesWithSubpages[NS_ORG] = true;

# Deleted NS 3010 - 3015 skipped ID to avoid issues

define("NS_UPDATE", 3016);
define("NS_UPDATE_TALK", 3017);
$wgExtraNamespaces[NS_UPDATE] = "Update";
$wgExtraNamespaces[NS_UPDATE_TALK] = "Update_talk";
$wgNamespacesWithSubpages[NS_UPDATE] = true;

$wgNamespaceProtection[NS_TEMPLATE] = array( 'template-edit' );
$wgNamespaceProtection[NS_COMMLINK] = array( 'commlink-edit' );
$wgNamespaceProtection[NS_PROJMGMT] = array( 'projmgmt-edit' );
$wgNamespaceProtection[NS_ISSUE] = array( 'issue-edit' );
$wgNamespaceProtection[NS_GUIDE] = array( 'guide-edit' );
$wgNamespaceProtection[NS_ORG] = array( 'org-edit' );

# Namespace alias
$wgNamespaceAliases['SCW'] = NS_PROJECT;
# Legacy support
$wgNamespaceAliases['Star_Citizen'] = NS_PROJECT;
$wgNamespaceAliases['SC'] = NS_PROJECT;
$wgNamespaceAliases['ST'] = NS_PROJECT_TALK;
$wgNamespaceAliases['H'] = NS_HELP;
$wgNamespaceAliases['T'] = NS_TEMPLATE;
$wgNamespaceAliases['CAT'] = NS_CATEGORY;
$wgNamespaceAliases['CL'] = NS_COMMLINK;
$wgNamespaceAliases['U'] = NS_UPDATE;

$wgVisualEditorAvailableNamespaces = array(
  NS_MAIN     	=> true,
  NS_USER     	=> true,
  NS_HELP     	=> true,
  NS_PROJECT 	=> true,
  NS_COMMLINK 	=> true,
  NS_PROJMGMT 	=> true,
  NS_ISSUE    	=> true,
  NS_GUIDE    	=> true,
  NS_ORG      	=> true,
  NS_UPDATE     => true
);

$wgContentNamespaces = [ NS_MAIN, NS_GUIDE, NS_COMMLINK, NS_UPDATE ];

#=============================================== Permissions ===============================================
$wgAutopromote = array(
  "autoconfirmed" => array( "&",
    array( APCOND_EDITCOUNT, &$wgAutoConfirmCount ),
    array( APCOND_AGE, &$wgAutoConfirmAge ),
    APCOND_EMAILCONFIRMED,
  ),
  "Trusted" => array( "&",
    array( APCOND_EDITCOUNT, 300),
    array( APCOND_INGROUPS, "Verified"),
  ),
);

#all
$wgGroupPermissions['*']['createaccount'] = true;
$wgGroupPermissions['*']['autocreateaccount'] = true;
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['writeapi'] = true;
$wgGroupPermissions['*']['createtalk'] = false;

#user
$wgGroupPermissions['user']['edit'] = true;
$wgGroupPermissions['user']['purge'] = false;
$wgGroupPermissions['user']['createpage'] = false;
$wgGroupPermissions['user']['createtalk'] = false;
$wgGroupPermissions['user']['minoredit'] = false;
$wgGroupPermissions['user']['move'] = false;
$wgGroupPermissions['user']['movefile'] = false;
$wgGroupPermissions['user']['move-categorypages'] = false;
$wgGroupPermissions['user']['move-rootuserpages'] = false;
$wgGroupPermissions['user']['move-subpages'] = false;
$wgGroupPermissions['user']['reupload'] = false;
$wgGroupPermissions['user']['reupload-own'] = false;
$wgGroupPermissions['user']['guide-edit'] = true;
$wgGroupPermissions['user']['oathauth-enable'] = true;

#ORG Editor
$wgGroupPermissions['ORG-Editor']['org-edit'] = true;

#autoconfirmed
# $wgAutoConfirmAge = 86400*3; // three days
# $wgAutoConfirmCount = 20;
# $wgGroupPermissions['autoconfirmed']['upload_by_url'] = true;
# $wgGroupPermissions['autoconfirmed']['createpage'] = true;
# $wgGroupPermissions['autoconfirmed']['createtalk'] = true;

# Confirmed User
$wgGroupPermissions['确认用户']['browsearchive'] = true;
$wgGroupPermissions['确认用户']['createpage'] = true;
$wgGroupPermissions['确认用户']['createtalk'] = true;
$wgGroupPermissions['确认用户']['delete'] = true;
$wgGroupPermissions['确认用户']['deletedhistory'] = true;
$wgGroupPermissions['确认用户']['deletedtext'] = true;
$wgGroupPermissions['确认用户']['deleterevision'] = true;
$wgGroupPermissions['确认用户']['import'] = true;
$wgGroupPermissions['确认用户']['importupload'] = true;
$wgGroupPermissions['确认用户']['managechangetags'] = true;
$wgGroupPermissions['确认用户']['mergehistory'] = true;
$wgGroupPermissions['确认用户']['minoredit'] = true;
$wgGroupPermissions['确认用户']['move'] = true;
$wgGroupPermissions['确认用户']['move-categorypages'] = true;
$wgGroupPermissions['确认用户']['move-subpages'] = true;
$wgGroupPermissions['确认用户']['movefile'] = true;
$wgGroupPermissions['确认用户']['noratelimit'] = true;
$wgGroupPermissions['确认用户']['read'] = true;
$wgGroupPermissions['确认用户']['reupload'] = true;
$wgGroupPermissions['确认用户']['rollback'] = true;
$wgGroupPermissions['确认用户']['writeapi'] = true;
$wgGroupPermissions['确认用户']['edit'] = true;
$wgGroupPermissions['确认用户']['editinterface'] = true;
$wgGroupPermissions['确认用户']['editmyoptions'] = true;
$wgGroupPermissions['确认用户']['editmyprivateinfo'] = true;
$wgGroupPermissions['确认用户']['editmywatchlist'] = true;
$wgGroupPermissions['确认用户']['upload'] = true;

#verified
$wgGroupPermissions['Verified'] = $wgGroupPermissions['确认用户'];
$wgGroupPermissions['Verified']['skipcaptcha'] = true;
$wgGroupPermissions['Verified']['purge'] = true;
$wgGroupPermissions['Verified']['reupload'] = true;
$wgGroupPermissions['Verified']['reupload-own'] = true;
$wgGroupPermissions['Verified']['minoredit'] = true;

#trusted
$wgGroupPermissions['Trusted'] = $wgGroupPermissions['Verified'];
$wgGroupPermissions['Trusted']['patrol'] = true;
$wgGroupPermissions['Trusted']['move'] = true;
$wgGroupPermissions['Trusted']['movefile'] = true;
$wgGroupPermissions['Trusted']['move-categorypages'] = true;
$wgGroupPermissions['Trusted']['writeapi'] = true;
$wgGroupPermissions['Trusted']['sendemail'] = true;
$wgGroupPermissions['Trusted']['commlink-edit'] = true;
$wgGroupPermissions['Trusted']['issue-edit'] = true;
$wgGroupPermissions['Trusted']['projmgmt-edit'] = true;
$wgGroupPermissions['Trusted']['move-subpages'] = true;
$wgGroupPermissions['Trusted']['template-edit'] = true;

#editor
$wgGroupPermissions['Editor'] = $wgGroupPermissions['Trusted'];
$wgAddGroups['Editor'] = array( 'Verified', 'Translator', 'ORG-Editor' );
$wgGroupPermissions['Editor']['rollback'] = true;
$wgGroupPermissions['Editor']['protect'] = true;
$wgGroupPermissions['Editor']['editprotected'] = true;
$wgGroupPermissions['Editor']['suppressredirect'] = true;
$wgGroupPermissions['Editor']['autopatrol'] = true;
$wgGroupPermissions['Editor']['pagetranslation'] = true;
$wgGroupPermissions['Editor']['delete'] = true;
$wgGroupPermissions['Editor']['bigdelete'] = true;
$wgGroupPermissions['Editor']['deletedhistory'] = true;
$wgGroupPermissions['Editor']['deletedtext'] = true;
$wgGroupPermissions['Editor']['block'] = true;
$wgGroupPermissions['Editor']['undelete'] = true;
$wgGroupPermissions['Editor']['mergehistory'] = true;
$wgGroupPermissions['Editor']['browsearchive'] = true;
$wgGroupPermissions['Editor']['noratelimit'] = true;
$wgGroupPermissions['Editor']['move-rootuserpages'] = true;
$wgGroupPermissions['Editor']['org-edit'] = true;

#sysop
$wgGroupPermissions['sysop'] = $wgGroupPermissions['Editor'];
$wgGroupPermissions['sysop']['userrights'] = true;
$wgGroupPermissions['sysop']['siteadmin'] = true;
$wgGroupPermissions['sysop']['checkuser'] = true;
$wgGroupPermissions['sysop']['checkuser-log'] = true;
$wgGroupPermissions['sysop']['nuke'] = true;
$wgGroupPermissions['sysop']['editinterface'] = true;
$wgGroupPermissions['sysop']['delete'] = true;
$wgGroupPermissions['sysop']['renameuser'] = true;
$wgGroupPermissions['sysop']['import'] = true;
$wgGroupPermissions['sysop']['importupload'] = true;
$wgGroupPermissions['sysop']['smw-admin'] = true;
$wgGroupPermissions['sysop']['smw-pageedit'] = true;
$wgGroupPermissions['sysop']['smw-patternedit'] = true;
$wgGroupPermissions['sysop']['smw-schemaedit'] = true;
// To grant sysops permissions to edit interwiki data
$wgGroupPermissions['sysop']['interwiki'] = true;

#=============================================== Footer ===============================================

$wgFooterIcons = [
    "poweredby" => [
        "mediawiki" => [
            "src" => "$wgResourceBasePath/resources/assets/badge-mediawiki.svg",
            "url" => "https://www.mediawiki.org",
            "alt" => "Powered by MediaWiki",
            "height" => "42",
            "width" => "127",
	],
	"semanticmediawiki" => [
            'src' => "$wgResourceBasePath/resources/assets/badge-semanticmediawiki.svg",
            'url' => 'https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki',
            'alt' => 'Powered by Semantic MediaWiki',
            "height" => "42",
            "width" => "131",
	]
    ],
/*
    "monitoredby" => [
          "wikiapiary" => [
              "src" => "$wgResourceBasePath/resources/assets/badge-wikiapiary.svg",
              "url" => "https://wikiapiary.com/wiki/The_Star_Citizen_Wiki",
              "alt" => "Monitored By Wikiapiary",
              "height" => "54",
              "width" => "95",
          ]
    ],
*/
/*
  "gdprcompliance" => [
        "gdpr" => [
            "src" => "$wgResourceBasePath/resources/assets/badge-gdpr.svg",
            "url" => "https://gdpr.eu",
            "alt" => "GDPR compliant",
	    "height" => "50",
            "width" => "50",
        ]
    ],
*/
    "copyright" => [
        "copyright" => [
        "src" => "$wgResourceBasePath/resources/assets/badge-ccbysa.svg",
        "url" => $wgRightsUrl,
        "alt" => $wgRightsText,
	"height" => "50",
        "width" => "110",
      ]
    ],
    "madeby" => [
          "thecommunity" => [
              "src" => "$wgResourceBasePath/resources/assets/badge-starcitizencommunity.svg",
              "url" => "https://robertsspaceindustries.com",
              "alt" => "Made by the community",
	      "height" => "50",
              "width" => "50",
          ]
    ],
    "partof" => [
        "starcitizentools" => [
            "src" => "$wgResourceBasePath/resources/assets/badge-starcitizentools.svg",
            "url" => "https://starcitizen.tools",
            "alt" => "Part of Star Citizen Tools",
	    "height" => "50",
            "width" => "50",
        ]
    ]
];

# Add links to footer
$wgHooks['SkinAddFooterLinks'][] = function ( $sk, $key, &$footerlinks ) {
	$rel = 'nofollow noreferrer noopener';

	if ( $key === 'places' ) {
		$footerlinks['cookiestatement'] = Html::element(
			'a',
			[ 
				'href' => $sk->msg( 'cookiestatementpage' )->escaped(),
				'title' => $sk->msg( 'cookiestatementpage' )->text()
			],
			$sk->msg( 'cookiestatement' )->text()
		);
		#$footerlinks['analytics'] = Html::element(
		#	'a',
		#	[
		#		'href' => 'https://analytics.starcitizen.tools/starcitizen.tools',
		#		'rel' => $rel
		#	],
		#	$sk->msg( 'footer-analytics' )->text()
		#);
		$footerlinks['statuspage'] = Html::element(
			'a',
			[
				'href' => 'https://status.starcitizen.tools',
				'rel' => $rel
			],
			$sk->msg( 'footer-statuspage' )->text()
		);
		$footerlinks['github'] = Html::element(
			'a',
			[
				'href' => 'https://github.com/StarCitizenTools',
				'rel' => $rel
			],
			$sk->msg( 'footer-github' )->text()
		);
		$footerlinks['patreon'] = Html::element(
			'a',
			[
				'href' => 'https://www.patreon.com/starcitizentools',
				'rel' => $rel
			],
			$sk->msg( 'footer-patreon' )->text()
		);
		$footerlinks['kofi'] = Html::element(
			'a',
			[
				'href' => 'https://ko-fi.com/starcitizentools',
				'rel' => $rel
			],
			$sk->msg( 'footer-kofi' )->text()
		);
	}
};
