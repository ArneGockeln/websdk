<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 03.11.15
 */

global $twig;

/**
 * Check if $right is in $right_list
 * @return bool
 */
$twig->addFunction(new Twig_SimpleFunction('hasUserRight', function($right_list, $right){
    return hasUserRight($right_list, $right);
}, array('right_list', 'right')));