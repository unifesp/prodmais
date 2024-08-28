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
$result_post['query']["sort"]["DADOS-DO-PROJETO.@attributes.ANO-INICIO.keyword"]["order"] = "desc";
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

    <title><?php echo $branch; ?> - Projetos de pesquisa</title>


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

            <details id="filterlist" class="c-filterlist" onload="resizeMenu" open="">
                <summary class="c-filterlist__header">
                    <h3 class="c-filterlist__title">Refinar resultados</h3>
                </summary>

                <div class="c-filterlist__content" id="app">

                    <?php
                    $facets = new Facets();
                    $facets->query = $result_post['query'];
                    if (!isset($_POST["search"])) {
                        $_POST["search"] = "";
                    }
                    echo ($facets->facet(1, "NOME-INSTITUICAO", 100, "Instituição", null, "_term", $_POST, "projetos.php", $index_projetos));
                    echo ($facets->facet(2, "DADOS-DO-PROJETO.@attributes.SITUACAO", 100, "Situação", null, "_term", $_POST, "projetos.php", $index_projetos));
                    ?>
                </div>
            </details>
        </nav>

        <main class="p-result-main">

            <div class="p-result-search-ctn">

                <form class="u-100" action="projetos.php" method="POST" accept-charset="utf-8"
                    enctype="multipart/form-data" id="search">

                    <div class="c-searcher">
                        <input class="" type="text" name="search"
                            placeholder="Pesquise por palavras chave ou termos em projetos"
                            aria-label="Pesquise por palavras chave ou termos em projetos"
                            aria-describedby="button-addon2" />
                    </div>
                </form>

            </div>



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

        <script>
        new Vue({
            el: '#app',
            data: {
                isVisible1: false,
                isVisible2: false,
                isVisible3: false,
                isVisible4: false,
                isVisible5: false,
                isVisible6: false,
                isVisible7: false,
                isVisible8: false,
                isVisible8: false,
                isVisible10: false,
                isVisible11: false,
                isVisible12: false,
                isVisible13: false,
                isVisible14: false,
                isVisible15: false,
                isVisible16: false,
                isVisible17: false,
                isVisible18: false,
                isVisible19: false,
                isVisible20: false,
                isVisible21: false,
                isVisible22: false,
                isVisible23: false,
                isVisible24: false,
                isVisible25: false,
                isVisible26: false,
                isVisible27: false,
                isVisible28: false,
                isVisible29: false,
                isVisible30: false,
                isVisible31: false,
                isVisible32: false,
                isVisible33: false,
                isVisible34: false,
                isVisible35: false,
                isVisible36: false,
                isVisible37: false,
                isVisible38: false,
                isVisible39: false,
                isVisible40: false,
                isVisible41: false,
                isVisible42: false,
                isVisible43: false,
                isVisible44: false,
                isVisible45: false,
                isVisible46: false,
                isVisible47: false,
                isVisible48: false,
            },
            methods: {
                toggleDiv(id) {
                    id.toString();
                    var str = 'isVisible' + id;
                    this[str] = !this[str];
                    console.log(this.str);
                },
            },

        });
        </script>

    </div>

    <?php include('inc/footer.php'); ?>

</body>

</html>