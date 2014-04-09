<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

include 'includes/application_top.php';

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
$fileIdentPage = (!isEmptyString(getValue('page', $data)) ? getValue('page', $data) : 'index');

/**
 * Functions
 */
/**
 * ACTIONS
 */
switch ($action) {
  /*case 'edit':
   * // do something
    break;*/
}

/**
 * Templates
 */
includeTemplate('header.php');

switch($action){
  default:
    includeTemplate('index.php');
    break;
}

includeTemplate('footer.php');
?>
