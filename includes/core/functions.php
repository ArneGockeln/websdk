<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */

use WebSDK\UserSession;

/**
 * Autoloader
 * @param $className
 */
function WebSDKAutoloader($className){
    $className = str_replace('WebSDK\\', '', $className);

    if(is_file(WDK_CORE_INC_PATH . $className . '.php')){
        require_once(WDK_CORE_INC_PATH . $className . '.php');
    } else if(is_file(WDK_APP_INC_PATH . $className . '.php')){
        require_once(WDK_APP_INC_PATH . $className . '.php');
    }
}

/**
 * Require app routes in folder routes/app/
 */
function loadAppRoutes(){
    $d = dir(WDK_APP_ROUTE_PATH);
    while(false !== ($entry = $d->read())){
        if($entry != '.' && $entry != '..'){
            if(isRouteFile($entry)){
                require_once(WDK_APP_ROUTE_PATH . $entry);
            }
        }
    }
}

/**
 * Require core routes in folder routes/core/
 */
function loadCoreRoutes(){
    $d = dir(WDK_CORE_ROUTE_PATH);
    while(false !== ($entry = $d->read())){
        if($entry != '.' && $entry != '..'){
            if(isRouteFile($entry)){
                require_once(WDK_CORE_ROUTE_PATH . $entry);
            }
        }
    }
}

/**
 * Flash a message for next route request
 * @param string $message
 * @param string $type
 */
function flashMessage($message = '', $type = 'error'){
    $app = \Slim\Slim::getInstance();
    $app->flash($type, $message);
}

/**
 * Flash a message now
 * @param string $message
 * @param string $type
 */
function flashNowMessage($message, $type = 'error'){
    $app = \Slim\Slim::getInstance();
    $app->flashNow($type, $message);
}

/**
 * Use this in exception handling to flash a message and redirect to route name
 * @param $route string|array route name or array with route name and parameters
 * @param $message
 * @param string $status
 */
function flashRedirect($route, $message, $status = 'error'){
    $app = \Slim\Slim::getInstance();
    flashMessage($message, $status);

    if(is_array($route)){
        $app->redirectTo($route['route'], $route['params']);
    } else {
        $app->redirectTo($route);
    }
}

/**
 * Restore database values to object reference
 * @param array $dbVars
 * @param object $class
 */
function restoreFromDB($dbVars = array(), &$class){
    if(!is_array($dbVars)){
        $dbVars = (array)$dbVars;
    }
    $classVars = classToArray($class);
    foreach($classVars as $key => $value){
        $callable = 'set' . strtoupper(substr($key, 0, 1)) . substr($key, 1);

        // overwrite callable if underscores are found
        if(strpos($key, '_') !== false){
            $parts = explode('_', $key);
            $callable = 'set';
            foreach($parts as $int => $part){
                $callable .= strtoupper(substr($part, 0, 1)) . substr($part, 1);
            }
        }

        if(array_key_exists($key, $dbVars)){
            $param = $dbVars[$key];
            call_user_func_array(array($class, $callable), array($param));
        }
    }
}

/**
 * Prepare class for database insert or update
 * @param $class
 * @return array
 * @throws Exception
 */
function prepareForDB($class){
    if(!is_object($class)){
        throw new Exception("Object is not a class!");
    }

    $vars = classToArray($class);

    // strip ID if its value is 0
    if(array_key_exists('id', $vars)){
        if(getValue($vars, 'id') == 0){
            unset($vars['id']);
        }
    }

    // unescape lastmod value
    if(array_key_exists('lastmod', $vars)){
        $vars['lastmod'] = array('value' => 'NOW()', 'escape' => false);
    }

    return $vars;
}

/**
 * Converts class to array
 * @param $object
 * @return array
 */
function classToArray($object){
    $array = array();
    if(is_object($object)){
        $className = get_class($object);

        $methods = get_class_methods($className);

        // Version 1 without _ varnames
        /*foreach($methods as $int => $method){
            if(strcmp(substr($method, 0, 3), 'get') == 0){
                $array[strtolower(substr($method, 3))] = call_user_func(array($object, $method));
            }
        }*/
        // Version 2 with _ var_names
        foreach($methods as $int => $method){
            // rewrite getters with underscores
            if(strcmp(substr($method, 0, 3), 'get') == 0){
                preg_match_all('/[A-Z]/', substr($method, 3), $matches);
                $varname = substr($method, 3);
                $matchCount = count($matches[0]);
                if($matchCount > 1) {
                    for($i = 1; $i <= $matchCount; $i++){
                        $varname = str_replace($matches[0][$i], '_' . $matches[0][$i], $varname);
                    }
                }

                $array[strtolower($varname)] = call_user_func(array($object, $method));
            }
        }
    }
    return $array;
}

