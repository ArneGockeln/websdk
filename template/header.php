<?php global $fileIdent, $fileIdentPage; ?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo _('WebArbyte'); ?></title>
    <meta charset="utf-8">
    <meta name="author" content="www.Webchef.de, Arne Gockeln"/>
    <!--meta name="viewport" content="width=device-width, initial-scale=1.0"//-->
    <!-- Bootstrap core CSS -->
    <link href="<?php echo getUrl('template/css/bootstrap.css'); ?>" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo getUrl('template/css/starter-template.css'); ?>" rel="stylesheet">
    
    <!-- WebArbyte styles //-->
    <link href="<?php echo getUrl('template/css/webarbyte.css'); ?>" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <script src="<?php echo getUrl('template/js/jquery.js'); ?>"></script>
    <script src="<?php echo getUrl('template/js/bootstrap.js'); ?>"></script>
    <script src="<?php echo getUrl('template/js/webarbyte.js'); ?>"></script>
  </head>
  <body>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo _('WebArbyte'); ?></a>
        </div>
        <div class="navbar-collapse collapse navbar-right">
          <?php if (!is_online()) { ?>
          <form class="navbar-form navbar-right" method="post" action="<?php echo getUrl('login.php'); ?>" role="form">
            <div class="form-group">
              <input type="text" name="post_email" placeholder="<?php echo _('E-Mail'); ?>" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" name="post_pwd" placeholder="<?php echo _('Passwort'); ?>" class="form-control">
            </div>
            <button type="submit" class="btn btn-success"><?php echo _('Anmelden'); ?></button>
          </form>
          <?php } 
           includeTemplate('template.nav.php'); 
           ?>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>
    
    <?php if($fileIdent != 'index') { ?><div class="container"><?php } ?>
      <?php echo getMessage(); ?>