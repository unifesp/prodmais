<?php 
  if (file_exists('../inc/config.php')) {
      require '../inc/config.php';
  } elseif (file_exists('../../inc/config.php')) {
      require '../../inc/config.php';
  } elseif (file_exists('inc/config.php')) {
      require 'inc/config.php';
  } else {
      die('No config file found');
  }
?>

<header class="siteheader">

  <a href="<?php echo $url_base; ?>/index.php">
    <i class="i i-logo-header siteheader-logo"></i>
  </a>

  <a class="u-skip" href="#skipmenu">Pular menu principal</a>

  <nav class="" title="Menu do prodmais" aria-labelledby="Menu principal">
    <ul class="sitemenu">

      <li class="sitemenu-item" title="Home">
        <a class="sitemenu-link" href="<?php echo $url_base; ?>/index.php" title="Home">
          <i class="i i-home sitemenu-icons"></i>
        </a>

      </li>

      <li class="sitemenu-item">
        <a class="sitemenu-link" href="<?php echo $url_base; ?>/result_autores.php" title="Pesquisadores">
          <i class="i i-aboutme sitemenu-icons"></i>
        </a>

      </li>

      <li class="sitemenu-item">
        <a class="sitemenu-link" href="<?php echo $url_base; ?>/manual/" title="Manual">
          <i class="i i-manual sitemenu-icons"></i>
        </a>

      </li>

      <li class="sitemenu-item">
        <a class="sitemenu-link" href="<?php echo $url_base; ?>/predash.php" title="Dashboard">
          <i class="i i-dashboard sitemenu-icons"></i>
        </a>
      </li>

      <li class="sitemenu-item">
        <a class="sitemenu-link" href="<?php echo $url_base; ?>/sobre.php" title="Sobre o Prodmais">
          <i class="i i-about sitemenu-icons"></i>
        </a>
      </li>
    </ul>
  </nav>
  <div class="u-skip" id="skipmenu"></div>
</header>