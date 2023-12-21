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

    <br /><br /><br />

    <main class="p-predash-main">

        <h2 class="t t-center">Para entrar no dashboard:</h2><br>

        <!-- <p class="t t-gray t-center">Username: <i>dashboard</i></p><br>

        <p class="t t-gray t-center">Password: <i>dashboard</i></p><br>

        <img class="p-predash-img" src="inc/images/login.jpg" /> -->

        <p class="t t-center">O Prodmais utiliza a ferramenta Elastic Search para oferecer um poderoso dashboard com
            dados
            detalhados sobre a base de produção acadêmica. Você pode consultar duas categorias de dashboard:
            produção acadêmica e perfil dos pesquisadores.</p>

        <div class="dh d-hc u-my-20">
            <p>
                <a class="c-btn"
                    href="https://unifesp.br/prodmais/kibana/app/dashboards?auth_provider_hint=anonymous1#/view/7cc146e0-9f9c-11ee-b989-9f6cdcc63b63?_g=(refreshInterval:(pause:!t,value:60000),time:(from:now-15m,to:now))&_a=()">
                    Produção Acadêmica
                </a>
            </p>
            <p>
                <a class="c-btn"
                    href="https://unifesp.br/prodmais/kibana/app/dashboards?auth_provider_hint=anonymous1#/view/1a485b50-9fe9-11ee-a0e5-9b601263f818?_g=(refreshInterval:(pause:!t,value:60000),time:(from:now-15m,to:now))&_a=()">
                    Perfil dos Pesquisadores
                </a>
            </p>
        </div>

    </main>

    <?php include('inc/footer.php'); ?>
</body>

</html>