<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php
    require 'inc/config.php';
    require 'inc/functions.php';
    require 'inc/meta-header.php';

    ?>
    <title>Manual do Prodmais | Manual do Dashboard</title>
    <meta name="generator" content="Jekyll v4.2.1" />
    <meta property="og:title" content="Manual do Dashboard do Prodmais" />
    <meta property="og:locale" content="en_US" />
    <meta name="description" content="Manual do Dashboard do Prodmais" />
    <meta property="og:description" content="Manual do Dashboard do Prodmais" />
    <link rel="canonical" href="https://unifesp.br/prodmais/predash.php" />
    <meta property="og:url" content="https://unifesp.br/prodmais/predash.php" />
    <meta property="og:site_name" content="Manual do Dashboard do Prodmais" />
    <meta name="twitter:card" content="summary" />
    <meta property="twitter:title" content="Manual do Dashboard do Prodmais" />
    <script type="application/ld+json">
        {
            "@type": "WebPage",
            "description": "Manual do Dashboard do Prodmais",
            "url": "https://unifesp.br/prodmais/predash.php",
            "headline": "Manual do Dashboard do Prodmais",
            "@context": "https://schema.org"
        }
    </script>
    <!-- End Jekyll SEO tag -->


</head>

<body data-theme="<?php echo $theme; ?>" class="">
    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->

    <main class="p-predash-main">

        <h1 class="t t-alert"> O Dashboard está em manutenção! </h1>

        <h2 class="t t-center">Para entrar no dashboard:</h2><br>

        <p class="t t-gray t-center">Username: <i>dashboard</i></p><br>

        <p class="t t-gray t-center">Password: <i>dashboard</i></p><br>

        <img class="p-predash-img" src="inc/images/login.jpg" />

        <p class="t t-center">O Prodmais utiliza a ferramenta Elastic Search para oferecer um poderoso dashboard com
            dados
            detalhados sobre a base de produção acadêmica. Você pode consultar duas categorias de dashboard:
            produção acadêmica e perfil dos pesquisadores.</p>

        <div class="dh d-hc u-my-20">
            <p>
                <a class="c-btn" href="https://unifesp.br/kibana/app/kibana#/dashboard/26076fa0-618c-11ec-9863-4f7d48084ff8?embed=true&_g=(refreshInterval%3A(pause%3A!t%2Cvalue%3A0)%2Ctime%3A(from%3Anow-15m%2Cto%3Anow))">
                    Produção Acadêmica
                </a>
            </p>
            <p>
                <a class="c-btn" href="https://unifesp.br/kibana/app/kibana#/dashboard/568e12f0-618c-11ec-9863-4f7d48084ff8?embed=true&_g=(filters%3A!()%2CrefreshInterval%3A(pause%3A!t%2Cvalue%3A0)%2Ctime%3A(from%3Anow-15m%2Cto%3Anow))">
                    Perfil dos Pesquisadores
                </a>
            </p>
        </div>

    </main>

    <?php include('inc/footer.php'); ?>
</body>

</html>