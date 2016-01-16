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
use \WebSDK\Upload;

global $app;

/**
 * Core
 */
$app->get('/', 'route_get_index')->name('index');
$app->get('/login', 'route_get_login')->name('login');
$app->get('/logout', 'route_get_logout');
$app->get('/help', 'route_get_help');
$app->get('/file/:type/:name', 'route_get_file');
$app->post('/login', 'route_post_login');
$app->post('/restorepwd', 'route_post_restore_pwd');
$app->post('/register', 'route_post_register');
$app->post('/upload/:fileType', 'isUserOnline', 'route_post_upload');

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
    if(isUserType(\WebSDK\UserTypeEnum::ADMINISTRATOR)){
        // New Users
        $data['newUsers'] = $mysql->query("SELECT * FROM " . \WebSDK\DBTables::USERS . " u WHERE u.deleted = 0 AND u.type = 1 ORDER BY lastmod ASC LIMIT 10")->fetchList(true);
    }

    renderTemplate('core/index.twig', $data);
}

/**
 * Show Login Form
 */
function route_get_login(){
    renderTemplate('core/login_form.twig', array('loginAttempt' => UserSession::getValue('loginAttempt')));
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
 * Serves files from upload directory to the browser request
 *
 * @param string $type
 * @param string $name
 */
function route_get_file($type, $name){
    try {

        $allowedTypes = array('logo', 'userfile');
        if(!in_array($type, $allowedTypes)){
            throw new Exception(_('Der Dateityp ist nicht erlaubt!'));
        }

        if(is_empty($name)){
            throw new Exception(_('Dateiname darf nicht leer sein!'));
        }

        if(!isSecureFilename($name)){
            throw new Exception(_('Der Dateiname entspricht nicht den Sicherheitsstandards!'));
        }

        $uploadDir = '';
        switch(strtoupper($type)){
            case 'LOGO':
                $uploadDir = getUploadDir('logos');
                break;
            case 'USERFILE':
                $uploadDir = getUploadDir('userfiles');
                break;
        }

        $file = $uploadDir . $name;

        if(is_file($file)){
            $mimeType = getMimeTypeOfFile($file);

            header('Content-Type: ' . $mimeType);
            readfile($file);
            exit;
        }

    } catch(Exception $e){
        flashRedirect(\WebSDK\Routes::INDEX, $e->getMessage(), FlashStatus::ERROR);
    }
}

/**
 * Process Login
 */
function route_post_login(){
    $app = Slim::getInstance();

    // count login attempts
    $loginUserID = 0;
    $loginAttempt = UserSession::getValue('loginAttempt');
    if(is_null($loginAttempt)){
        $loginAttempt = 1;
    } else {
        $loginAttempt += 1;
    }

    try {
        $postVars = $app->request->post();

        // could be Username or eMail
        $inputUsername = trim(getValue($postVars, 'inputUsername'));
        $inputPwd = trim(getValue($postVars, 'inputPwd'));
        $loginByEmail = false;

        if(isSecureEmail($inputUsername)){
            $loginByEmail = true;
        }

        if(!$loginByEmail && !isSecureUsername($inputUsername)){
            throw new Exception(_('Der Benutzername enthält ungültige Zeichen!'));
        }

        if(!isSecurePassword($inputPwd)){
            throw new Exception(_('Passwort ist nicht gültig!'));
        }

        $user = new User();
        if($loginByEmail){
            $user->setEmail($inputUsername);
        } else {
            $user->setUsername($inputUsername);
        }
        $user->load();

        // try to login
        if($user->getId() <= 0){
            throw new Exception(_('Benutzer nicht gefunden!'));
        }

        // for login attempts
        $loginUserID = $user->getId();

        // gelöschter Benutzer
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

        // Reset login attempts
        UserSession::remValue('loginAttempt');

        // perform login
        UserSession::register($user);
    } catch(Exception $e){

        $errorMessage = $e->getMessage();

        // if this is the third login attempt, lock user
        if($loginAttempt > 2){
            // lock user account
            if($loginUserID > 0){
                $user = new User($loginUserID);
                $user->setLocked(1);
                $user->save();

                $body = renderTemplate('email/email_body_admin_max_logins.twig', array('username' => $user->getUsername(), 'userid' => $user->getId(), 'email' => $user->getEmail()), true);
                mailTo(array(
                    'subject' => _('Benutzerkonto wurde gesperrt!'),
                    'body' => nl2br($body),
                    'altbody' => strip_tags($body)
                ));

                UserSession::remValue('loginAttempt');

                $errorMessage = _('Sie haben die maximale Anzahl an Loginversuchen erreicht! Das Benutzerkonto wurde gesperrt und ein Administrator informiert.');
            } else {
                $errorMessage = _('Sie haben die maximale Anzahl an Loginversuchen erreicht! Ein Administrator wurde informiert.');
            }
        } else {
            UserSession::addValue('loginAttempt', $loginAttempt);
        }

        flashRedirect('login', $errorMessage);
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

            UserSession::remValue('loginAttempt');
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
        $salt = getSalt(10);
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

/**
 * Upload files per dropzone js / ajax
 */
function route_post_upload($fileType = 'file'){
    $app = Slim::getInstance();

    try {
        if(!$app->request->isAjax()){
            throw new Exception(_('Request ist kein XHR/Ajax Request'));
        }

        $file = new Upload($_FILES['file']);
        $file->setEnableOverwriteDestFile(true);

        switch(strtoupper($fileType)){
            case 'FILE':
                $file->setMaxFilesize(getOption('opt_core_uploads_userfiles_maxfilesize_in_mb'));
                $file->setAllowMimeTypes(array(
                    'pdf' => 'application/pdf'
                ));
                $file->setUploadDir(getTrailingSlash(CFG_UPLOAD_DIR) . 'userfiles');
                break;
            case 'LOGO':
                $file->setMaxFilesize(getOption('opt_core_uploads_logo_maxfilesize_in_mb'));
                $file->setAllowMimeTypes(array(
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                ));
                $file->setUploadDir(getTrailingSlash(CFG_UPLOAD_DIR) . 'logos');
                break;
        }

        $file->upload();

        ajaxResponse(array('filename' => $file->getDestName(), 'original_filename' => $file->getOriginalFilename()), 200);
    } catch(Exception $e){
        ajaxResponse($e->getMessage(), 400);
    }
}
?>