<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php
    require 'inc/config.php';
    require 'inc/functions.php';
    require 'inc/meta-header.php';

    // Busca por produção científica

    $result_post = Requests::postParser($_POST);

    $limit_records = $result_post['limit'];
    $page = $result_post['page'];
    $params = [];
    $params["index"] = $index;
    $params["body"] = $result_post['query'];
    $cursorTotal = $client->count($params);
    $total_records = $cursorTotal["count"];

    // Busca por autores

    $result_post_authors = Requests::postParser($_POST);
    $limit_records = 50;
    $page = $result_post['page'];
    $paramsAuthors = [];
    $result_post['query']['query']["query_string"]["fields"] = ["nome_completo", "resumo_cv.texto_resumo_cv_rh"];
    $paramsAuthors["index"] = $index_cv;
    $paramsAuthors["body"] = $result_post['query'];
    $cursorTotalAuthors = $client->count($paramsAuthors);
    $total_records_authors = $cursorTotalAuthors["count"];

    // Busca por projetos


    $result_post = Requests::postParser($_POST);
    $limit_records = 50;
    $page = $result_post['page'];
    $paramsProjetos = [];
    $paramsProjetos["index"] = $index_projetos;
    $paramsProjetos["body"] = $result_post['query'];
    $cursorTotalProjetos = $client->count($paramsProjetos);
    $totalRecordsProjetos = $cursorTotalProjetos["count"];

    ?>
    <title>Pré Busca</title>
    <meta name="generator" content="Jekyll v4.2.1" />
    <meta property="og:title" content="Pré Busca" />
    <meta property="og:locale" content="en_US" />
    <meta name="description" content="Pré Busca" />
    <meta property="og:description" content="Pré Busca" />
    <link rel="canonical" href="https://unifesp.br/prodmais/presearch.php" />
    <meta property="og:url" content="https://unifesp.br/prodmais/presearch.php" />
    <meta property="og:site_name" content="Pré Busca" />
    <meta name="twitter:card" content="summary" />
    <meta property="twitter:title" content="Pré Busca" />
    <script type="application/ld+json">
        {
            "@type": "WebPage",
            "description": "Pré Busca",
            "url": "https://unifesp.br/prodmais/presearch.php",
            "headline": "Pré Busca",
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

        <?php var_dump($_REQUEST); ?>

        <h2 class="t t-center">Resultados obtidos:</h2><br>

        <!-- <p class="t t-gray t-center">Username: <i>dashboard</i></p><br>

        <p class="t t-gray t-center">Password: <i>dashboard</i></p><br>

        <img class="p-predash-img" src="inc/images/login.jpg" /> -->

        <p class="t t-center">Estes são os resultados obitidos nas seguintes categorias:</p>

        <div class="dh d-hc u-my-20">
            <p>
            <form class="p-home-form" class="" action="result.php" title="Pesquisa simples" method="post">
                <input type="hidden" name="search" value="<?php echo $_REQUEST['search'] ?>">
                <button class="c-btn" type="submit">
                    Produção Científica - <?php echo $total_records; ?>
                </button>
            </form>
            </p>
            <p>
            <form class="p-home-form" class="" action="result_autores.php" title="Pesquisa simples" method="post">
                <input type="hidden" name="search" value="<?php echo $_REQUEST['search'] ?>">
                <input type="hidden" name="resumocv" value="<?php echo $_REQUEST['search'] ?>">
                <button class="c-btn" type="submit">
                    Perfil dos Pesquisadores - <?php echo $total_records_authors; ?>
                </button>
            </form>
            </p>
            <p>
            <form class="p-home-form" class="" action="projetos.php" title="Pesquisa simples" method="post">
                <input type="hidden" name="search" value="<?php echo $_REQUEST['search'] ?>">
                <button class="c-btn" type="submit">
                    Projetos de pesquisa - <?php echo $totalRecordsProjetos; ?>
                </button>
            </form>
            </p>
        </div>

    </main>

    <?php include('inc/footer.php'); ?>
</body>

</html>