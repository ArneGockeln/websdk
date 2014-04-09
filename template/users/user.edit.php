<?php
/**
 * Template: user.edit.php
 * Description: Add or Edit users
 */
global $user, $fileIdent, $fileIdentPage, $localeList;
?>
<script type="text/javascript">
  $(function() {
    $('#generatePwdBtn').click(function(){
      $('#post_new_pwd').prop('type', 'text');
    
      $.post('<?php echo getUrl($fileIdent); ?>', {action:'ajaxGeneratepwd'} , function(result){
        $('#post_new_pwd').val(result.pwd);
      }, "json");
    });
  });
</script>
<div class="row">  
  <form class="form-horizontal selectAllOnListBeforeSubmitHook" data-selectall-list="assignedRoles" method="post" action="<?php echo getUrl('users.php'); ?>" role="form" id="userForm">
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="page" value="<?php echo $fileIdentPage; ?>"/>
    <input type="hidden" name="post_id" value="<?php echo $user->id; ?>"/>
    <div class="col-md-7">
      <h2><?php echo _('Benutzer'); ?> > <?php echo (isAddPage() ? _('hinzufügen') : _('bearbeiten')); ?></h2>

      <div class="row">&nbsp;</div>

      <div class="form-group">
        <label for="post_locale" class="col-sm-3 control-label"><?php echo _('Sprache'); ?></label>
        <div class="col-sm-9">
          <select name="post_locale" class="form-control">
            <?php foreach($localeList as $int => $locale){ ?>
            <option value="<?php echo $locale; ?>" <?php echo ($locale == $user->locale ? 'selected' : ''); ?>><?php echo $locale; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label for="post_firstname" class="col-sm-3 control-label"><?php echo _('Vorname'); ?></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="post_firstname" id="post_firstname" placeholder="<?php echo _('Vorname'); ?>" value="<?php echo $user->firstname; ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="post_lastname" class="col-sm-3 control-label"><?php echo _('Nachname'); ?></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="post_lastname" id="post_lastname" placeholder="<?php echo _('Nachname'); ?>" value="<?php echo $user->lastname; ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="post_email" class="col-sm-3 control-label"><?php echo _('E-Mail'); ?></label>
        <div class="col-sm-9">
          <input type="email" class="form-control" name="post_email" id="post_email" placeholder="<?php echo _('E-Mail'); ?>" value="<?php echo $user->email; ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="post_new_pwd" class="col-sm-3 control-label"><?php echo _('Passwort'); ?></label>
        <div class="col-sm-9">
          <div class="input-group">
            <input type="password" class="form-control" name="post_new_pwd" id="post_new_pwd" autocomplete="off" placeholder="<?php echo _('Passwort'); ?>">
            <span class="input-group-btn">
              <button class="btn btn-default" id="generatePwdBtn" type="button"><?php echo _('Generieren'); ?></button>
            </span>
          </div>
        </div>
      </div>
      <?php if (hasRights(array(0))) { ?>
        <div class="form-group">
          <label for="post_locked" class=" col-sm-3 control-label"><?php echo _('Ist gesperrt'); ?>?:</label>
          <div class="col-sm-9">
            <div class="checkbox">
              <label>
                <input type="checkbox" name="post_locked" value="1" <?php echo ($user->locked == 1 ? 'checked' : ''); ?>> <?php echo _('Ja'); ?>
              </label>
            </div>
          </div>
        </div>
        <?php if (hasRights(array(2))) { ?>
          <div class="form-group">
            <label for="post_roles_available" class="col-sm-3 control-label"><?php echo _('Verfügbare Rollen'); ?>:</label>
            <div class="col-sm-9">
              <div class="input-group">
                <select name="post_roles_available" class="form-control" size="5" multiple="true" id="availableRoles">
                  <?php
                  $userRoles = array();
                  if (!isEmptyString($user->roles)) {
                    $userRoles = explode(',', $user->roles);
                  }

                  $mysql = new mysqlDatabase(CFG_DBT_USER_ROLES);
                  $mysql->setColumns('*');
                  $roles = $mysql->fetchList();
                  if ($mysql->hasRows())
                    foreach ($roles as $int => $roleArray) {
                      $role = (object) $roleArray;
                      if (!in_array($role->id, $userRoles)) {
                        ?>
                        <option value="<?php echo $role->id; ?>"><?php echo $role->name; ?></option>
                      <?php }
                    }
                  ?>
                </select>
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default addSelectedToListBtn" data-from-list="availableRoles" data-to-list="assignedRoles" id="addRoleToUserBtn" data-toggle="tooltip" data-placement="top" title="<?php echo _('Füge Rolle hinzu'); ?>"><span class="glyphicon glyphicon-plus"></span></button>
                </span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="post_roles" class="col-sm-3 control-label"><?php echo _('Zugewiesene Rollen'); ?>:</label>
            <div class="col-sm-9">
              <div class="input-group">
                <select name="post_roles[]" class="form-control" size="5" multiple="true" id="assignedRoles">
                  <?php
                  if ($mysql->hasRows())
                    foreach ($roles as $int => $roleArray) {
                      $role = (object) $roleArray;
                      if (in_array($role->id, $userRoles)) {
                        ?>
                        <option value="<?php echo $role->id; ?>"><?php echo $role->name; ?></option>
        <?php }
      }
    ?>
                </select>
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default addSelectedToListBtn" data-from-list="assignedRoles" data-to-list="availableRoles" id="removeRoleFromUserBtn" data-toggle="tooltip" data-placement="top" title="<?php echo _('Rolle entfernen'); ?>"><span class="glyphicon glyphicon-minus"></span></button>
                </span>
              </div>
            </div>
          </div>
            <?php } ?>
          <?php } ?>
      <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
          <?php
          if (hasRights(array(0))) {
            $cancelUrl = getUrl($fileIdent);
          } else {
            $cancelUrl = getUrl('downloads');
          }
          ?>
          <a href="<?php echo $cancelUrl; ?>" class="btn btn-default"><?php echo _('Abbrechen'); ?></a>
          <button type="submit" class="btn btn-primary"><?php echo _('Speichern'); ?></button>
        </div>
      </div>
    </div>
</div>
</form>
</div>