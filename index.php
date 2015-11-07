<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */

require_once 'includes/app_top.php';

use WebSDK\UserSession;
use WebSDK\UserTypeEnum;
use WebSDK\BootstrapNavbar;
use WebSDK\BootstrapListItem;

global $app;
$isAdmin = isUserType(UserTypeEnum::ADMINISTRATOR);

/**
 * Top Menu
 */
$leftMenu = new BootstrapNavbar();
$rightMenu = new BootstrapNavbar();

$leftMenu->add((new BootstrapListItem('/', _('Übersicht')))->prepend('<i class="fa fa-area-chart"></i> '));
if(UserSession::isOnline()){
    $rightMenu->add((new BootstrapListItem('#', _('Hilfe')))->prepend('<i class="fa fa-question btn-question"></i> '));

    if($isAdmin){
        $options = $rightMenu->add(new BootstrapListItem('#', '<i class="fa fa-cogs"></i>'));
            $options->add(new BootstrapListItem('/options', _('Einstellungen')));
            $options->add(new BootstrapListItem('/users', _('Benutzer')));
    }

    $profile = $rightMenu->add((new BootstrapListItem('#', ''))->prepend('<i class="fa fa-user"></i> '));
        $profile->add(new BootstrapListItem('/profile', _('Profil')));
        $profile->add(new BootstrapListItem('/logout', _('Abmelden')));
}

$menus = array(
    'leftMenu' => $leftMenu->render('ul'),
    'rightMenu' => $rightMenu->render('ul')
);

/**
 * Globals
 */
global $twig;
$twig->addGlobal('menus', $menus);
$twig->addGlobal('isAdmin', $isAdmin);


/**
 * Load Core Routes
 */
loadCoreRoutes();

/**
 * Load App Routes
 */
loadAppRoutes();

/**
 * Run Application
 */
$app->run();
?>