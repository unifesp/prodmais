<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
  <?php 
      include('inc/config.php');             
      include('inc/meta-header.php');
      include('inc/functions.php');
      
      /* Define variables */
      define('authorUSP','authorUSP');
        ?>
  <title><?php echo $branch ?></title>
  <!-- Facebook Tags - START -->
  <meta property="og:locale" content="pt_BR">
  <meta property="og:url" content="<?php echo $url_base ?>">
  <meta property="og:title" content="<?php echo $branch ?> - Página Principal">
  <meta property="og:site_name" content="<?php echo $branch ?>">
  <meta property="og:description" content="<?php echo $branch_description ?>">
  <meta property="og:image" content="<?php echo $facebook_image ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="800">
  <meta property="og:image:height" content="600">
  <meta property="og:type" content="website">
  <!-- Facebook Tags - END -->

  <style>
  .bd-placeholder-img {
    font-size: 1.125rem;
    text-anchor: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }

  @media (min-width: 768px) {
    .bd-placeholder-img-lg {
      font-size: 3.5rem;
    }
  }

  .jumbotron {
    background-image: url("<?php echo $background_1 ?>");
    background-size: 100%;
    background-repeat: no-repeat;
  }
  </style>

</head>

<body data-theme="<?php echo $theme; ?>">



  <!-- NAV -->
  <?php require 'inc/navbar.php'; ?>
  <!-- /NAV -->
  </br></br></br>
  <div class="container">

    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <strong>Para login</strong> use o <strong>usuário: dashboard</strong> e <strong>senha: dashboard</strong>.
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <?php if (isset($_REQUEST["dashboard"])) : ?>

    <?php if ($_REQUEST["dashboard"] == "lattes_producoes") : ?>
    <iframe src="<?php echo $dashboard_lattes_producoes ?>" height="10000" width="100%" frameBorder="0"
      scrolling="no"></iframe>
    <?php elseif ($_REQUEST["dashboard"] == "lattes_cv") : ?>
    <iframe src="<?php echo $dashboard_lattes_cv ?>" height="10000" width="100%" frameBorder="0"
      scrolling="no"></iframe>
    <?php else: ?>
    <iframe src="<?php echo $dashboard_source ?>" height="10000" width="100%" frameBorder="0" scrolling="no"></iframe>
    <?php endif ?>

    <?php else: ?>
    <div>Dashboard não encontrado</div>
    <?php endif ?>


  </div>


  <?php include('inc/footer.php'); ?>


</body>

</html>