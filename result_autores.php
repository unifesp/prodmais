<!DOCTYPE html>
<?php

require 'inc/config.php';
require 'inc/functions.php';

if (isset($_POST["search"]) & !empty($_POST["search"])) {
    if (!str_contains($_POST['search'], 'nome_completo')) {
        $_POST["search"] = 'nome_completo:(' . $_POST['search'] . ')';
    }
} else {
    $_POST["search"] = '';
}

if (isset($_POST["resumocv"])) {
    $_POST["search"] = 'resumo_cv.texto_resumo_cv_rh:' . $_POST["resumocv"] . '';
}

if (isset($fields)) {
    $_POST["fields"] = $fields;
}

$result_post = Requests::postParser($_POST);
$limit_records = 50;
$page = $result_post['page'];
$params = [];
$params["index"] = $index_cv;
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

//echo "<br/><br/><br/><br/><br/><br/><br/><pre>" . print_r($cursor["hits"], true) . "</pre>";

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

            <details id="filterlist" class="c-filterlist" onload="resizeMenu" open="">

                <?php
                if (isset($_REQUEST['filter'])) {
                    $filter_aplicado_array =  $_REQUEST['filter'];
                    //var_dump($filter_aplicado_array);
                    echo '<div class="c-term">';
                    echo '<p>Filtros aplicados:</p>';
                    foreach ($_REQUEST['filter'] as $filter) {
                        echo '<div class="c-term">';
                        echo '<form action="result_autores.php" method="post">';
                        echo '<input type="hidden" name="search" value="' . $_REQUEST["search"] . '">';
                        $array_sem_filtro = array_diff($filter_aplicado_array, [$filter]);
                        foreach ($array_sem_filtro as $filtro_aplicado) {
                            echo '<input type="hidden" name="filter[]" value="' . $filtro_aplicado . '">';
                        }
                        $filter_name = str_replace('formacao_maxima:', 'Maior formação: ', $filter);
                        $filter_name = str_replace('vinculo.ppg_nome:', 'Programa de Pós-Graduação: ', $filter_name);
                        $filter_name = str_replace('formacao_academica_titulacao_mestrado.nomeInstituicao:', 'Instituição: ', $filter_name);
                        $filter_name = str_replace('formacao_academica_titulacao_doutorado.nomeInstituicao:', 'Instituição: ', $filter_name);
                        $filter_name = str_replace('pais_de_nascimento:', 'País: ', $filter_name);
                        $filter_name = str_replace('ppg_nome:', 'Programa de Pós Graduação: ', $filter_name);
                        $filter_name = str_replace('departamento:', 'Departamento: ', $filter_name);
                        $filter_name = str_replace('tipvin:', 'Típo de vínculo: ', $filter_name);
                        $filter_name = str_replace('desc_curso:', 'Curso: ', $filter_name);

                        echo '<input class="c-filterdrop__item-name" style="text-decoration: none; color: initial; white-space: normal;" type="submit" value="' . $filter_name . ' (Remover)" />';
                        echo '</form>';
                        echo '</div>';
                    }
                    echo '</div>';
                    //echo '<div class="c-term">Filtro aplicado: ' . implode('', $filter_array) . '</div>';
                }
                ?>
                <summary class="c-filterlist__header">
                    <h3 class="c-filterlist__title">Refinar resultados</h3>
                </summary>

                <div class="c-filterlist__content" id="app">

                    <?php
                    $facets = new Facets();
                    $facets->query = $result_post['query'];

                    if (!isset($_POST)) {
                        $_POST = null;
                    }
                    if ($mostrar_instituicao) {
                        echo ($facets->facet(1, "instituicao", 100, "Instituição", null, "_term", $_POST, "result_autores.php", $index_cv));
                    }
                    echo ($facets->facet(4, "unidade", 100, "Unidade", null, "_term", $_POST, "result_autores.php", $index_cv));
                    echo ($facets->facet(5, "departamento", 100, "Departamento", null, "_term", $_POST, "result_autores.php", $index_cv));
                    echo ($facets->facet(8, "ppg_nome", 100, "Nome do PPG", "asc", "_key", $_POST, "result_autores.php", $index_cv));
                    echo ($facets->facet(10, "tipvin", 100, "Tipo de vínculo", null, "_term", $_POST, "result_autores.php", $index_cv));
                    echo ($facets->facet(12, "desc_curso", 100, "Curso", null, "_term", $_POST, "result_autores.php", $index_cv));

                    //echo ($facets->facet(2, "campus", 100, "Campus", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(3, "desc_gestora", 100, "Gestora", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(6, "divisao", 100, "Divisão", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(7, "secao", 100, "Seção", null, "_term", $_POST, "result_autores.php", $index_cv));

                    if ($mostrar_area_concentracao) {
                        //echo ($facets->facet(9, "area_concentracao", 100, "Área de concentração", null, "_term", $_POST, "result_autores.php", $index_cv));
                    }

                    //echo ($facets->facet(11, "desc_nivel", 100, "Nível", null, "_term", $_POST, "result_autores.php", $index_cv));


                    //echo($facets->facet(13, "tag", 100, "Tag", null, "_term", $_POST, "result_autores.php", $index_cv));
                    // echo($facets->facet(14, "nacionalidade", 100, "Nacionalidade", null, "_term", $_POST, "result_autores.php", $index_cv));

                    //echo($facets->facet(15, "endereco.endereco_profissional.nomeInstituicaoEmpresa", 100, "Nome da Instituição ou Empresa", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo($facets->facet(16, "endereco.endereco_profissional.nomeOrgao", 100, "Nome do orgão", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo($facets->facet(17, "endereco.endereco_profissional.nomeUnidade", 100, "Nome da unidade", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo($facets->facet(18, "endereco.endereco_profissional.pais", 100, "País do endereço profissional", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo($facets->facet(19, "endereco.endereco_profissional.cidade", 100, "Cidade do endereço profissional", null, "_term", $_POST, "result_autores.php", $index_cv));

                    //echo ($facets->facet(20, "formacao_academica_titulacao_graduacao.nomeInstituicao", 100, "Instituição em que cursou graduação", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(21, "formacao_academica_titulacao_graduacao.nomeCurso", 100, "Nome do curso na graduação", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(22, "formacao_academica_titulacao_mestrado.nomeInstituicao", 100, "Instituição em que cursou mestrado", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(23, "formacao_academica_titulacao_mestrado.nomeCurso", 100, "Nome do curso no mestrado", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(24, "formacao_academica_titulacao_mestradoProfissionalizante.nomeInstituicao", 100, "Instituição em que cursou mestrado profissional", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(25, "formacao_academica_titulacao_mestradoProfissionalizante.nomeCurso", 100, "Nome do curso no mestrado profissional", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(26, "formacao_academica_titulacao_doutorado.nomeInstituicao", 100, "Instituição em que cursou doutorado", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(27, "formacao_academica_titulacao_doutorado.nomeCurso", 100, "Nome do curso no doutorado", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(28, "formacao_academica_titulacao_livreDocencia.nomeInstituicao", 100, "Instituição em que cursou livre docência", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(29, "formacao_maxima", 10, "Maior formação que iniciou", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(30, "data_atualizacao", 100, "Data de atualização do currículo", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(31, "genero", 100, "Genero", null, "_term", $_POST, "result_autores.php", $index_cv));
                    //echo ($facets->facet(32, "pais_de_nascimento", 100, "País de nascimento", null, "_term", $_POST, "result_autores.php", $index_cv));

                    ?>
                </div>
            </details>
        </nav>

        <main class="p-result-main">

            <div class="p-result-search-ctn">

                <form class="u-100" action="result_autores.php" method="POST" accept-charset="utf-8"
                    enctype="multipart/form-data" id="searchresearchers">

                    <div class="c-searcher">
                        <input class="" type="text" name="search" placeholder="Digite parte do nome do pesquisador"
                            aria-label="Digite parte do nome do pesquisador" aria-describedby="button-addon2" />
                        <button class="c-searcher__btn" type="submit" form="searchresearchers" value="Submit">
                            <i class="i i-lupa c-searcher__btn-ico"></i>
                        </button>
                    </div>
                </form>

                <form class="u-100" action="result_autores.php" method="POST" accept-charset="utf-8"
                    enctype="multipart/form-data" id="resumocv">

                    <div class="c-searcher">
                        <input class="" type="text" name="resumocv"
                            placeholder="Digite um termo para pesquisar no resumo"
                            aria-label="Digite um termo para pesquisar no resumo" aria-describedby="button-addon2" />
                        <button class="c-searcher__btn" type="submit" form="resumocv" value="Submit">
                            <i class="i i-lupa c-searcher__btn-ico"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Navegador de resultados - Início -->
            <?php ui::newpagination($page, $total_records, $limit_records, $_POST, 'result_autores'); ?>
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
                            <a href="profile.php?lattesID=<?php echo $r['_source']['lattesID']; ?>">
                                <?php echo $r["_source"]['nome_completo']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Navegador de resultados - Início -->
            <?php ui::newpagination($page, $total_records, $limit_records, $_POST, 'result_autores'); ?>
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