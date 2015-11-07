<?php
/**
 *
 * Author: Arne Gockeln, WebSDK
 * Date: 17.09.15
 */

use \WebSDK\UserSession;

/**
 * Check online status
 * if not online, redirect to login form
 */
function isUserOnline(){
    if(!UserSession::isOnline()){
        flashRedirect('login', _('Sie müssen angemeldet sein!'));
    }
}

/**
 * Check if user is type of UserTypeEnum $type
 * @param int $type
 * @param string $redirectTo optional, if set to route name and return would be false, it will be routed to routename
 * @return bool
 */
function isUserType($type, $redirectTo = null){
    $currentUser = UserSession::getUser();
    if($currentUser->getType() == $type){
        return true;
    }

    if(!is_null($redirectTo)){
        flashRedirect($redirectTo, _('Sie benötigen mehr Rechte um diese Seite zu benutzen!'), \WebSDK\FlashStatus::ERROR);
    }

    return false;
}

/**
 * Check if user is authorised and has the needed rights
 * @param array|int $need_right_list list of rights
 * @param string|null $redirectTo optional, route to redirect if the user is not authorised
 * @return bool
 */
function isUserAuthorised($need_right_list, $redirectTo = null){
    $currentUser = UserSession::getUser();
    if(hasUserRight($currentUser->getRights(), $need_right_list)){
        return true;
    }

    if(!is_null($redirectTo)){
        flashRedirect($redirectTo, _('Sie benötigen mehr Rechte um diese Seite zu benutzen!'), \WebSDK\FlashStatus::ERROR);
    }

    return false;
}

/**
 * Check if $password has a minimum of 6 characters, at least 1 upper case char, at least 1 lower case char and at least 1 number with no spaces.
 * Returns true: passwOrd1 | Pa$$word2 | pA!@#$%3
 * Returns false: 1stpassword | $password# | pass word | pAssword
 * @param string $password
 * @return bool
 */
function isSecurePassword($password){
    if(preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[\\w!@#$%]{6,}$/", $password)){
        return true;
    }
    return false;
}

/**
 * Check if $username len > 0 && username is alphanumeric, 1-15 characters long and contains no special characters other than underscore.
 * @param string $username
 * @return bool|int
 */
function isSecureUsername($username){
    if(preg_match("/^(?=.*[a-zA-Z]{1,})(?=.*[\\d]{0,})[a-zA-Z0-9_]{1,15}$/i", $username)){
        return true;
    }

    return false;
}

/**
 * Check if $filename validates agains [a-zA-Z0-9_-].routes.php
 * @param $filename
 * @return bool
 */
function isRouteFile($filename){
    if(preg_match("/([a-zA-Z0-9_-]+\\.routes\\.php)$/", $filename)){
        return true;
    }
    return false;
}

/**
 * Test string against regex, default validates a-zA-Z0-9, umlauts, whitespace, !,.:_?-/#@
 * @param string $string
 * @param string $allowedRegex default ist /^[a-zA-Z0-9äöüÄÖÜß\\s\\!\\.,\\:_\\?\\-\\/\\#\\@]*$/u
 * @return bool
 */
function isSecureString($string, $allowedRegex = "/^[a-zA-Z0-9äöüÄÖÜß\\s\\!\\.,\\:_\\?\\-\\/\\#\\@]*$/u"){
    if(strlen(trim($string)) > 0) {
        if(preg_match($allowedRegex, trim($string))){
            return true;
        }
    }

    return false;
}

/**
 * Returns true if $string is a valid hostname
 * @param string $string
 * @return bool
 */
function isSecureHost($string){
    return isSecureString($string, "/^[a-zA-Z0-9\\._\\-\\/\\:]*$/u");
}

/**
 * Check if string is a valid email address
 * @param string $email
 * @return boolean
 */
function isSecureEmail($email) {
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
 * Check if the user has all of the given rights
 * @param array|string $user_rights
 * @param array|string $rights
 * @return bool
 * @throws Exception
 */
function hasUserRight($user_rights, $rights){
    // get array of rights
    $rights_array = getArrayFromMixed($rights);
    // get array of user rights
    $user_rights_array = getArrayFromMixed($user_rights);
    // convert array values to int
    convertArrayValueToInt($rights_array);
    convertArrayValueToInt($user_rights_array);

    $match = 0;
    if(!is_empty($rights_array)){
        foreach($rights_array as $int => $checkRight){
            if(in_array($checkRight, $user_rights_array)){
                $match++;
            }
        }
    }

    return $match == count($rights_array);
}

/**
 * Add one or more rights to the end of $user_rights
 * @param array|string|int $user_rights reference, the user right list will NOT change its origin type
 * @param array|int $rights
 * @throws Exception
 */
function addUserRight(&$user_rights, $rights){
    // get array of new rights
    $rights_array = getArrayFromMixed($rights);
    // get array of current user rights
    $user_rights_array = getArrayFromMixed($user_rights);

    if(!is_empty($rights_array)){
        $temp_array = array();
        foreach($rights_array as $int => $right){
            if(!hasUserRight($user_rights_array, $right)){
                $temp_array[] = $right;
            }
        }

        // Update with respect to the reference type
        updateMixedListWithType($user_rights, $temp_array);
    }
}

/**
 * Remove one or more rights from $user_rights
 * @param array|string|int $user_rights
 * @param array|string|int $right
 * @throws Exception
 */
function removeUserRight(&$user_rights, $right){
    // get array of current user rights
    $user_rights_array = getArrayFromMixed($user_rights);
    // get array of remove right
    $right_array = getArrayFromMixed($right);

    if(!is_empty($user_rights_array)){
        foreach($user_rights_array as $int => $checkRight){
            if(in_array($checkRight, $right_array)){
                unset($user_rights_array[$int]);
            }
        }

        // Update with respect to the reference type
        updateMixedListWithType($user_rights, $user_rights_array);
    }
}

/**
 * API Middleware to restrict access only to allowed origins
 * Origins can be configured in the options panel!
 * @return bool
 * @throws Exception
 *
 * @ToDo: return false by default and only for allowed origins return true!
 */
function isOriginAllowed(){
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

    $app = Slim::getInstance();

    // deny access from other hosts
    // can be configured in options menu
    $allowedOrigins = array();
    $allowedOrigins[] = getHttpHost();
    $optionAllowedOrigins = getOption('OPT_CORE_API_ALLOWED_ORIGINS');
    if(!is_empty($optionAllowedOrigins)){
        $optionAllowedOrigins = getArrayFromMixed($optionAllowedOrigins);
        foreach($optionAllowedOrigins as $int => $allowedHost){
            $allowedOrigins[] = getTrailingSlash(trim($allowedHost));
        }
    }

    $origin = getHttpOrigin();
    if(!is_empty($origin)){
        if(!in_array(getTrailingSlash($origin), $allowedOrigins)){
            ajaxResponse(sprintf(_('HTTP_ORIGIN %s ist nicht erlaubt!'), $origin), 400);
            return false;
        }
    }

    // if this is an options request, return true
    if($app->request->isOptions()){
        return true;
    }

    return true;
}
?>