<?php
global $pageTree, $fileIdent, $fileIdentPage;
$subPageRights = array();
$pages = $pageTree->get();
?>
<ul class="nav navbar-nav">
  <?php
  foreach ($pages as $int => $page) {
    if (getValue('url', $page) == 'dropdown') { // Draw dropdown link list 
      $elements = getValue('elements', $page);
      if (count($elements) > 0) {
        // check for rights in subpages
        foreach ($elements as $int1 => $subPage) {
          $rights = getValue('rights', $subPage);
          foreach ($rights as $r => $right) {
            if (!in_array($right, $subPageRights))
              $subPageRights[] = $right;
          }
        }
        $hasOneOrMoreRights = false;
        foreach ($subPageRights as $r => $right) {
          if (!$hasOneOrMoreRights) {
            if (hasRights(array($right))) {
              $hasOneOrMoreRights = true;
            }
          }
        }
        if ($hasOneOrMoreRights) {
          ?>
          <li class="dropdown <?php echo (recursive_array_search($fileIdentPage, $elements) !== false ? 'active' : ''); ?>">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo getValue('text', $page); ?> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <?php
              foreach ($elements as $int2 => $subpage) {
                if (hasRights(getValue('rights', $subpage))) {
                  ?>
                  <li><a href="<?php echo getValue('url', $subpage); ?>"><?php echo getValue('text', $subpage); ?></a></li>
            <?php }
          } ?>
            </ul>
          </li>
        <?php
        }
      }
    } else {
      if (hasRights(getValue('rights', $page))) {
        ?>
        <li class="<?php echo ($fileIdentPage == getValue('ident', $page) ? 'active' : ''); ?>"><a href="<?php echo getValue('url', $page); ?>"><?php echo getValue('text', $page); ?></a></li>
    <?php
    }
  }
}
?>
<?php if ($fileIdentPage != 'index' && hasRights($subPageRights)) { ?><li><a href="<?php echo getUrl($fileIdent . '?page=' . $fileIdentPage . '&action=add'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?php echo _('Hinzufügen'); ?>"><span class="glyphicon glyphicon-plus"></span></a></li><?php } ?>
</ul>