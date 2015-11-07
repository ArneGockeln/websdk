<?php
/**
 * iqz
 * Author: Arne Gockeln, WebSDK
 * Date: 03.11.15
 */

global $twig;

/**
 * Get UserType Name for Frontend
 */
$twig->addFunction(new Twig_SimpleFunction('getUserType', function($id){
    $userTypes = \WebSDK\UserTypeEnum::getNames();
    return getValue($userTypes, $id);
}), array('id'));

/**
 * Check if a variable is empty
 */
$twig->addFunction(new Twig_SimpleFunction('is_empty', function($mixed){
    return is_empty($mixed);
}, array('mixed')));

/**
 * Debugging
 */
$twig->addFunction(new Twig_SimpleFunction('debug', function($mixed) {
    debug($mixed);
}), array('mixed'));

/**
 * Translate bootstrap alert status
 */
$twig->addFunction(new Twig_SimpleFunction('transAlert', function($status){
    $locale = '';
    $lower_status = strtolower($status);
    switch($lower_status){
        case 'success':
            $locale = _('Erfolg');
            break;
        case 'error':
        case 'danger':
            $locale = _('Fehler');
            break;
        case 'info':
            $locale = _('Info');
            break;
        case 'warning':
            $locale = _('Hinweis');
            break;
        default:
            $locale = $status;
            break;
    }
    return $locale;
}), array('status'));