<?php
/**
 * Template: roles.php
 * Description: lists user roles
 */
global $fileIdent, $fileIdentPage, $pages, $WebArbyteRights;

$mysql = new mysqlDatabase(CFG_DBT_USER_ROLES);
$mysql->setColumns('*');
if(!isEmptyString(getValue('search', $_GET))){
  $mysql->setRestriction('name', 'LIKE', '%' . getValue('search', $_GET) . '%', 'OR');
}
$list = $mysql->fetchList();
?>
<div class="row">
  <h2><?php echo _('Rechteverwaltung'); ?> <small>(<?php echo $mysql->rowCount(); ?>)</small></h2>
  
  <div class="row">&nbsp;</div>
  
  <table class="table table-hover">
    <thead>
      <tr>
        <th><?php echo _('Name'); ?></th>
        <th><?php echo _('Rechte'); ?></th>
        <th><?php echo _('Löschen'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php
      if($mysql->hasRows()){
        foreach($list as $int => $row){
          $role = (object)$row;
          ?>
      <tr>
        <td><a href="<?php echo getUrl($fileIdent . '?page=' . $fileIdentPage . '&action=edit&id=' . $role->id); ?>"><?php echo $role->name; ?></a></td>
        <td><?php if(!isEmptyString($role->rights)) { ?>
          <button type="button" class="btn btn-xs btn-default" data-toggle="subrows" data-subrowident="rights<?php echo $role->id; ?>"><?php echo _('Rechte'); ?></button>
          <?php } ?></td>
        <td><?php if(hasRights(array(3))) { ?><a href="#" class="btn btn-xs btn-danger" data-deleteurl="<?php echo getUrl($fileIdent . '?page=' . $fileIdentPage . '&action=deleteconfirm&id=' . $role->id); ?>" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash" data-toggle="tooltip" data-placement="top" title="<?php echo _('Rolle löschen?'); ?>"></span></a><?php } ?></td>
      </tr>
      <?php
          if(!isEmptyString($role->rights)){
            $rights = explode(',', $role->rights);
            foreach($rights as $int => $right_id){
              ?>
      <tr class="rights<?php echo $role->id; ?>" style="display:none;">
        <td colspan="2">>&nbsp;<?php echo $WebArbyteRights[$right_id]; ?></td>
        <td><a href="#" class="btn btn-xs btn-danger" data-deleteurl="<?php echo getUrl($fileIdent . '?page='.$fileIdentPage . '&action=remrightfromrole&rightid=' . $right_id . '&roleid=' . $role->id); ?>" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash" data-toggle="tooltip" data-placement="top" title="<?php echo _('Entferne Recht von Rolle?'); ?>"></span></a></td>
      </tr>
      <?php
            }
          }
        }
      }
      ?>
    </tbody>
  </table>
</div>
