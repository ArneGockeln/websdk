<?php 
/**
 * Template: role.edit.php
 * Description: Add or Edit Roles
 */
global $role, $WebArbyteRights, $fileIdent, $fileIdentPage;
?>
<div class="row">      
  <form class="form-horizontal selectAllOnListBeforeSubmitHook" data-selectall-list="post_role_rights" method="post" action="<?php echo getUrl($fileIdent); ?>" role="form" id="roleForm">
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="page" value="<?php echo $fileIdentPage; ?>"/>
    <input type="hidden" name="post_id" value="<?php echo $role->id; ?>"/>
    <div class="col-md-7">
      <h2><?php echo _('Rollen') . ' > ' . (isAddPage() ? _('hinzufügen') : _('bearbeiten')); ?></h2>
        
      <div class="row">&nbsp;</div>
  
      <div class="form-group">
        <label for="post_name" class="col-sm-3 control-label"><?php echo _('Name'); ?>:</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="post_name" name="post_name" placeholder="Name" value="<?php echo $role->name; ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label for="post_rights_select" class="col-sm-3 control-label"><?php echo _('Verfügbare Rechte'); ?>:</label>
        <div class="col-sm-9">
          <div class="input-group">
            <select name="post_rights_select" size="10" class="form-control" multiple="true" id="post_rights_select">
              <?php 
              $assignedRights = array();
              if(!isEmptyString($role->rights)){
                $assignedRights = explode(',', $role->rights);
              }
              foreach($WebArbyteRights as $right_id => $rightname){
                if(in_array($right_id, $assignedRights)){
                  continue;
                }
                ?>
              <option value="<?php echo $right_id; ?>"><?php echo $rightname; ?></option>
              <?php } ?>
            </select>
            <span class="input-group-btn">
              <button type="button" class="btn btn-default addSelectedToListBtn" data-from-list="post_rights_select" data-to-list="post_role_rights" data-toggle="tooltip" data-placement="top" title="<?php echo _('Füge Recht hinzu'); ?>"><span class="glyphicon glyphicon-plus"></span></button>
            </span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="post_role_rights" class="col-sm-3 control-label"><?php echo _('Zugewiesene Rechte'); ?>:</label>
        <div class="col-sm-9">
          <div class="input-group">
            <select name="post_role_rights[]" size="10" class="form-control" multiple="true" id="post_role_rights">
              <?php foreach($WebArbyteRights as $right_id => $rightname){ 
                if(!in_array($right_id, $assignedRights)){
                  continue;
                }
                ?>
              <option value="<?php echo $right_id; ?>"><?php echo $rightname; ?></option>
              <?php } ?>
            </select>
            <span class="input-group-btn">
              <button type="button" class="btn btn-default addSelectedToListBtn" data-from-list="post_role_rights" data-to-list="post_rights_select" data-toggle="tooltip" data-placement="top" title="<?php echo _('Entferne Recht'); ?>"><span class="glyphicon glyphicon-minus"></span></button>
            </span>
          </div>
        </div>
      </div>
      
      
      <div class="form-group">
        <div class="col-lg-offset-3 col-sm-9">
          <a href="<?php echo getUrl($fileIdent . '?page=' . $fileIdentPage); ?>" class="btn btn-default"><?php echo _('Abbrechen'); ?></a>
          <button type="submit" class="btn btn-primary"><?php echo _('Speichern'); ?></button>
        </div>
      </div>
    </div>
  </form>
</div>