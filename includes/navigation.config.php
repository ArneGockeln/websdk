<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

$pageTree = new PageTree();
$pageTree->add('index.php', _('Startseite'), 'index');
$pageTree->add('dropdown', _('Einstellungen'), 'settings', array(-1), -1, true);
$pageTree->add('users.php', _('Benutzerverwaltung'), 'users', array(0, 1), 1, true);
$pageTree->add('users.php?page=roles', _('Rechteverwaltung'), 'roles', array(2,3), 1, true);
$pageTree->add('users.php?action=edit&id='. getCurrentUID(), getCurrentUsername(), 'user', array(-1), -1, true);
$pageTree->add('logout.php', _('Abmelden'), 'user', array(-1), -1, true);
?>
