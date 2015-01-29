<?php
/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 * 
 * Add your custom functions here
 */
function checkOnline(){
    if(!is_online()) die('You need to be online!');
}
?>