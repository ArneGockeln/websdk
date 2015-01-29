<?php
checkOnline();
/**
 * Template: users.php
 * Description: lists users
 */
global $fileIdent, $fileIdentPage, $pages;

$mysql = new mysqlDatabase();
$mysql->setTable(CFG_DBT_USERS);
$mysql->setColumns('*');

if(!isEmptyString(getValue('search', $_GET))){
  $mysql->setRestriction('firstname', 'LIKE', '%' . getValue('search', $_GET) . '%', 'OR');
  $mysql->setRestriction('lastname', 'LIKE', '%' . getValue('search', $_GET) . '%', 'OR');
  $mysql->setRestriction('email', 'LIKE', '%' . getValue('search', $_GET) . '%', 'OR');
}

$list = $mysql->fetchList();


?>
<div class="row">
  <h2><?php echo _('Benutzerverwaltung'); ?> <small>(<?php echo $mysql->rowCount(); ?>)</small>
      <a href="<?php echo getUrl($fileIdent, array(
          'page' => $fileIdentPage,
          'action' => 'add'
      )); ?>" data-toggle="tooltip" data-placement="bottom" class="btn btn-primary pull-right" title="<?php echo _('Hinzufügen'); ?>"><span class="glyphicon glyphicon-plus"></span></a>
  </h2>

  <div class="row">&nbsp;</div>

  <table class="table table-hover">
    <thead>
      <tr>
        <th><?php echo _('Vorname'); ?></th>
        <th><?php echo _('Nachname'); ?></th>
        <th><?php echo _('E-Mail'); ?></th>
        <th><?php echo _('Gesperrt'); ?></th>
        <?php if(hasRights(array(2))) { ?><th><?php echo _('Rollen'); ?></th><?php } ?>
        <th><?php echo _('letzte Änderung'); ?></th>
        <?php if(hasRights(array(1))) { ?><th><?php echo _('Löschen'); ?></th><?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php
      if($mysql->hasRows()){
        foreach ($list as $int => $user) {
          $user = (object) $user;
          ?>
          <tr>
            <td><?php echo $user->firstname; ?></td>
            <td><?php echo $user->lastname; ?></td>
            <td><a href="<?php echo getUrl('users.php?action=edit&id=' . $user->id); ?>" data-toggle="tooltip" data-placement="top" title="<?php echo _('Benutzer bearbeiten'); ?>"><?php echo $user->email; ?></a></td>
            <td>
              <?php if ($user->locked == 1) { ?>
                <a href="<?php echo getUrl('users.php?action=unlock&id=' . $user->id); ?>" class="btn btn-xs btn-warning" data-toggle="tooltip" data-placement="top" title="<?php echo _('Benutzer ist gesperrt'); ?>"><span class="glyphicon glyphicon-eye-close"></span></a>
              <?php } else { ?>
                <a href="<?php echo getUrl('users.php?action=lock&id=' . $user->id); ?>" class="btn btn-xs btn-success" data-toggle="tooltip" data-placement="top" title="<?php echo _('Benutzer ist freigeschaltet'); ?>"><span class="glyphicon glyphicon-eye-open"></span></a>  
              <?php } ?>
            </td>
            <?php if(hasRights(array(2))) { ?><td><?php if(!isEmptyString($user->roles)) { ?><button type="button" class="btn btn-xs btn-default" data-toggle="subrows" data-subrowident="roles<?php echo $user->id; ?>"><?php echo _('Rollen'); ?></button><?php } else { ?><?php echo _('Keine Rollen zugewiesen'); ?><?php } ?></td><?php } ?>
            <td><?php echo $user->lastmod; ?></td>
            <?php if(hasRights(array(1))) { ?><td><a href="#" class="btn btn-xs btn-danger" data-deleteurl="<?php echo getUrl($fileIdent . '?page='.$fileIdentPage . '&action=deleteconfirm&id=' . $user->id); ?>" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash" data-toggle="tooltip" data-placement="top" title="<?php echo _('Benutzer löschen'); ?>?"></span></a></td><?php } ?>
          </tr>
        <?php
        if(hasRights(array(2))) {
          if(!isEmptyString($user->roles)){
            $mysql = new mysqlDatabase(CFG_DBT_USER_ROLES);
            $mysql->setColumns('*');
            $mysql->setRestriction('id', 'IN', $user->roles);
            $roles = $mysql->fetchList();
            if($mysql->hasRows()) foreach($roles as $int => $roleArray){
              $role = (object)$roleArray; ?>
            <tr class="roles<?php echo $user->id; ?>" style="display:none;">
              <td colspan="6">>&nbsp;<a href="<?php echo getUrl($fileIdent . '?page=roles&action=edit&id=' . $role->id); ?>"><?php echo $role->name; ?></a></td>
              <td><a href="#" class="btn btn-xs btn-danger" data-deleteurl="<?php echo getUrl($fileIdent . '?page='.$fileIdentPage . '&action=removerole&id=' . $role->id . '&uid=' . $user->id); ?>" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash" data-toggle="tooltip" data-placement="top" title="<?php echo _('Rolle entfernen'); ?>?"></span></a></td>
            </tr>
            <?php }
          }
        }
        }
      }
      ?>
    </tbody>
  </table>
</div>