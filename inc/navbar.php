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
  <div class="siteheader__content">

    <a href="<?php echo $url_base; ?>">
      <!-- <img class="siteheader-logo" src="<?php echo $url_base; ?>/inc/images/logos/logo_header.svg" loading="lazy" /> -->
      <i class="i i-prodmaisheader siteheader__logo"></i>
    </a>

    <div class="sitemenu-container">
      <!-- <a class="u-skip" href="#skipmenu">Pular menu principal</a> -->
      <input class="sitemenu-btn-check" type="checkbox" id="checkbox_toggle" />
      <label class="sitemenu-btn-ico" for="checkbox_toggle" class="hamburger">&#9776;</label>
      <!-- <div class="u-skip" id="skipmenu"></div> -->

      <nav class="sitemenu" title="Menu do prodmais" aria-labelledby="Menu principal">

        <ul class="sitemenu-list">

          <li class="sitemenu-item" title="Home">
            <a class="sitemenu-link" href="<?php echo $url_base; ?>" title="Home">
              Home
              <i class="i i-home sitemenu-ico"></i>
            </a>
          </li>

          <li class=" sitemenu-item">
            <a class="sitemenu-link" href="result_autores.php" title="Pesquisadores">
              Pesquisadores
              <i class="i i-aboutme sitemenu-ico"></i>
            </a>
          </li>

          <li class=" sitemenu-item">
            <a class="sitemenu-link" href="categorias.php" title="Categorias">
              Categorias
              <i class="i i-category sitemenu-ico"></i>
            </a>
          </li>

          <!--
          <li class=" sitemenu-item">
            <a class="sitemenu-link" href="ppgs.php" title="Programas de pós graduação">
              PPGs
              <i class="i i-ppg-logo sitemenu-ico"></i>
            </a>
          </li>
          <li class=" sitemenu-item">
            <a class="sitemenu-link" href="projetos.php" title="Projetos de pesquisa">
              Projetos de Pesquisa
              <i class="i i-project sitemenu-ico"></i>
            </a>
          </li>
        -->

          <?php if($mostrar_link_dashboard): ?>
          <li class="sitemenu-item">
            <a class="sitemenu-link" href="predash.php" title="Dashboard">
              Dashboard
              <i class="i i-dashboard sitemenu-ico"></i>
            </a>
          </li>
          <?php endif; ?>

          <!-- <li class="sitemenu-item">
          <a class="sitemenu-link" href="manual/" title="Manual">
            Manual
            <i class="i i-manual sitemenu-ico"></i>
          </a>
        </li> -->

          <li class="sitemenu-item">
            <a class="sitemenu-link" href="sobre.php" title="Sobre o Prodmais">
              Sobre
              <i class="i i-about sitemenu-ico"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>