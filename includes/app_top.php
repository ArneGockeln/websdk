<?php
/**

 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */
session_cache_limiter(false);
session_start();

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);

if (version_compare(PHP_VERSION, '5.2.7', '<')) {
    die('ERROR: To run this application you need minimum PHP 5.2.7! You have PHP Version ' . PHP_VERSION);
}

if (!function_exists("gettext")){
    die('ERROR: gettext extension is not installed!');
}

define('WDK_VERSION', '1.0');
define('CFG_PASSWORD_HASH_ALGO', 'sha256');

/**
 * Root Path
 */
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$script = dirname($_SERVER['PHP_SELF']);
$rootPath .= $script;

if (strpos($rootPath, '//') !== false) {
    $rootPath = str_replace('//', '/', $rootPath);
}

if (substr($rootPath, strlen($rootPath) - 1, 1) != '/') {
    $rootPath .= '/';
}

define('WDK_ROOT_PATH', $rootPath);
define('WDK_APP_INC_PATH', $rootPath . 'includes/app/');
define('WDK_CORE_INC_PATH', $rootPath . 'includes/core/');
define('WDK_EXT_INC_PATH', $rootPath . 'includes/ext/');
define('WDK_CORE_ROUTE_PATH', $rootPath . 'includes/routes/core/');
define('WDK_APP_ROUTE_PATH', $rootPath . 'includes/routes/app/');
define('WDK_LOCALE_PATH', $rootPath . 'includes/locale/');

require_once WDK_ROOT_PATH . 'config.php';
require_once WDK_CORE_INC_PATH . 'Database.php';
require_once WDK_CORE_INC_PATH . 'functions.php';
require_once WDK_CORE_INC_PATH . 'security.php';
require_once WDK_CORE_INC_PATH . 'DefaultMessages.php';
require_once WDK_CORE_INC_PATH . 'UserSession.php';

use WebSDK\UserSession;
rebindLocale(UserSession::getLocale());

require_once WDK_CORE_INC_PATH . 'Routes.php';
require_once WDK_CORE_INC_PATH . 'IDBObject.php';
require_once WDK_CORE_INC_PATH . 'User.php';
require_once WDK_CORE_INC_PATH . 'UserRightEnum.php';
require_once WDK_CORE_INC_PATH . 'UserTypeEnum.php';
require_once WDK_CORE_INC_PATH . 'Option.php';
require_once WDK_CORE_INC_PATH . 'Menu.php';

spl_autoload_register("WebSDKAutoloader");

/**
 * Require some external stuff
 */
require_once WDK_EXT_INC_PATH . 'PHPMailer/class.phpmailer.php';
require_once WDK_EXT_INC_PATH . 'PHPMailer/class.smtp.php';
// Twig Template Engine
require_once WDK_EXT_INC_PATH . 'Twig/Autoloader.php';
Twig_Autoloader::register();
// Slim Routing Framework
require_once WDK_EXT_INC_PATH . 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

// Instantiate App and set SLIM_MODE!!
// Possible values are "production" and "development"
// "development" is default, if mode is not configured!
// define constant CFG_WDK_MODE with value 'development' in config.php
// to turn development mode on!
$app = new \Slim\Slim(array(
    'mode' => (!is_empty(getOption('CFG_WDK_MODE')) ? getOption('CFG_WDK_MODE') : 'production')
));

use Slim\Middleware;
use Slim\Middleware\SessionCookie;

$twigOptions = array();
$loader = new Twig_Loader_Filesystem(WDK_ROOT_PATH . 'templates');
$twig = new Twig_Environment($loader, $twigOptions);
// Add Frontend Globals
$twig->addGlobal('siteurl', getHttpHost(false));
$twig->addGlobal('app_title', (!is_empty(getOption('CFG_WDK_APP_TITLE')) ? getOption('CFG_WDK_APP_TITLE') : 'WebSDK'));
$twig->addGlobal('isOnline', UserSession::isOnline());
$twig->addGlobal('wdk_version', WDK_VERSION);
$twig->addGlobal('sessionlimit', UserSession::getSessionLimit() * 60 * 1000); // CFG_SESSION_LIMIT (in Minutes) * 1000 milliseconds
$twig->addGlobal('sessionuid', UserSession::getValue('uid'));

// Require Twig Extensions
require_once WDK_CORE_INC_PATH . 'UserTwigExtensions.php';
require_once WDK_CORE_INC_PATH . 'CoreTwigExtensions.php';

// load only minified styles and js if we are in production mode
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));

    global $twig;
    $twig->addGlobal('app_mode', 'production');
});

// load development styles
$app->configureMode('development', function() use($app){
    $app->config(array(
        'log.enable' => true,
        'debug' => true
    ));

    global $twig;
    $twig->addGlobal('app_mode', 'development');
});

$app->add(new SessionCookie(array('secret' => getSalt())));

/**
 * I18n Localization
 */
$twig->addExtension(new Twig_Extensions_Extension_I18n());

/**
 * Define options
 * OPT_<key>
 */
$dbOptions = getOptions(true);
try {
    if(!is_empty($dbOptions)){
        foreach($dbOptions as $key => $option){
            setOption('OPT_' . strtoupper($key), getValue($option, 'option_value'));
        }
    }
} catch(Exception $e){
    debug($e->getMessage(), true);
}


/**
 * Require core stuff
 */


require_once WDK_CORE_ROUTE_PATH . 'core.routes.php';
require_once WDK_CORE_ROUTE_PATH . 'user_profile.routes.php';
require_once WDK_CORE_ROUTE_PATH . 'users.routes.php';
?>