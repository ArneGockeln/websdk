<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 * 
 * Do not delete these functions!
 */

/**
 * Checks if session is online
 * @global type $AP_SESSION
 * @return boolean
 */
function is_online() {
  if (UserSession::isOnline()) {
    return true;
  }
  return false;
}

/**
 * Returns a correct href url
 * @param string $file
 * @param array $params additional params as key => value pairs
 * @return string
 */
function getUrl($file, $params = array()) {
    if(is_array($params) && count($params) > 0){
        $url = getHttpHost(true) . $file . '?';
        foreach($params as $key => $value){
            $url .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        $url = substr($url, 0, -1);

        return $url;
    } else {
        return getHttpHost(true) . $file;
    }
}

/**
 * Returns a correct path to a file
 * @param string $file
 * @return string
 */
function getPath($file) {
  return getDocRoot() . $file;
}

/**
 * Checks if the template file exists and includes it
 * @param string $file
 */
function includeTemplate($file) {
  if (is_file(getPath('template/' . $file))) {

    include(getPath('template/' . $file));
  } else {
    die(getMessage(array('text' => sprintf(_('Das Template %1$s existiert nicht!'), $file), 'isError' => true)));
  }
}

/**
 * includes a file in the folder includes/
 * @param type $file
 */
function includeFile($file) {
  if (is_file(getPath('includes/' . $file))) {
    include(getPath('includes/' . $file));
  } else {
    die(getMessage(array('text' => sprintf(_('Die Datei %1$s existiert nicht!'), $file), 'isError' => true)));
  }
}

/**
 * If $file == $fileIdent return class active
 * @global type $fileIdent
 * @param type $file
 * @return string
 */
function getActiveLi($file) {
  global $fileIdent;
  if ($fileIdent == $file) {
    return 'class="active"';
  }
  return '';
}

/**
 * Register a new message object to session
 * @param type $message
 * @param type $isError
 */
function registerMessage($message, $isError = false) {
  $object = array(
      'text' => $message,
      'isError' => $isError
  );

  UserSession::addObject('message', $object);
}

/**
 * Get system messages
 * @return string
 */
function getMessage($mObj = array()) {
  $message = '';
  if (count($mObj) == 0 && !array_key_exists('text', $mObj)) {
    $mObj = UserSession::getObject('message');
  }

  if (is_array($mObj)) {
    $message = '<p></p><div class="alert ' . ($mObj['isError'] ? 'alert-danger' : 'alert-success') . '">';
    $message .= '<strong>' . ($mObj['isError'] ? _('Fehler') : _('Erfolg')) . '</strong> ' . $mObj['text'];
    $message .= '<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>';
    $message .= '</div>';
  }

  UserSession::removeObject('message');

  return $message;
}

/**
 * Redirect to file
 * @param string $fileIdent
 * @param boolean $status HTTP HEADER STATUS
 */
function redirect($fileIdent, $status = false) {
  session_write_close();
  if ($status > 0) {
    header("Location: " . getUrl($fileIdent), true, $status);
  } else {
    header("Location: " . getUrl($fileIdent));
  }
  die();
}

/**
 * Redirect to file if headers already sent!
 * @param type $fileIdent
 */
function redirectHtml($fileIdent){
  session_write_close();
  echo '<meta http-equiv="refresh" content="0; URL=' . getUrl($fileIdent) . '">';
  die();
}

/**
 * Get array value
 * @param string|int $identifer
 * @param array $array
 * @return mixed
 */
function getValue($identifer, $array) {
  if (is_array($array)) {
    if (array_key_exists($identifer, $array)) {
      return $array[$identifer];
    }
  }
  return false;
}

/**
 * Check if string is a valid email address
 * @param type $email
 * @return boolean
 */
function is_email($email) {
  // Test for the minimum length
  if (strlen($email) < 3) {
    return false;
  }

  // Test for an @ character after the first position
  if (strpos($email, '@', 1) === false) {
    return false;
  }

  // Split the email in parts
  list($local, $domain) = explode('@', $email);

  // LOCAL PART
  // Test for invalid characters
  if (!preg_match('/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local)) {
    return false;
  }

  // DOMAIN PART
  // Test for sequences of periods
  if (preg_match('/\.{2,}/', $domain)) {
    return false;
  }

  // Test for leading and trailing periods and whitespace
  if (trim($domain, " \t\n\r\0\x0B.") !== $domain) {
    return false;
  }

  // Split the domain into subs
  $subs = explode('.', $domain);

  // Assume the domain will have at least two subs
  if (2 > count($subs)) {
    return false;
  }

  // Loop through each sub
  foreach ($subs as $sub) {
    // Test for leading and trailing hyphens and whitespace
    if (trim($sub, " \t\n\r\0\x0B-") !== $sub) {
      return false;
    }

    // Test for invalid characters
    if (!preg_match('/^[a-z0-9-]+$/i', $sub)) {
      return false;
    }
  }

  // Congratulations your email made it!
  return true;
}

/**
 * Generiert ein plaintext Passwort
 * @param int $length
 * @param bool $specialChars true|false add special chars
 * @return string
 */
function generatePassword($length = 8, $specialChars = false) {
  try {
    $allowed = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    if ($specialChars)
      $allowed .= "!{}()-_@$";
    $passwd = '';
    $max = strlen($allowed);

    do {
      $rand = mt_rand(0, $max);
      if ($rand > $max || $rand < 0) {
        $rand = $max - 1;
      }
      $passwd .= substr($allowed, $rand, 1);
    } while (strlen($passwd) < $length);

    if (strlen($passwd) < $length)
      throw new Exception(ALERT_ERR_PASSWORD_TO_SHORT);
    return $passwd;
  } catch (Exception $e) {
    return $e->getMessage();
  }
}

/**
 * Überprüft ob die E-Mailadresse bereits existiert.
 * @param string $email
 * @return boolean
 */
function doesEmailExists($email) {
  $return = false;
  if (is_email($email)) {
    $db = new mysqlDatabase();
    $db->setSQL("SELECT `id` FROM " . CFG_DBT_USERS . " WHERE email = '" . mysql_real_escape_string($email) . "'");
    $ret = $db->fetchRow();
    if ($db->hasRows()) {
      $return = true;
    }
  }
  return $return;
}

/**
 * Replaces whitespace, ., ... with underscore
 * @param string $unsecure
 * @return string
 */
function getSecureString($unsecure) {
  return preg_replace('/[^\w\._]+/', '_', $unsecure);
}

/**
 * print_r $value
 * @param mixed $value
 */
function dump($value, $die = false) {
  echo '<pre>' . print_r($value, true) . '</pre>';
  if ($die)
    die();
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
 * Return current http host
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
 * Returns the domain only
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
 * Convert timestring to database date
 * @param type $sourceDate
 * @return type
 */
function convertToDbDate($sourceDate) {
  return date("Y-m-d", strtotime($sourceDate));
}

/**
 * Check if the string contains any char
 * @param string $string
 * @return boolean true if $string is empty
 */
function isEmptyString($string = '') {
  return isEmpty($string);
}

/**
 * Returns true if $mixed is empty
 * @param mixed $mixed
 * @return boolean
 */
function isEmpty($mixed){
  if(is_array($mixed)){
    return count($mixed) <= 0;
  } else if(is_string($mixed) || is_numeric($mixed)){
    return (strlen(trim($mixed)) > 0 ? false : true);
  }
  
  return -1;
}

/**
 * Get order by url
 * @param string $fileIdent
 * @param string $fileIdentPage
 * @param string $orderby
 * @return string
 */
function getOrderUrl($orderby) {
  global $fileIdent, $fileIdentPage;
  $url = getUrl($fileIdent . '?page=' . $fileIdentPage . '&orderby=' . $orderby . '&order=' . (getValue('order', $_GET) == 'desc' ? 'asc' : 'desc'));
  return $url;
}

/**
 * If user is online it returns the current user id
 * else it returns 0
 * @return int
 */
function getCurrentUID() {
  if (is_online()) {
    return UserSession::getCurrentUserId();
  }
  return 0;
}

/**
 * Get current user name, if username is empty, returns current email address
 * @return mixed
 */
function getCurrentUsername() {
  if (is_online()) {
    $username = UserSession::getCurrentUsername();
    if (strlen(trim($username)) > 0) {
      return $username;
    } else {
      $user = new User(getCurrentUID());
      return $user->email;
    }
  }
  return false;
}

/**
 * Get current locale, if its not set, returns the default locale
 * @global array $localeList
 * @return string
 */
function getCurrentLocale(){
  $locale = (defined('CFG_LOCALE_DEFAULT') ? CFG_LOCALE_DEFAULT : 'de_DE');
    
  if(is_online()){
    $locale = UserSession::getCurrentLocale(true);    
        
    if(isEmptyString($locale)){
      $locale = (defined('CFG_LOCALE_DEFAULT') ? CFG_LOCALE_DEFAULT : 'de_DE');
    } else {
      global $localeList;
      if(!in_array($locale, $localeList)){
        $locale = (defined('CFG_LOCALE_DEFAULT') ? CFG_LOCALE_DEFAULT : 'de_DE');
      }
    }
  }
  return $locale;
}

/**
 * Get current user rights from all roles
 * @return array
 */
function getCurrentUserRights() {
  global $WebArbyteRights;
  $currentUserId = getCurrentUID();

  if ($currentUserId > 0) {
    $mysql = new mysqlDatabase(CFG_DBT_USERS);
    $sql = "SELECT GROUP_CONCAT(ur.rights) AS rights FROM ".CFG_DBT_USER_ROLES." ur WHERE FIND_IN_SET(ur.id, (SELECT roles FROM ".CFG_DBT_USERS." WHERE id = '" . (int)$currentUserId. "'))";
    $mysql->setSQL($sql);
    $roleList = $mysql->fetchList();

    $roleRights = array();
    if ($mysql->hasRows()) {
      foreach ($roleList as $int => $rowArray) {
        $row = (object) $rowArray;
        $dbRights = explode(',', $row->rights);
        foreach ($dbRights as $int2 => $dbRight) {
          // check if right id is in use
          if(array_key_exists($dbRight, $WebArbyteRights)){
            // check if right id is in current role rights
            if (!in_array($dbRight, $roleRights)) {
              $roleRights[] = $dbRight;
            } 
          }
        }
      }
    }

    return $roleRights;
  }

  return array(-1);
}

/**
 * Checks if current user has the given rights
 * @param array|string $rights
 * @return boolean
 */
function hasRights($rights) {
  global $currentUserRights;

  $hasRight = false;
  
  // Convert rights to array if its a comma seperated string
  $checkRights = array();
  if (is_array($rights)) {
    $checkRights = $rights;
  } else if (strpos($rights, ',') !== false) {
    $checkRights = explode(',', $rights);
  }

  if (count($checkRights) > 0 && count($currentUserRights) > 0) {
    foreach($checkRights as $int => $checkRight){
      if(in_array($checkRight, $currentUserRights) || $checkRight == -1){
        $hasRight = true;
      }
    }
  }
  return $hasRight;
}

/**
 * Remove $remove_id from $list
 * @param int $remove_id
 * @param mixed $list array|string
 * @return mixed false if $list is empty, array if $list is array, string if $list is string
 */
function removeIdFromList($remove_id, $list){
  $oldList = array();
  $returnArray = false;
  if(!is_array($list) && !isEmptyString($list)){
    if(strpos($list, ',') !== false){
      $oldList = explode(',', $list);
    } else {
      $oldList = array($list);
    }
  } elseif(is_array($list) && count($list)>0) {
    $oldList = $list;
    $returnArray = true;
  } else {
    return false;
  }
  
  $newList = array();
  foreach($oldList as $int => $current_id){
    if(!isEmptyString($current_id) && $current_id != $remove_id){
      $newList[] = $current_id;
    }
  }
  
  return ($returnArray ? $newList : implode(',', $newList));
}

/**
 * Add $add_id to $list
 * @param int $add_id
 * @param mixed $list array|string
 * @return mixed false if $list is empty, array if $list is array, string if $list is string
 */
function addIdToList($add_id, $list){
  $oldList = array();
  $returnArray = false;
  if(!is_array($list)){
    if(strpos($list, ',') !== false){
      $oldList = explode(',', trim($list));
    } else {
      if(!isEmptyString($list)){
        $oldList = array($list);
      }
    }
  } elseif(is_array($list)) {
    $oldList = $list;
    $returnArray = true;
  } else {
    return false;
  }
    
  if(!in_array($add_id, $oldList)){
    if(!isEmptyString($add_id)){
      $oldList[] = $add_id;
    }
  } 
    
  return ($returnArray ? $oldList : implode(',', $oldList));
}

/**
 * Recursive array search
 * @param mixed $needle
 * @param array $haystack
 * @return boolean
 */
function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

/**
 * Get List of all locales
 * @return array
 */
function getLocaleList(){
  $localeDir = getTrailingSlash(AP_DOCROOT . 'locale');
  $list = array();
  if(is_dir($localeDir)){
    $d = dir($localeDir);
    while(false !== ($entry = $d->read())){
      if(!in_array($entry, array('.', '..'))){
        if(is_dir($localeDir . $entry))
        $list[] = $entry;
      }
    }
  }
  return $list;
}

/**
 * If current page is a add form,
 * returns true
 * @return boolean
 */
function isAddPage(){
  return (getValue('action', $_REQUEST) == 'add' ? true : false);
}

/**
 * Send email
 * @param type $to
 * @param type $to_name
 * @param type $subject
 * @param type $body
 * @return boolean
 */
function sendEmail($to, $to_name, $subject, $body){
  includeFile('class.phpmailer.php');
  
  // send email to user
  $mail = new PHPMailer();

  if(defined('CFG_MAIL_USE_SMTP') && (CFG_MAIL_USE_SMTP !== false)){
    $mail->isSMTP();
    $mail->Host = (defined('CFG_MAIL_SMTP_SERVER') && !isEmpty(CFG_MAIL_SMTP_SERVER) ? CFG_MAIL_SMTP_SERVER : die(_('CFG_MAIL_SMTP_SERVER nicht definiert')));
    $mail->Username = (defined('CFG_MAIL_SMTP_USER') && !isEmpty(CFG_MAIL_SMTP_USER) ? CFG_MAIL_SMTP_USER : die(_('CFG_MAIL_SMTP_USER nicht definiert')));
    $mail->Password = (defined('CFG_MAIL_SMTP_PWD') && !isEmpty(CFG_MAIL_SMTP_PWD) ? CFG_MAIL_SMTP_PWD : die(_('CFG_MAIL_SMTP_PWD nicht definiert')));
    $mail->SMTPAuth = (defined('CFG_MAIL_SMTP_AUTH') ? CFG_MAIL_SMTP_AUTH : false);
    $mail->SMTPSecure = (defined('CFG_MAIL_SMTP_SECURE') && !isEmpty(CFG_MAIL_SMTP_SECURE) ? CFG_MAIL_SMTP_SECURE : die(_('CFG_MAIL_SMTP_SECURE nicht definiert')));
    if(defined('CFG_MAIL_SMTP_PORT') && !isEmpty(CFG_MAIL_SMTP_PORT)){
      $mail->Port = (int)CFG_MAIL_SMTP_PORT;
    }
  }

  $mail->From = (defined('CFG_MAIL_FROM') && !isEmpty(CFG_MAIL_FROM) ? CFG_MAIL_FROM : die(_('CFG_MAIL_FROM nicht definiert')));
  $mail->FromName = (defined('CFG_MAIL_FROM_NAME') && !isEmpty(CFG_MAIL_FROM_NAME) ? CFG_MAIL_FROM_NAME : die(_('CFG_MAIL_FROM_NAME nicht definiert')));
  $mail->addAddress($to, $to_name);
  $mail->addReplyTo('noreply@' . getDomain());
  $mail->isHTML(false);

  $mail->Subject = $subject;
  $mail->Body = $body;
    
  return $mail->send();
}

/**
 * Set custom options to default options array
 * sets only options if keys are available in default options array
 * @param array $defaults
 * @param array $customs
 * @return array
 */
function get_options(array $defaults, array $customs){
    if(is_array($defaults) && is_array($customs)){
        foreach($customs as $customOptionKey => $customOption){
            if(array_key_exists($customOptionKey, $defaults)){
                $defaults[$customOptionKey] = $customOption;
            }
        }
        return $defaults;
    }

    return array();
}

/**
 * Walks through $pageTree defined in template.nav.php
 * $args = array(
    'pageTree' => array() // the page tree
    'root_container' => '<ul>|</ul>', // root container
    'child_container' => '<ul>|</ul>', // child container
    'root_wrapper' => '<li>|</li>', // links will be wrapped into
    'child_wrapper' => '<li>|</li>', // child links will be wrapped into
    'ident_selected' => '' // this ident get class="active"
 * )
 */
function nav_walker(array $args){
    $pageTree = getValue('pageTree', $args);
    if(!is_array($pageTree)){
        die('need pageTree as array!');
    }

    $options = get_options(array(
        'pageTree' => array(),
        'root_container' => '<ul>|</ul>',
        'child_container' => '<ul>|</ul>',
        'root_wrapper' => '<li>|</li>',
        'child_wrapper' => '<li>|</li>',
        'is_dropdown' => false,
        'ident_selected' => ''
    ), $args);

    $root_container = explode('|', getValue('root_container', $options));
    $child_container = explode('|', getValue('child_container', $options));
    $root_wrapper = explode('|', getValue('root_wrapper', $options));
    $child_wrapper = explode('|', getValue('child_wrapper', $options));

    $nl = "\n";
    $result = getValue('is_dropdown', $options) !== false ? $child_container[0] : $root_container[0];
    $isOnline = is_online();
    foreach($pageTree as $filename => $pageOptions){
        switch($filename) {
            case 'dropdown':
                $result .= str_replace('>', ' class="dropdown">', $root_wrapper[0]);
                $result .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">';
                $result .= getValue('text', $pageOptions);
                $result .= '</a>';
                // recursive
                $childOptions = $options;
                $childOptions['pageTree'] = getValue('childs', $pageOptions);
                $childOptions['is_dropdown'] = true;

                $result .= nav_walker($childOptions);
                $result .= $root_wrapper[1];
                break;
            default: // page like 'index.php'
                // require active user session
                if(getValue('require_login', $pageOptions) !== false && $isOnline === false){
                    break;
                }

                // check rights
                if (!hasRights(getValue('rights', $pageOptions))) {
                    break;
                }

                // generate identification if not available
                $ident = (getValue('ident', $pageOptions) !== false ? getValue('ident', $pageOptions) : preg_replace('/[^\w\._]+/', '', strtolower(getValue('text', $pageOptions))));
                // add link
                $result .= (getValue('ident_selected', $options) == $ident ? str_replace('>', ' class="active">', $root_wrapper[0]) : $root_wrapper[0]) . '<a href="' . getUrl($filename) . '" target="_parent">' . getValue('text', $pageOptions) . '</a>' . $root_wrapper[1] . $nl;
                break;
        }
    }
    $result .= getValue('is_dropdown', $options) !== false ? $child_container[1] : $root_container[1];
    return $result;
}

/**
 * Check if given ident equals $fileIdent
 * @param String $ident
 * @return bool
 */
function isIdent($ident){
    global $fileIdent;
    return (md5($fileIdent) == md5($ident));
}

/**
 * Returns fileIdentPage or default
 * @param $default
 * @return String
 */
function getFileIdentPage($default){
    global $data;
    return (!isEmptyString(getValue('page', $data)) ? getValue('page', $data) : $default);
}

/**
 * Returns action and post/get data as array
 * @return array
 */
function getDataArray(){
    return ($_POST ? $_POST : $_GET);
}

/**
 * Returns action and post/get data as object
 * @return object
 */
function getDataObject(){
    return (object)getDataArray();
}
?>
