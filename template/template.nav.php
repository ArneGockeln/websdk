<?php
global $pageTree, $fileIdent, $fileIdentPage;

/**
 * The Page Navigation Tree
 */
$pageTree = array(
    'index.php' => array(
        'text' => _('Dashboard'),
        'rights' => array(-1),
        'index'
    ),
    'dropdown' => array(
        'text' => _('Einstellungen'),
        'rights' => array(-1),
        'require_login' => true,
        'childs' => array(
            'users.php' => array(
                'text' => _('Benutzerverwaltung'),
                'ident' => 'users',
                'rights' => array(0, 1),
                'require_login' => true
            ),
            'users.php?page=roles' => array(
                'text' => _('Rechteverwaltung'),
                'ident' => 'roles',
                'rights' => array(2,3),
                'require_login' => true
            )
        )
    ),
    'users.php?action=edit&id=' . getCurrentUID() => array(
        'text' => getCurrentUsername(),
        'ident' => 'user',
        'rights' => array(-1),
        'require_login' => true
    ),
    'logout.php' => array(
        'text' => _('Abmelden'),
        'ident' => 'user',
        'rights' => array(-1),
        'require_login' => true
    )
);

if(is_online()){
  echo nav_walker(array(
      'pageTree' => $pageTree,
      'root_container' => '<ul class="nav navbar-nav">|</ul>',
      'child_container' => '<ul class="dropdown-menu" role="menu">|</ul>',
      'ident_selected' => $fileIdentPage
  ));
}
?>
