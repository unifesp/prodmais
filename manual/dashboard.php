<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1"><!-- Begin Jekyll SEO tag v2.7.1 -->
  <title>Manual do Prodmais | Manual do Prodmais</title>
  <meta name="generator" content="Jekyll v4.2.1" />
  <meta property="og:title" content="Manual do Prodmais" />
  <meta property="og:locale" content="en_US" />
  <meta name="description" content="Manual do Prodmais" />
  <meta property="og:description" content="Manual do Prodmais" />
  <link rel="canonical" href="https://unifesp.br/prodmais/manual/dashboard.php" />
  <meta property="og:url" content="https://unifesp.br/prodmais/manual/dashboard.php" />
  <meta property="og:site_name" content="Manual do Prodmais" />
  <meta name="twitter:card" content="summary" />
  <meta property="twitter:title" content="Manual do Prodmais" />
  <script type="application/ld+json">
    {
      "@type": "WebPage",
      "description": "Manual do Prodmais",
      "url": "https://unifesp.br/prodmais/manual/dashboard.php",
      "headline": "Manual do Prodmais",
      "@context": "https://schema.org"
    }
  </script>
  <!-- End Jekyll SEO tag -->
  <link rel="stylesheet" href="../sass/main.css" />
  <link type="application/atom+xml" rel="alternate" href="https://unifesp.br/prodmais/manual/feed.xml" title="Manual do Prodmais" />
  <link rel="shortcut icon" href="../inc/images/favicon-64x.png" type="image/x-icon">
</head>

<body class="manual-body">

  <!-- NAV -->
  <?php require 'navbar_manual.php'; ?>
  <!-- /NAV -->


  <main class="manual-container" aria-label="Content">
      <div class="manual-wrapper">

      <h1 id="dashboards">Manual — Dashboards</h1>

      <p>O Prodmais utiliza a ferramenta <em>Elastic Search</em> para oferecer um poderoso <em>dashboard</em> com dados detalhados sobre a base de produção acadêmica. O Prodmais oferece dois <em>dashboards</em>: O <em>dashboard</em> de Produção Acadêmica, e o <em>dashboard</em> de Perfil dos Pesquisadores.</p>

      <blockquote>
        <p>Para acessar, preencha ambos os campos <em>Username</em> e <em>Password</em> com a palavra <strong><em>dashboard</em></strong></p>
      </blockquote>

      <center>
        <img class="manual-img" src="assets/img/manual/dash_login.png" />
      </center>

      <p>O conteúdo da tela se carregará com gráficos como este:</p>

      <center>
        <img class="manual-img" src="assets/img/manual/dash_graficos.png" />
      </center>

      <p>Os gráficos são interativos e se modificam filtrando a informação quando você clica em
        cada categoria de informação exibida. Então, a cada ação de filtragem, o gráfico exibe informações cada vez mais afuniladas sobre determinada informação. Para voltar ao estado anterior do gráfico utilize a seta “voltar” do seu navegador.</p>

      <center>
        <img class="manual-img" src="assets/img/manual/dash-focus.jpg" />
      </center>
      <p><em>Clicando nas partes do gráfico, novas informações são carregadas a partir de suas subcategorias.</em></p>

      <h3 id="nuvem-de-tags">Nuvem de tags</h3>

      <p>A núvem de tags facilita a pesquisa por tópicos com maior volume de produção:</p>

      <center>
        <img class="manual-img" src="assets/img/manual/dash_cloudtag.png" />
      </center>

      <p>Clicando em uma tag, o <em>dashboard</em> se atualiza com as novas informações.</p>


      <div class="u-mb-2"></div>
      <?php require 'manual-menu.php'; ?>
    </div> <!-- manual wrapper -->
  </main>




  <?php require 'footer.php'; ?>

</body>

</html>