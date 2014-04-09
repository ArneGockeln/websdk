<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */
include('includes/application_top.php');

if(UserSession::isOnline()){
  UserSession::destroy();
}

redirect('index.php');
?>
