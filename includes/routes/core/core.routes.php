<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */
use \Slim\Slim;
use \WebSDK\User;
use \WebSDK\UserSession;
use \WebSDK\FlashStatus;
use \WebSDK\Database;

global $app;

/**
 * Core
 */
$app->get('/', 'route_get_index')->name('index');
$app->get('/login', 'route_get_login')->name('login');
$app->get('/logout', 'route_get_logout');
$app->get('/help', 'route_get_help');
$app->post('/login', 'route_post_login');
$app->post('/restorepwd', 'route_post_restore_pwd');
$app->post('/register', 'route_post_register');

/**
 * Show Index Content
 */
function route_get_index(){
    $app = Slim::getInstance();

    if(!UserSession::isOnline()){
        $app->redirectTo('login');
    }

    $mysql = Database::getInstance();
    $data = array();

    // is Administrator?
    $isAdmin = false;
    if(isUserType(\WebSDK\UserTypeEnum::ADMINISTRATOR)){
        $isAdmin = true;
        // New Users
        $data['newUsers'] = $mysql->query("SELECT * FROM " . \WebSDK\DBTables::USERS . " u WHERE u.deleted = 0 AND u.type = 1 ORDER BY lastmod ASC LIMIT 10")->fetchList(true);
    }

    renderTemplate('core/index.twig', $data);
}

/**
 * Show Login Form
 */
function route_get_login(){
    renderTemplate('core/login_form.twig');
}

/**
 * Logout
 */
function route_get_logout(){
    $app = Slim::getInstance();
    if(UserSession::destroy(UserSession::getValue('uid'))){
        flashMessage(_('Erfolgreich abgemeldet!'), FlashStatus::SUCCESS);
        $app->redirectTo('login');
    }

    flashMessage(_('Das hat nicht geklappt!'));
    $app->redirectTo('index');
}

/**
 * Show Help Page
 */
function route_get_help(){
    $app = Slim::getInstance();

    if(!UserSession::isOnline()){
        $app->redirectTo('login');
    }

    renderTemplate('app/help.twig');
}

/**
 * Process Login
 */
function route_post_login(){
    $app = Slim::getInstance();

    try {
        $postVars = $app->request->post();

        $inputUsername = trim(getValue($postVars, 'inputUsername'));
        $inputPwd = trim(getValue($postVars, 'inputPwd'));

        if(!isSecureUsername($inputUsername)){
            throw new Exception(_('Benutzername ist nicht gültig!'));
        }

        if(!isSecurePassword($inputPwd)){
            throw new Exception(_('Passwort ist nicht gültig!'));
        }

        $user = new User();
        $user->setUsername($inputUsername);
        $user->load();

        // try to login
        if($user->getId() <= 0){
            throw new Exception(_('BenutzerID ist null oder Benutzer nicht gefunden!'));
        }

        if($user->getDeleted() == 1){
            throw new Exception(_('Bitte kontaktieren Sie einen Administrator!'));
        }

        // is user locked?
        if($user->getLocked() == 1){
            throw new Exception(_('Der Benutzer ist gesperrt. Bitte kontaktieren Sie einen Administrator!'));
        }

        // Password check
        $checkPwd = hash(CFG_PASSWORD_HASH_ALGO, $inputPwd . $user->getSalt());
        if($user->getPwd() != $checkPwd){
            throw new Exception(_('Das Passwort ist falsch!'));
        }

        // perform login
        UserSession::register($user);
    } catch(Exception $e){
        flashRedirect('login', $e->getMessage());
    }

    flashRedirect('index', _('Erfolgreich angemeldet!'), FlashStatus::SUCCESS);
}

/**
 * Process restore password
 */
function route_post_restore_pwd(){
    $app = Slim::getInstance();

    try {
        $postVars = $app->request->post();
        $inputUsername = getValue($postVars, 'inputUsername');
        if(!isSecureUsername($inputUsername)){
            throw new Exception(_('Benutzername ist nicht gültig!'));
        }

        // find user
        $user = new User();
        $user->setUsername($inputUsername);
        $user->load();
        if($user->getId() <= 0){
            throw new Exception(_('Der Benutzer wurde nicht gefunden!'));
        }

        if(!isSecureEmail($user->getEmail())){
            throw new Exception(_('Die E-Mail Adresse ist nicht gültig!'));
        }

        $newPassword = getRandomPassword();
        $salt = getSalt();
        $user->setPwd(hash(CFG_PASSWORD_HASH_ALGO, $newPassword . $salt));
        $user->setSalt($salt);

        if($user->save()){
            // Send mail to user
            $body = renderTemplate('email/email_body_restore_pwd.twig', array('username' => $user->getUsername(), 'newpassword' => $newPassword), true);
            mailTo(array(
                'subscriber' => $user->getEmail(),
                'subject' => _('Ihr neues Passwort'),
                'body' => nl2br($body),
                'altbody' => strip_tags($body)
            ));
        }

    } catch(Exception $e){
        flashRedirect('restorepwd', $e->getMessage());
    }

    flashRedirect('login', _('Das neue Passwort wird zugesendet.'), FlashStatus::SUCCESS);
}

/**
 * Process user registration
 */
function route_post_register(){
    $app = Slim::getInstance();

    try {
        $postVars = $app->request->post();
        $inputUsername = getValue($postVars, 'inputUsername');
        $inputEmail = getValue($postVars, 'inputEmail');
        $inputFirstname = getValue($postVars, 'inputFirstname');
        $inputLastname = getValue($postVars, 'inputLastname');

        if(!isSecureEmail($inputEmail)){
            throw new Exception(_('Die E-Mail Adresse ist nicht gültig!'));
        }

        if(!isSecureUsername($inputUsername)){
            throw new Exception(_('Benutzername ist nicht gültig!'));
        }

        if(!is_username_available($inputUsername)){
            throw new Exception(_('Der Benutzername ist bereits vergeben!'));
        }

        if(!is_email_available($inputEmail)){
            throw new Exception(_('Ein Konto mit dieser E-Mail Adresse besteht bereits! Haben Sie Ihr Passwort vergessen?'));
        }

        $user = new User();
        $user->setType(\WebSDK\UserTypeEnum::USER);
        $user->setUsername($inputUsername);
        $user->setEmail($inputEmail);
        $user->setFirstname($inputFirstname);
        $user->setLastname($inputLastname);

        $newPassword = getRandomPassword();
        $salt = getSalt();
        $user->setPwd(hash(CFG_PASSWORD_HASH_ALGO, $newPassword . $salt));
        $user->setSalt($salt);

        $user->setLocked(1);

        if($user->save()){
            $body = renderTemplate('email/email_body_admin_register_user.twig', array('username' => $user->getUsername(), 'email' => $user->getEmail()), true);
            mailTo(array(
                'subject' => _('Ein neuer Benutzer wartet auf die Freischaltung!'),
                'body' => nl2br($body),
                'altbody' => strip_tags($body)
            ));
        }

    } catch(Exception $e){
        flashRedirect('login', $e->getMessage());
    }

    flashRedirect('login', _('Die Registrierung war erfolgreich! Sobald Ihr Konto freigeschaltet wurde, erhalten Sie ein Passwort per E-Mail.'), FlashStatus::SUCCESS);
}
?>