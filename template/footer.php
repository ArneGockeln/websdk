<?php global $fileIdent; if($fileIdent != 'index') { ?></div><?php } ?>
  <!-- Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="deleteModalLabel"><?php echo _('Frage'); ?></h4>
        </div>
        <div class="modal-body">
          <?php echo _('Soll der Eintrag wirklich gelöscht werden?'); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Nein'); ?></button>
          <button type="button" id="deleteModalConfirmBtn" class="btn btn-danger"><?php echo _('Ja'); ?></button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
</body>
</html>
