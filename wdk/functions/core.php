<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 16.01.16
 */

/**
 * Include Once the files in $fileNames in the order
 * if $fileNames is empty, it includes all files in $path without order
 * @param string $path
 * @param array $fileNames
 */
function loadFiles($path, $fileNames = array()){
    // add trailing slash if not exist
    $path = getTrailingSlash($path);

    if(is_dir($path)){
        if(is_empty($fileNames)) {
            // include once all
            $dir = dir($path);
            while(false !== ($entry = $dir->read())){
                if($entry != '.' && $entry != '..'){
                    if(isSecureFilename($entry) && is_file($path . $entry)){
                        include_once($path . $entry);
                    }
                }
            }
        } else {
            // include only files in $fileNames
            foreach($fileNames as $file){
                if(isSecureFilename($file)){
                    if(is_file($path . $file)){
                        include_once($path . $file);
                    }
                }
            }
        }
    }
}

/**
 * WebSDK Autoloader Callback
 * @param string $className
 */
function WebSDKAutoloader($className){
    $className = str_replace('WebSDK\\', '', $className);

    // replace app namespace
    if(defined('CFG_WDK_APP_NAMESPACE')){
        $className = str_replace(CFG_WDK_APP_NAMESPACE . '\\', '', $className);
    }

    $checkPaths = array(
        WDK_CLASS_PATH,
        WDK_INC_PATH . '/classes/'
    );

    foreach($checkPaths as $path){
        if(is_file($path . $className . '.php')){
            require_once($path . $className . '.php');
        }
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
 * Checks if $str1 equals $str2 by lowering the strings to lower case and then strcmp them
 * @param $str1
 * @param $str2
 * @return bool
 */
function is_string_equal($str1, $str2){
    return strcmp(strtolower($str1), strtolower($str2)) === 0;
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
?>