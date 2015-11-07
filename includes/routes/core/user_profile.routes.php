<?php
/**

 * Author: Arne Gockeln, WebSDK
 * Date: 31.08.15
 */

use \Slim\Slim;
use \WebSDK\User;
use \WebSDK\UserSession;
use \WebSDK\FlashStatus;
use \WebSDK\Database;

global $app;

/**
 * User Profile
 */
$app->group('/profile', 'isUserOnline', function() use($app){
    $app->get('/', 'route_get_user_profile')->name('user_profile');
    $app->post('/save', 'route_post_user_profile_save')->name('user_profile_save');
});

/**
 * Get Profile Form
 */
function route_get_user_profile(){
    $data = array();

    $user = new User(UserSession::getValue('uid'));
    $data['user'] = classToArray($user);
    $data['locales'] = getLocales();

    renderTemplate('core/user_profile.twig', $data);
}

/**
 * POST Profile form
 */
function route_post_user_profile_save(){
    $app = Slim::getInstance();

    try {
        $postVars = $app->request->post();

        $id = getValue($postVars, 'inputID');
        $inputUsername = getValue($postVars, 'inputUsername');
        $inputFirstname = getValue($postVars, 'inputFirstname');
        $inputLastname = getValue($postVars, 'inputLastname');
        $inputEmail = getValue($postVars, 'inputEmail');
        $inputLocale = getValue($postVars, 'inputLocale');

        $inputPwd1 = getValue($postVars, 'inputPwd1');
        $inputPwd2 = getValue($postVars, 'inputPwd2');

        if($id <= 0){
            throw new Exception(_('ID ist null oder kleiner als 0!'));
        }

        if(strlen($inputUsername) <= 0){
            throw new Exception(_('Der Benutzername wird benötigt!'));
        }

        if(!isSecureUsername($inputUsername)){
            throw new Exception(_('Der Benutzername ist nicht gültig!'));
        }

        if(strlen($inputEmail) <= 0){
            throw new Exception(_('Die E-Mail Adresse wird benötigt!'));
        }

        if(!isSecureEmail($inputEmail)){
            throw new Exception(_('Die E-Mail Adresse ist nicht gültig!'));
        }

        $pwd1len = strlen($inputPwd1);
        $pwd2len = strlen($inputPwd2);
        if($pwd1len > 0 && $pwd2len > 0) {
            if(strcmp($inputPwd1, $inputPwd2) !== 0){
                throw new Exception(_('Die Passwörter stimmen nicht überein!'));
            }

            if(!isSecurePassword($inputPwd1)){
                throw new Exception(_('Das Passwort muss aus mindestens 6 alphanumerischen Zeichen, 1 Großbuchstaben und 1 Kleinbuchstaben bestehen. Die Sonderzeichen !@#$_% sind erlaubt.'));
            }
        }

        if(($pwd1len > 0 && $pwd2len <= 0) || ($pwd1len <= 0 && $pwd2len > 0)){
            throw new Exception(_('Die Passwörter stimmen nicht überein!'));
        }

        $user = new User($id);

        if(!is_empty($inputLocale)){
            if(!is_dir(getTrailingSlash(WDK_LOCALE_PATH) . $inputLocale)){
                throw new Exception(_('Locale ist nicht vorhanden!'));
            }

            $user->setLocale($inputLocale);
        }

        // check if username exists
        if(strcmp($user->getUsername(), $inputUsername) !== 0){
            $mysql = Database::getInstance();
            $count = $mysql->query("SELECT COUNT(*) as c FROM " . \WebSDK\DBTables::USERS . " WHERE username = '" . $inputUsername . "' OR username = '" . strtolower($inputUsername) . "' OR username = '" . strtoupper($inputUsername) . "'")->fetchRow();
            if($count->c > 0){
                throw new Exception(_('Der Benutzername wird bereits verwendet!'));
            }
        }

        $user->setUsername($inputUsername);
        $user->setFirstname($inputFirstname);
        $user->setLastname($inputLastname);
        $user->setEmail($inputEmail);

        if($pwd1len > 0 && $pwd2len > 0){
            if(strcmp($inputPwd1, $inputPwd2) === 0){
                $salt = getSalt(10);
                $user->setPwd(hash(CFG_PASSWORD_HASH_ALGO, $inputPwd1 . $salt));
                $user->setSalt($salt);
            }
        }

        if(!$user->save()){
            throw new Exception(_('Das hat nicht geklappt!'));
        }

    } catch(Exception $e){
        flashRedirect('user_profile', $e->getMessage());
    }

    flashRedirect('user_profile', _('Profil gespeichert!'), FlashStatus::SUCCESS);
}
?>