/**
 * Check if current resource uri equals $checkUri
 * ex. /projects == /projects
 * @param $checkUri
 * @return bool
 */
function isActiveUri($checkUri){
    $app = \Slim\Slim::getInstance();
    $requestUri = $app->request->getResourceUri();
    if(strlen($checkUri) > 1){
        if(strcasecmp($requestUri, $checkUri) == 0){
            return true;
        } else if(strpos($requestUri, $checkUri) !== false){
            return true;
        }
    } else {
        if(strcmp($checkUri, $requestUri) == 0){
            return true;
        }
    }
    return false;
}

/**
 * Render or Return Template html with optional values
 * @param $template
 * @param array $values
 * @param bool|false $return
 * @return string
 */
function renderTemplate($template, $values = array(), $return = false){
    global $twig;

    $app = \Slim\Slim::getInstance();

    // Get messages
    $messageTypes = array(
        'error',
        'info',
        'warning',
        'success'
    );
    foreach($messageTypes as $int => $messageType){
        $message = getValue($app->flashData(), $messageType);
        if(strlen($message)>0){
            $values['system_messages'][] = array('type' => $messageType, 'msg' => $message);
        }
    }

    $html = $twig->render($template, $values);
    if(!$return){
        echo $html;
    } else {
        return $html;
    }
}

/**
 * Returns the current host
 * if $is_host is given, the functions checks if current host is equal to $is_host
 *
 * @param string $is_host
 * @return mixed bool oder string(host)
 */
function getHost($is_host = '') {
    $svr_host = $_SERVER['HTTP_HOST'];
    if (trim($svr_host) == '') {
        $svr_host = $_SERVER['SERVER_NAME'];
    }
    $folder = dirname($_SERVER['PHP_SELF']);
    if ($folder != '' && $folder != '/') {
        $svr_host .= $folder . '/';
    }

    if (trim($is_host) != '') {
        if (md5($is_host) == md5($svr_host)) {
            return true;
        }
        return false;
    } else {
        return $svr_host;
    }
}

/**
 * Return current http host like http://domain.de
 * @return string
 */
function getHttpHost($addTrailingSlash = true) {
    $host = getHost();
    if ($addTrailingSlash) {
        $host = getTrailingSlash($host);
    } else {
        $host = getTrailingSlash($host, true);
    }

    if (strpos('http://', $host) === false) {
        $string = 'http://' . $host;
    } else {
        $string = $host;
    }
    return $string;
}

/**
 * Returns the domain only without protocol like domain.de and not http://domain.de
 * @return string
 */
function getDomain(){
    $host = getHost();
    if(strpos('http://', $host) !== false){
        $host = str_replace('http://', '', $host);
    }
    if(strpos('https://', $host) !== false){
        $host = str_replace('https://', '', $host);
    }
    return $host;
}

/**
 * Returns the http origin like http://extern.domain.de
 * @return null|string
 * @throws Exception
 */
function getHttpOrigin(){
    $origin = $_SERVER['HTTP_ORIGIN'];
    if(is_empty($origin)){
        return null;
    }

    return $origin;
}

/**
 * Adds a traling slash to the path if not exist
 * @param string $path
 * @return string
 */
function getTrailingSlash($path, $remove = false){
    // add slash
    if(substr($path, strlen($path) - 1, 1) != '/' && $remove === false){
        $path .= '/';
    } else {
        // remove trailing slash?
        if($remove){
            if(substr($path, strlen($path) - 1, 1) == '/'){
                $path = substr($path, 0, -1);
            }
        }
    }
    return $path;
}

/**
 * Return current document root
 */
function getDocRoot() {
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $script = dirname($_SERVER['PHP_SELF']);
    $docRoot .= $script;
    if (strpos($docRoot, '//') !== false) {
        $docRoot = str_replace('//', '/', $docRoot);
    }

    if (substr($docRoot, strlen($docRoot) - 1, 1) != '/') {
        $docRoot .= '/';
    }

    return $docRoot;
}

