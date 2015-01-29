<?php session_start();

error_reporting(E_ALL); //^ E_NOTICE
ini_set("display_errors", 1);

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
  die('ERROR: To run this application you need minimum PHP 5.0.0. You have PHP Version ' . PHP_VERSION);
}

$rootPath = $_SERVER['DOCUMENT_ROOT'];
$script = dirname($_SERVER['PHP_SELF']);
$rootPath .= $script;

if( strpos($rootPath, 'uploadify') !== false){
  $rootPath = str_replace('uploadify', '', $rootPath);
}

if (strpos($rootPath, '//') !== false) {
  $rootPath = str_replace('//', '/', $rootPath);
}

if (substr($rootPath, strlen($rootPath) - 1, 1) != '/') {
  $rootPath .= '/';
}

// GETTEXT
function _setLocale($locale, $textDomain){
  global $rootPath;
  putenv('LANG=' . $locale);
  setlocale(LC_ALL, $locale);
  setlocale(LC_TIME, $locale);
  // Angeben des Pfads der Übersetzungstabellen
  bindtextdomain($textDomain, $rootPath . 'locale');
  bind_textdomain_codeset($textDomain, 'UTF8');

  // Domain auswählen
  textdomain($textDomain);
}

require_once $rootPath . 'config.php';
require_once $rootPath . 'includes/rights.config.php';
require_once $rootPath . 'includes/mysql.class.php';
require_once $rootPath . 'includes/user.class.php';
require_once $rootPath . 'includes/userrole.class.php';
require_once $rootPath . 'includes/usersession.class.php';

require_once $rootPath . 'includes/functions.php';
if(is_file($rootPath . 'includes/functions.custom.php')){
    require_once $rootPath . 'includes/functions.custom.php';
}

define('AP_HTTPHOST', getHttpHost());
define('AP_DOCROOT', $rootPath);

$currentUserRights = getCurrentUserRights();
$localeList = getLocaleList();

// set user locale
_setLocale(getCurrentLocale(), getCurrentLocale());
?>