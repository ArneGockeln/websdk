<?php
include('includes/application_top.php');

if($_POST){
  $email = getValue('post_email', $_POST);
  $pwd = getValue('post_pwd', $_POST);
  
  $mysql = new mysqlDatabase();
  $mysql->setTable(CFG_DBT_USERS);
  $mysql->setColumns('*');
  $mysql->setRestriction('email', '=', $email);
  
  $dbUser = $mysql->fetchRow();
  
  /**
   * If user is locked, login denied
   */
  if(isset($dbUser->locked) && $dbUser->locked == 1){
    registerMessage(_('Der Benutzer ist gesperrt!'), true);
    redirect('index.php');
  }
  
  if(isset($dbUser->id)){
    $user = new User($dbUser->id);

    $mysql = new mysqlDatabase();
    $mysql->setTable(CFG_DBT_USERS);
    $mysql->setColumns('*');
    $mysql->setSQL("SELECT PASSWORD('" . mysql_real_escape_string($pwd) . "') AS p");
    $test = (object)$mysql->fetchRow();

    if(md5($user->pwd) == md5($test->p)){
      UserSession::register($user->id);

      registerMessage(_('Angemeldung erfolgreich!'));

      redirect('index.php');
    }
  }
  
  registerMessage(_('Anmeldung verweigert!'), true);
} else {
  registerMessage(_('login.php muss per HTTP_POST aufgerufen werden'), true);
}

redirect('index.php');
?>