/**
 * Returns the value from $array[$key] if $key exists as index in $array
 * If $defaultValue is set, it will be returned if array key does not exist or array value is empty!
 * @param $array
 * @param $key
 * @param $defaultValue = null (optional)
 * @return null
 */
function getValue($array, $key, $defaultValue = null){
    $return = null;

    if(is_array($array) && array_key_exists($key, $array)){
        $return = $array[$key];
        if(is_empty($return) && !is_null($defaultValue)){
            $return = $defaultValue;
        }
    } else if(!is_null($defaultValue)){
        $return = $defaultValue;
    }

    return $return;
}

/**
 * Returns the value from constant $constant. If the constant does not exist it returns null
 * @param string $constant
 * @return string|null
 */
function getOption($constant){
    if(defined(strtoupper($constant))){
        return constant(strtoupper($constant));
    }
    return null;
}

/**
 * Defines a constant, but first checks if it is already defined and if yes tries to redefine
 * @param string $constant
 * @param string $value
 * @throws Exception
 */
function setOption($constant, $value){
    if(!is_string($value)){
        throw new Exception(_('Wert ist kein String!'));
    }

    $constant = strtoupper($constant);
    if(defined($constant)){
        if(function_exists('runkit_constant_redefine')){
            runkit_constant_redefine($constant, $value);
        } else {
            throw new Exception(sprintf(_('Die Konstante "%s" ist bereits definiert!'), $constant));
        }
    } else {
        define($constant, $value);
    }
}

/**
 * Returns a string with random chars with given length
 * @param int $length
 * @return string
 */
