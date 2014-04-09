<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */
include('includes/application_top.php');

/**
 * Variables
 */
$fileIdent = basename(__FILE__);
$action = getValue('action', $_GET);
$data = $_GET;
if ($_POST) {
  $action = getValue('action', $_POST);
  $data = $_POST;
}
$fileIdentPage = (!isEmptyString(getValue('page', $data)) ? getValue('page', $data) : 'users');

/**
 * SECURITY
 */
if (!UserSession::isOnline()) {
  registerMessage(_('Sie müssen angemeldet sein!'), true);
  redirect('index.php');
}

/**
 * FILES / CLASSES
 */

/**
 * ACTIONS
 */
switch ($action) {
  case 'ajaxGeneratepwd':
    $newpwd = generatePassword();
    die(json_encode(array('pwd' => $newpwd)));
    break;
  case 'save':

    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'roles':

        // has right to manage roles?
        if (!hasRights(array(2))) {
          registerMessage(_('Sie benötigen mehr Rechte!'), true);
          redirect($fileIdent . '?page=' . $fileIdentPage);
        }

        if (isEmptyString(getValue('post_name', $data))) {
          registerMessage(_('Name ist ein Pflichtfeld!'), true);
          redirect($fileIdent . '?page=' . $fileIdentPage);
        }

        $role = new UserRole(((int) getValue('post_id', $data) > 0 ? (int) getValue('post_id', $data) : 0));
        $role->name = getValue('post_name', $data);
        if (is_array(getValue('post_role_rights', $data)) && count(getValue('post_role_rights', $data)) > 0) {
          $role->rights = implode(',', getValue('post_role_rights', $data));
        } else {
          // remove rights
          $role->rights = '';
        }

        if ($role->save()) {
          registerMessage(_('Rolle gespeichert!'));
        } else {
          registerMessage(_('Rolle nicht gespeichert!'), true);
        }

        redirect($fileIdent . '?page=' . $fileIdentPage);
        break;
      case 'users':
        $user = new User();
        if ((int) getValue('post_id', $data) > 0) {
          $user->load((int) getValue('post_id', $data));
        } else {
          // userId = 0
          if (!is_email(getValue('post_email', $data))) {
            registerMessage(_('E-Mail ist nicht gültig!'), true);
            redirect($fileIdent . '?action=edit&id=' . getValue('userId', $data));
          }

          // check if email exists
          if (doesEmailExists(getValue('post_email', $data))) {
            registerMessage(_('Ein Benutzer mit dieser E-Mailadresse existiert bereits!'), true);
            redirect($fileIdent . '?action=add');
          }
        }

        if (!is_email(getValue('post_email', $data))) {
          registerMessage(_('E-Mail ist nicht gültig!'), true);
          redirect($fileIdent . '?action=edit&id=' . getValue('userId', $data));
        }

        $user->firstname = getValue('post_firstname', $data);
        $user->lastname = getValue('post_lastname', $data);
        $user->email = getValue('post_email', $data);
        $user->locked = (int) getValue('post_locked', $data);
        
        // CHANGE LOCALE??
        $changeLocale = false;
        if(!isEmptyString(getValue('post_locale', $data)) && $user->locale != getValue('post_locale', $data)){
          $changeLocale = getValue('post_locale', $data);
          $user->locale = $changeLocale;
        }

        // UPDATE ROLES
        if(hasRights(array(0,2))){
          if (is_array(getValue('post_roles', $data)) && count(getValue('post_roles', $data)) > 0) {
            $user->roles = implode(',', getValue('post_roles', $data));
          } else {
            $user->roles = '';
          }
        }

        if ($user->save()) {

          // locale changed?
          if($user->id == getCurrentUID() && $changeLocale !== false){
            UserSession::changeLocale($changeLocale);
          }
          
          if (!isEmptyString(getValue('post_new_pwd', $data))) {
            $user->changePassword(getValue('post_new_pwd', $data));
            
            $body = utf8_decode(sprintf(_('Hallo %1$s, 
Sie haben einen Zugang für %2$s erhalten. Ihre Anmeldedaten lauten:

E-Mail: %3$s
Passwort: %4$s

Unter %5$s können Sie sich anmelden und unter Ihrem Profil können Sie Ihr Passwort ändern.'), $user->firstname . ' ' . $user->lastname, (defined('CFG_APP_NAME') ? CFG_APP_NAME : 'Webchef Filemanager'), $user->email, getValue('post_new_pwd', $data), getHttpHost()));
            
            if(!sendEmail($user->email, $user->firstname . ' ' . $user->lastname, _('Ihre Zugangsdaten'), $body)){
              registerMessage(_('Der Benutzer wurde gespeichert ABER die E-Mail konnte nicht gesendet werden!'), true);
              redirect($fileIdent . '?page=' . $fileIdentPage);
            }
          }

          registerMessage(_('Benutzer gespeichert!'));
        } else {
          registerMessage(_('Es ist ein Fehler aufgetreten!'), true);
        }
        
        if(!hasRights(array(0,1,2,3))){
          // normal user, redirect to downloads
          redirect('index.php');
        }
        break;
    }

    redirect($fileIdent . '?page=' . $fileIdentPage);
    break;
  case 'remrightfromrole':
    // has right to manage roles?
    if (!hasRights(array(2))) {
      registerMessage(_('Sie benötigen mehr Rechte!'), true);
      redirect($fileIdent . '?page=' . $fileIdentPage);
    }

    if ((int) getValue('rightid', $data) > 0 && (int) getValue('roleid', $data) > 0) {
      $role = new UserRole((int) getValue('roleid', $data));
      if ($role->removeRights(array((int) getValue('rightid', $data)))) {
        registerMessage(_('Recht entfernt'));
      }
    }

    redirect($fileIdent . '?page=roles');
    break;
  case 'removerole':

    // has right to manage roles?
    if (!hasRights(array(2))) {
      registerMessage(_('Sie benötigen mehr Rechte!'), true);
      redirect($fileIdent . '?page=' . $fileIdentPage);
    }

    /**
     * Remove role from user
     */
    $userId = (int) getValue('uid', $data);
    $roleId = (int) getValue('id', $data);

    if ($userId > 0 && $roleId > 0) {
      $user = new User($userId);

      // if user id == 1 && user role id == 1
      // admin user can not loose admine role!
      if ($roleId == 1 && $userId == 1) {
        registerMessage(_('Die Adminrolle kann vom Adminbenutzer nicht entfernt werden!'), true);
      } else {
        if ($user->removeRoles(array((int) getValue('id', $data)))) {
          registerMessage(_('Rolle entfernt!'));
        }
      }
    }

    redirect($fileIdent . '?page=' . $fileIdentPage);
    break;
  case 'deleteconfirm':
    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'roles':

        // has right do delete roles?
        if (!hasRights(array(3))) {
          registerMessage(_('Sie benötigen mehr Rechte!'), true);
          redirect($fileIdent . '?page=' . $fileIdentPage);
        }

        if ((int) getValue('id', $data) > 1) { // USER ROLE ID
          $role = new UserRole((int) getValue('id', $data));

          // get all users with this role
          $mysql = new mysqlDatabase(CFG_DBT_USERS);
          $mysql->setSQL("SELECT id, roles FROM ".CFG_DBT_USERS." WHERE FIND_IN_SET(" . (int) $role->id . ", roles)");
          $mysql->setColumns(array('id', 'roles'));
          $mysql->setRestriction('roles', 'IN', $role->id);
          $userList = $mysql->fetchList();
          if ($mysql->hasRows()) {
            foreach ($userList as $int => $userArray) {
              $userRow = (object) $userArray;
              $user = new User($userRow->id);

              // remove user roles
              if ($user->removeRoles(array($role->id))) {
                // nothing but silence
              }
            }
          }

          if ($role->delete()) {
            registerMessage(_('Rolle gelöscht!'));
          } else {
            registerMessage(_('Rolle nicht gelöscht!'), true);
          }
        } else if ((int) getValue('id', $data) == 1) { // USER ROLE ID
          registerMessage(_('Die Adminrolle kann nicht entfernt werden!'), true);
        }

        break;
      case 'users':

        // has right to delete users?
        if (!hasRights(array(1))) {
          registerMessage(_('Sie benötigen mehr Rechte!'), true);
          redirect($fileIdent . '?page=' . $fileIdentPage);
        }

        if ((int) getValue('id', $data) > 1) { // USER ID
          $user = new User((int) getValue('id', $data));

          if ($user->delete()) {
            registerMessage(_('Benutzer gelöscht!'));
          } else {
            registerMessage(_('Es ist ein Fehler aufgetreten!'), true);
          }
        } elseif ((int) getValue('id', $data) == 1) {
          registerMessage(_('Ursprungsbenutzer kann nicht gelöscht werden!'), true);
        } else {
          registerMessage(_('ID ist nicht gegeben!'), true);
        }
        break;
    }

    redirect($fileIdent . '?page=' . $fileIdentPage);
    break;
  case 'lock':
    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'users':

        // has right to manage users?
        if (!hasRights(array(0))) {
          registerMessage(_('Sie benötigen mehr Rechte!'), true);
          redirect($fileIdent . '?page=' . $fileIdentPage);
        }

        if ((int) getValue('id', $data) > 0) {

          if ((int) getValue('id', $data) == getCurrentUID()) {
            registerMessage(_('Sie können sich nicht selbst sperren!'), true);
            redirect($fileIdent);
          }

          if ((int) getValue('id', $data) == 1) {
            registerMessage(_('Der Ursprungsbenutzer kann nicht gelöscht werden!'), true);
            redirect($fileIdent);
          }

          $user = new User((int) getValue('id', $data));
          $user->locked = 1;

          if ($user->save()) {
            registerMessage(_('Benutzer gesperrt!'));
            redirect($fileIdent);
          }
        }
        break;
    }
    break;
  case 'unlock':
    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'users':

        // has right to manage users?
        if (!hasRights(array(0))) {
          registerMessage(_('Sie benötigen mehr Rechte!'), true);
          redirect($fileIdent . '?page=' . $fileIdentPage);
        }

        if ((int) getValue('id', $data) > 0) {
          $user = new User((int) getValue('id', $data));
          $user->locked = 0;

          if ($user->save()) {
            registerMessage(_('Benutzer freigeschaltet!'));
            redirect($fileIdent);
          }
        }
        break;
    }

    break;
}

/**
 * Templates
 */
includeTemplate('header.php');

switch ($action) {
  case 'edit':
    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'roles':
        $role = new UserRole((int) getValue('id', $_GET));
        break;
      case 'users':
        $user = new User((int) getValue('id', $_GET));
        break;
    }

  case 'add':

    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'roles':
        if (!isset($role)) {
          $role = new UserRole();
        }

        includeTemplate('users/role.edit.php');
        break;
      case 'users':
        if (!isset($user)) {
          $user = new User();
        }

        includeTemplate('users/user.edit.php');
        break;
    }

    break;
  default:
    if(!hasRights(array(0,1,2,3))){
      registerMessage(_('Sie benötigen mehr Rechte!'), true);
      redirectHtml('index.php');
    }    
    /**
     * Which page?
     */
    switch ($fileIdentPage) {
      case 'roles':
        includeTemplate('users/roles.php');
        break;
      case 'users':
      default:
        includeTemplate('users/users.php');
        break;
    }

    break;
}

includeTemplate('footer.php');
?>
