<!DOCTYPE html>
<?php

require 'inc/config.php';
require 'inc/functions.php';
require 'inc/components/SList.php';



$result_post = Requests::postParser($_POST);
$limit_records = 50;
$page = $result_post['page'];
$params = [];
$params["index"] = $index_projetos;
$params["body"] = $result_post['query'];
$cursorTotal = $client->count($params);
$total_records = $cursorTotal["count"];
$result_post['query']["sort"]["nome_completo.keyword"]["unmapped_type"] = "long";
$result_post['query']["sort"]["nome_completo.keyword"]["missing"] = "_last";
$result_post['query']["sort"]["nome_completo.keyword"]["order"] = "asc";
$params["body"] = $result_post['query'];
$params["size"] = $limit_records;
$params["from"] = $result_post['skip'];
$cursor = $client->search($params);

/*pagination - start*/
$get_data = $_GET;
/*pagination - end*/

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include('inc/meta-header.php');
    ?>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous">
  </script> -->

    <title><?php echo $branch; ?> - Resultado da busca por perfil profissional</title>


    <link rel="stylesheet" href="inc/css/style.css" />

</head>

<body data-theme="<?php echo $theme; ?>">

    <?php
    if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    }
    ?>

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->

    <div id="app-result" class="p-result-container">

        <nav class="p-result-nav">

            <details id="filterlist" class="c-filterlist" onload="resizeMenu">
                <summary class="c-filterlist__header">
                    <h3 class="c-filterlist__title">Refinar resultados</h3>
                </summary>

                <div class="c-filterlist__content">

                    <?php
                    $facets = new Facets();
                    $facets->query = $result_post['query'];
                    if (!isset($_POST["search"])) {
                        $_POST["search"] = "";
                    }
                    echo ($facets->facet(basename(__FILE__), "NOME-INSTITUICAO", 100, "Instituição", null, "_term", $_POST, "projetos.php", $index_projetos));
                    echo ($facets->facet(basename(__FILE__), "DADOS-DO-PROJETO.@attributes.SITUACAO", 100, "Situação", null, "_term", $_POST, "projetos.php", $index_projetos));
                    ?>
                </div>
            </details>
        </nav>

        <main class="p-result-main">

            <transition name="homeeffect">
                <div class="c-tips" v-if="showTips">

                    <h4>Refinar resultados</h4>
                    <p>Use os filtros à esquerda para refinar os resultados da sua busca. São diversas opções, como
                        Campus, Unidade, Departamento, Nome do PPG, e etc.
                    </p>

                    <p>Basta clicar sobre cada uma das opções e um menu de novas opções se abrirá. Ao lado direito de
                        cada item listado é exibida a quantidade de resultados disponíceis.
                    </p>
                    <h4></h4>

                    <button class="c-btn u-center" v-on:click="showTips = !showTips" title="Fechar dicas de pesquisa">
                        Fechar
                    </button>
                </div>
            </transition>

            <!-- Navegador de resultados - Início -->
            <?php ui::newpagination($page, $total_records, $limit_records, $_POST, 'projetos'); ?>
            <!-- Navegador de resultados - Fim -->

            <div class="p-result-authors">
                <ul class="c-authors-list">
                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>
                    <?php
                        if (empty($r["_source"]['datePublished'])) {
                            $r["_source"]['datePublished'] = "";
                        }
                        ?>

                    <li class="c-card-author t t-b t-md">
                        <?php
                            if (isset($r["_source"]['DADOS-DO-PROJETO']['EQUIPE-DO-PROJETO']['INTEGRANTES-DO-PROJETO'])) {
                                foreach ($r["_source"]['DADOS-DO-PROJETO']['EQUIPE-DO-PROJETO']['INTEGRANTES-DO-PROJETO'] as $integrantes) {
                                    if (isset($integrantes['@attributes'])) {
                                        $integrantes_do_projeto_array[] = $integrantes['@attributes']['NOME-COMPLETO'];
                                    } else {
                                        $integrantes_do_projeto_array = [];
                                    }
                                }
                                $integrantes_do_projeto = implode(", ", $integrantes_do_projeto_array);
                            } else {
                                $integrantes_do_projeto = "";
                            }

                            ?>

                    <li class='s-nobullet'>
                        <div class='s-list'>
                            <div class='s-list-bullet'>
                                <i class='i i-ppg-logo s-list-ico'></i>
                            </div>

                            <div class='s-list-content'>


                                <?php if (isset($r["_source"]['DADOS-DO-PROJETO'][0])) : ?>
                                <?php //print_r($r["_source"]['DADOS-DO-PROJETO']);
                                        ?>
                                <p class='t t-b'>
                                    <a href="projeto.php?ID=<?php echo $r['_id']; ?>"><?php echo $r["_source"]['DADOS-DO-PROJETO'][0]['@attributes']['NOME-DO-PROJETO'] ?>
                                </p>
                                <p class='t t-gray'>
                                    Descrição do projeto:
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO'][0]['@attributes']['DESCRICAO-DO-PROJETO']; ?>
                                </p>
                                <p class='t t-gray'><i>Integrantes: <?php echo $integrantes_do_projeto ?></i></p>
                                <p class='t t-gray'>Situação:
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO'][0]['@attributes']['SITUACAO']; ?></p>
                                <p class='t t-gray'>
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO'][0]['@attributes']['ANO-INICIO']; ?> -
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO'][0]['@attributes']['ANO-FIM']; ?></p>
                                <?php else : ?>
                                <p class='t t-b'>
                                    <a href="projeto.php?ID=<?php echo $r['_id']; ?>"><?php echo $r["_source"]['DADOS-DO-PROJETO']['@attributes']['NOME-DO-PROJETO'] ?>
                                    </a>
                                </p>
                                <p class='t t-gray'>
                                    Descrição do projeto:
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO']['@attributes']['DESCRICAO-DO-PROJETO']; ?>
                                </p>
                                <p class='t t-gray'><i>Integrantes: <?php echo $integrantes_do_projeto ?></i></p>
                                <p class='t t-gray'>Situação:
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO']['@attributes']['SITUACAO']; ?></p>
                                <p class='t t-gray'>
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO']['@attributes']['ANO-INICIO']; ?> -
                                    <?php echo $r["_source"]['DADOS-DO-PROJETO']['@attributes']['ANO-FIM']; ?></p>
                                <?php endif ?>
                            </div>
                        </div>
                    </li>
                    <?php unset($integrantes_do_projeto_array); ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Navegador de resultados - Início -->
            <?php ui::newpagination($page, $total_records, $limit_records, $_POST, 'projetos'); ?>
            <!-- Navegador de resultados - Fim -->

        </main>

    </div>

    <?php include('inc/footer.php'); ?>
    <script src="inc/js/pages/result.js"></script>
</body>

</html>