function getSalt($length = 10){
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Returns a string with random chars and special chars with given length
 * that is valid against is_secure_password()
 * @param int $length
 * @return string
 */
function getRandomPassword($length = 10){
    $dict = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    do {
        $random = substr(str_shuffle($dict), 0, $length);
    } while(!isSecurePassword($random));

    return $random;
}

/**
 * Sends an email to someone and makes some security checks
 *
 * Required options:
 * - subscriber email address, if this is not given, the message will be send to the administrator email
 * - subject
 * - body
 *
 * Following options will be set by default config values:
 * - from           : CFG_MAILER_DEFAULT_FROM | OPT_MAILER_FROM
 * - from_email     : CFG_MAILER_DEFAULT_FROM_EMAIL | OPT_MAILER_FROM_EMAIL
 * - reply_email    : CFG_MAILER_DEFAULT_REPLY_EMAIL | OPT_MAILER_REPLY_EMAIL
 *
 * @param array $options (subscriber,subject,body,altbody,from,from_email,reply_email)
 * @return bool returns true if mail was send
 * @throws Exception
 * @throws phpmailerException
 */
function mailTo($options = array()){
    $subscriber = getValue($options, 'subscriber');

    if(is_empty($subscriber) || is_null($subscriber)){
        // get admin email
        $subscriber = getOption('OPT_CORE_MAILER_ADMIN_EMAIL');

        if(is_empty($subscriber)){
            throw new Exception('Mailer: ' . _('Empfänger E-Mail wird benötigt!'));
        }
    }

    $subject = getValue($options, 'subject');
    $body = getValue($options, 'body');
    $altBody = getValue($options, 'altbody');

    // FROM Name
    $from = getValue($options, 'from');
    if(is_empty($from)){
        $from = getOption('CFG_MAILER_DEFAULT_FROM');
        if(is_empty($from)){
            $from = getOption('OPT_CORE_MAILER_FROM');
        }
    }

    if(is_empty($from)) throw new Exception('Mailer: ' . _('Absender Name wird benötigt!'));

    // FROM Email
    $from_email = getValue($options, 'from_email');
    if(is_empty($from_email)){
        $from_email = getOption('CFG_MAILER_DEFAULT_FROM_EMAIL');
        if(is_empty($from_email)){
            $from_email = getOption('OPT_CORE_MAILER_FROM_EMAIL');
        }
    }

    if(is_empty($from_email)) throw new Exception('Mailer: ' . _('Absender E-Mail Adresse wird benötigt!'));

    // REPLY Email
    $reply_email = getValue($options, 'reply_email');
    if(is_empty($reply_email)){
        $reply_email = getOption('CFG_MAILER_DEFAULT_REPLY_EMAIL');
        if(is_empty($reply_email)){
            $reply_email = getOption('OPT_CORE_MAILER_REPLY_EMAIL');
        }
    }

    if(is_empty($reply_email)) throw new Exception('Mailer: ' . _('Antwort E-Mail Adresse wird benötigt!'));

    // Ready to config mailer
    $mailer = new PHPMailer();
    $mailer->setFrom($from_email, $from);
    $mailer->addAddress($subscriber);
    $mailer->addReplyTo($reply_email);
    $mailer->isHTML(true);
    $mailer->CharSet = "UTF-8";

    if(!is_empty(getOption('OPT_CORE_MAILER_SMTP_HOST'))){
        $mailer->isSMTP();
        $mailer->SMTPAuth = true;
        $mailer->Username = getOption('OPT_CORE_MAILER_SMTP_USER');
        $mailer->Password = getOption('OPT_CORE_MAILER_SMTP_PASSWORD');
        $mailer->Host = getOption('OPT_CORE_MAILER_SMTP_HOST');
    }

    $mailer->Subject = $subject;
    $mailer->Body = $body;
    if(!is_null($altBody)){
        $mailer->AltBody = $altBody;
    }

    if(!$mailer->send()){
        throw new Exception($mailer->ErrorInfo);
    }

    return true;
}

/**
 * Checks if $username is not in use!
 * @param string $username
 * @return bool
 */
function is_username_available($username){
    $mysql = WebSDK\Database::getInstance();
    $count = $mysql->query("SELECT COUNT(*) as c FROM " . \WebSDK\DBTables::USERS . " WHERE username = '" . $username . "' OR username = '" . strtolower($username) . "' OR username = '" . strtoupper($username) . "'")->fetchRow();
    if($count->c == 0){
        return true;
    }
    return false;
}

/**
 * Checks if $email is not in use!
 * @param string $email
 * @return bool
 */
function is_email_available($email){
    $mysql = WebSDK\Database::getInstance();
    $count = $mysql->query("SELECT COUNT(*) as c FROM " . \WebSDK\DBTables::USERS . " WHERE email = '" . $email . "' OR email = '" . strtolower($email) . "' OR email = '" . strtoupper($email) . "'")->fetchRow();
    if($count->c == 0){
        return true;
    }
    return false;
}

/**
 * Debug mixed var
 * @param $mixed
 * @param bool|false $die
 */
function debug($mixed, $die = false){
    $debug = '<pre>' . var_export($mixed, true) . '</pre>';
    if($die){
        die($debug);
    }
    echo $debug;
}

/**
 * Save $array to session
 * @param array $array
 */
function saveFormData($array){
    if(!is_empty($array)){
        $string = trim(json_encode($array));
        $base64 = base64_encode($string);
        UserSession::addValue('formData', $base64);
    }
}

/**
 * Restore form data
 * @return mixed|null
 */
function restoreFormData(){
    $formDataString = base64_decode(UserSession::getValue('formData'));
    if(!is_empty($formDataString)){
        return json_decode($formDataString, true);
    }
    return null;
}

/**
 * Clear form data
 */
function clearFormData(){
    UserSession::addValue('formData', '');
    UserSession::remValue('formData');
}

/**
 * Check if string, array or number is empty or number = 0
 * @param $mixed
 * @return bool
 * @throws Exception
 */
function is_empty($mixed){
    if(is_array($mixed)){
        return count($mixed) <= 0;
    } else if(is_string($mixed)){
        return strlen(trim($mixed)) <= 0;
    } else if(is_numeric($mixed)){
        return $mixed == 0;
    } else if(is_null($mixed)){
        return true;
    }
    throw new Exception(_('Wert ist weder vom Typ String, Number noch Array!'));
}

/**
 * Ajax Response with http status code attached, stop $app
 * @param $mixed
 * @param int $http_status_code
 * @throws \Slim\Exception\Stop
 */
function ajaxResponse($mixed, $http_status_code = 200){
    $app = \Slim\Slim::getInstance();

    $response = array(
        'status' => $http_status_code,
        'body' => $mixed
    );

    if($http_status_code != 200){
        $app->response->setStatus($http_status_code);
    }

    // content type will be automatically detected by slim
    die(json_encode($response));
}

/**
 * Extract only key/value pairs which keys contains search
 * @param $array
 * @param string $search
 * @return array
 */
function extractByKeys($array, $search = ''){
    $result = array();
    if(strlen($search) > 0 && is_array($array)){
        foreach($array as $key => $value){
            if(strpos($key, $search) !== false){
                $result[$key] = $value;
            }
        }
    }
    return $result;
}

/**
 * Get all options
 * @param bool|true $asKeyList returns array[option_key] = option list
 * @return array
 */
function getOptions($asKeyList = true){
    $mysql = \WebSDK\Database::getInstance();
    $sql = "SELECT * FROM " . \WebSDK\DBTables::OPTIONS . " ORDER BY option_category";
    $mysql->query($sql);
    $list = $mysql->fetchList(true);

    if($asKeyList) {
        $options = array();
        if(!is_empty($list)){
            foreach($list as $int => $option){
                $options[$option['option_key']] = $option;
            }
        }
        return $options;
    }
    return $list;
}

/**
 * Check if FILTER formData is empty
 * @param $formData
 * @return bool
 * @throws Exception
 */
function is_filter_empty($formData){
    if(!is_empty($formData)){
        if(getValue($formData, 'inputIsFilter') == 1){
            return false;
        }
    }
    return true;
}

/**
 * Check if $formData contains one or more elements from $elements
 * @param $formData
 * @param array $elements
 * @return bool
 * @throws Exception
 */
function hasElements($formData, $elements = array()){
    $matches = 0;
    if(is_array($formData)){
        if(!is_empty($formData)){
            foreach($formData as $key => $value){
                if(in_array($key, $elements)){
                    $matches++;
                }
            }
        }
    }
    return $matches > 0;
}

/**
 * Parse formData with input fields "input" prefix
 * @param array $formData Reference to formData array
 */
function parseFormData(&$formData){
    $newFormData = array();
    foreach($formData as $key => $value){
        if(strpos(substr($key, 0, 5), 'input') !== false){
            $newKey = strtolower(substr($key, 5));
            $newFormData[$newKey] = $value;
            unset($formData[$key]);
        }
    }

    $formData = array_merge($formData, $newFormData);
}

/**
 * Updates the value of $mixed_list with respect of the origin type!
 * If you send an array list, it will return as an array!
 * If you send a comma separated string list, it will return as a comma separated string!
 *
 * @param array|string $mixed_list reference, the list to update
 * @param array|string $new_list the new list
 */
function updateMixedListWithType(&$mixed_list, $new_list){
    if(is_array($mixed_list)){
        $mixed_list = getArrayFromMixed($new_list);
    } else if(strpos($mixed_list, ',') !== false || is_string($mixed_list)){
        $mixed_list = implode(',', getArrayFromMixed($new_list));
    }
}

/**
 * Returns an array from $mixed
 * @param array|string|int $mixed array(1,2,3)|1,2,3|1
 * @return array
 */
function getArrayFromMixed($mixed){
    $list = array();
    if(is_array($mixed)){
        $list = $mixed;
    } else if(strpos($mixed, ',') !== false){
        $list = explode(',', $mixed);
    } else if(is_numeric($mixed)) {
        $list[] = (int)$mixed;
    } else if(is_string($mixed)) {
        $list[] = $mixed;
    }
    return $list;
}

/**
 * Converts all values of $array to int
 * @param array $array reference
 * @throws Exception
 */
function convertArrayValueToInt(&$array){
    if(!is_empty($array)){
        foreach($array as $key => $value){
            $array[$key] = (int)$value;
        }
    }
}

/**
 * Check if string is a json string
 * @param type $string
 * @return boolean
 */
function is_json($string) {
    if (strlen($string) > 0) {
        $jsonArray = json_decode($string, true);
        if ($jsonArray !== NULL && (is_array($jsonArray) && count($jsonArray) > 0)) {
            return true;
        }
    }
    return false;
}

/**
 * Set locale and bind textdoamin
 * @param string $locale
 * @param string $textDomain default is WebSDK
 */
function rebindLocale($locale, $textDomain = 'WebSDK'){
    putenv('LC_ALL='.$locale);
    setlocale(LC_ALL, $locale);
    setlocale(LC_TIME, $locale);
    bindtextdomain($textDomain, WDK_LOCALE_PATH);
    bind_textdomain_codeset($textDomain, 'UTF-8');
    textdomain($textDomain);
}

/**
 * Looks into path WDK_LOCALE_PATH
 * and lists all locale directories
 * @return array
 */
function getLocales(){
    $dir = dir(WDK_LOCALE_PATH);
    $list = array();
    while(false !== ($entry = $dir->read())){
        if(!in_array($entry, ['.', '..'])){
            if(is_dir(getTrailingSlash(WDK_LOCALE_PATH) . $entry)){
                $list[] = $entry;
            }
        }
    }
    return $list;
}
?>