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
    die('ERROR: gettext extension is not installed but required!');
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

// The document root path
define('WDK_ROOT_PATH', $rootPath);
// WebSDK Core Classes
define('WDK_CLASS_PATH', $rootPath . 'wdk/classes/');
// WebSDK Core Functions
define('WDK_FUNC_PATH', $rootPath . 'wdk/functions/');
// WebSDK Core Extensions
define('WDK_EXT_PATH', $rootPath . 'wdk/ext/');
// App Routes
define('WDK_ROUTE_PATH', $rootPath . 'routes/');
// App Includes
define('WDK_INC_PATH', $rootPath . 'includes/');
// App Locales
define('WDK_LOCALE_PATH', $rootPath . 'includes/locale/');

// Require configurations
require_once WDK_ROOT_PATH . 'config.php';

// Require Security Functions
require_once (WDK_FUNC_PATH . 'security.php');
// Require Core Functions
require_once (WDK_FUNC_PATH . 'core.php');

// Include once additional functions
loadFiles(WDK_FUNC_PATH);

// Include once additional ressources
loadFiles(WDK_CLASS_PATH, array(
    'Database.php',
    'UserSession.php',
    'IDBObject.php',
    'User.php',
    'UserRightEnum.php',
    'UserTypeEnum.php',
    'Option.php',
    'Menu.php'
));

// Set Locale
use WebSDK\UserSession;
rebindLocale(UserSession::getLocale());

// Register SPL Autoloader
spl_autoload_register("WebSDKAutoloader");

/**
 * Require some external stuff
 */
// Require Debugging Tool
require_once WDK_EXT_PATH . 'kint/Kint.class.php';
// PHP Mailer
require_once WDK_EXT_PATH . 'PHPMailer/class.phpmailer.php';
require_once WDK_EXT_PATH . 'PHPMailer/class.smtp.php';
// Twig Template Engine
require_once WDK_EXT_PATH . 'Twig/Autoloader.php';
Twig_Autoloader::register();
// Slim Routing Framework
require_once WDK_EXT_PATH . 'Slim/Slim.php';
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
$twig->addGlobal('fileurl', getHttpHost() . 'file/');
$twig->addGlobal('app_title', (!is_empty(getOption('CFG_WDK_APP_TITLE')) ? getOption('CFG_WDK_APP_TITLE') : 'WebSDK'));
$twig->addGlobal('isOnline', UserSession::isOnline());
$twig->addGlobal('wdk_version', WDK_VERSION);
$twig->addGlobal('sessionlimit', UserSession::getSessionLimit() * 60 * 1000); // CFG_SESSION_LIMIT (in Minutes) * 1000 milliseconds
$twig->addGlobal('sessionuid', UserSession::getValue('uid'));

// Require Twig Extensions
loadFiles(WDK_EXT_PATH, array(
    'UserTwigExtensions.php',
    'CoreTwigExtensions.php'
));

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
            $finalKey = 'OPT_' . strtoupper($key);
            $finalValue = getValue($option, 'option_value');
            // register global static option
            setOption($finalKey, $finalValue);
            // register twig static option
            $twig->addGlobal($finalKey, $finalValue);
        }
    }
} catch(Exception $e){
    d($e->getMessage());
}
?>