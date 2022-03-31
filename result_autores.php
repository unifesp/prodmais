<!DOCTYPE html>
<?php

require 'inc/config.php';
require 'inc/functions.php';

if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        $var_concluido["doc"]["concluido"] = $value;
        $var_concluido["doc"]["doc_as_upsert"] = true;
        Elasticsearch::update($key, $var_concluido);
    }
    sleep(6);
    header("Refresh:0");
}

if (isset($_GET["filter"])) {
    if (!in_array("type:\"Curriculum\"", $_GET["filter"])) {
        $_GET["filter"][] = "type:\"Curriculum\"";
    }
} else {
    $_GET["filter"][] = "type:\"Curriculum\"";
}

if (isset($_GET["query"])) {
    $_GET["search"] = 'nome_completo:' .$_GET['query']. '';
}


if (isset($fields)) {
    $_GET["fields"] = $fields;
}
$result_get = Requests::getParser($_GET);
$limit = $result_get['limit'];
$page = $result_get['page'];
$params = [];
$params["index"] = $index_cv;
$params["body"] = $result_get['query'];
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];
$result_get['query']["sort"]["nome_completo.keyword"]["unmapped_type"] = "long";
$result_get['query']["sort"]["nome_completo.keyword"]["missing"] = "_last";
$result_get['query']["sort"]["nome_completo.keyword"]["order"] = "asc";
$params["body"] = $result_get['query'];
$params["size"] = $limit;
$params["from"] = $result_get['skip'];
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
    <title>Lattes - Resultado da busca por trabalhos</title>

    <script src="http://cdn.jsdelivr.net/g/filesaver.js"></script>
    <script>
        function SaveAsFile(t, f, m) {
            try {
                var b = new Blob([t], {
                    type: m
                });
                saveAs(b, f);
            } catch (e) {
                window.open("data:" + m + "," + encodeURIComponent(t), '_blank', '');
            }
        }
    </script>
    <link rel="stylesheet" href="inc/css/style.css" />

</head>

<body>

    <?php
    if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    }
    ?>

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->
    <br /><br /><br /><br /><br />

    <main role="main">
        <div class="container">

            <div class="row">
                <div class="col-8">

                    <form action="result_autores.php" method="get" accept-charset="utf-8" enctype="multipart/form-data" id="searchresearchers">
                        <div class="input-group mb-3">
                            <input name="query" type="text" class="form-control" placeholder="Digite parte do nome do pesquisador" aria-label="Digite parte do nome do pesquisador" aria-describedby="button-addon2">
                            <button class="btn btn-outline-secondary" type="submit" form="searchresearchers" value="Submit">Pesquisar</button>
                        </div>
                    </form>

                    <!-- Navegador de resultados - Início -->
                    <?php ui::pagination($page, $total, $limit); ?>
                    <!-- Navegador de resultados - Fim -->

                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>
                        <?php if (empty($r["_source"]['datePublished'])) {
                            $r["_source"]['datePublished'] = "";
                        }
                        ?>

                        <div class="card">
                            <div class="card-body">

                                <div class="d-flex bd-highlight">
                                    <div class="p-2 flex-grow-1 bd-highlight">
                                        <h5 class="card-title"><a class="text-dark" href="profile/index.php?lattesID=<?php echo $r['_source']['lattesID']; ?>"><?php echo $r["_source"]['nome_completo']; ?></a></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>


                    <!-- Navegador de resultados - Início -->
                    <?php ui::pagination($page, $total, $limit); ?>
                    <!-- Navegador de resultados - Fim -->

                </div>
                <div class="col-4">

                    <hr>
                    <h3>Refinar meus resultados</h3>
                    <hr>
                    <?php
                    $facets = new facets();
                    $facets->query = $result_get['query'];

                    if (!isset($_GET)) {
                        $_GET = null;
                    }

                    $facets->facet(basename(__FILE__), "campus", 100, "Campus", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "desc_gestora", 100, "Gestora", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "unidade", 100, "Unidade", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "departamento", 100, "Departamento", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "divisao", 100, "Divisão", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "secao", 100, "Seção", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "ppg_nome", 100, "Nome do PPG", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "tipvin", 100, "Tipo de vínculo", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "genero", 100, "Genero", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "desc_nivel", 100, "Nível", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "desc_curso", 100, "Curso", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "numfuncional", 100, "Número funcional", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "tag", 100, "Tag", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "nacionalidade", 100, "Nacionalidade", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "pais_de_nascimento", 100, "País de nascimento", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "endereco.endereco_profissional.nomeInstituicaoEmpresa", 100, "Nome da Instituição ou Empresa", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "endereco.endereco_profissional.nomeOrgao", 100, "Nome do orgão", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "endereco.endereco_profissional.nomeUnidade", 100, "Nome da unidade", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "endereco.endereco_profissional.pais", 100, "País do endereço profissional", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "endereco.endereco_profissional.cidade", 100, "Cidade do endereço profissional", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_graduacao.nomeInstituicao", 100, "Instituição em que cursou graduação", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_graduacao.nomeCurso", 100, "Nome do curso na graduação", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_graduacao.statusDoCurso", 100, "Status do curso na graduação", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_mestrado.nomeInstituicao", 100, "Instituição em que cursou mestrado", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_mestrado.nomeCurso", 100, "Nome do curso no mestrado", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_mestrado.statusDoCurso", 100, "Status do curso no mestrado", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_mestradoProfissionalizante.nomeInstituicao", 100, "Instituição em que cursou mestrado profissional", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_mestradoProfissionalizante.nomeCurso", 100, "Nome do curso no mestrado profissional", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_mestradoProfissionalizante.statusDoCurso", 100, "Status do curso no mestrado profissional", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_doutorado.nomeInstituicao", 100, "Instituição em que cursou doutorado", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_doutorado.nomeCurso", 100, "Nome do curso no doutorado", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_doutorado.statusDoCurso", 100, "Status do curso no doutorado", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "formacao_academica_titulacao_livreDocencia.nomeInstituicao", 100, "Instituição em que cursou livre docência", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "formacao_maxima", 10, "Maior formação que iniciou", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "atuacao_profissional.nomeInstituicao", 100, "Instituição em que atuou profissionalmente", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "atuacao_profissional.vinculos.outroEnquadramentoFuncionalInformado", 100, "Enquadramento funcional", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "atuacao_profissional.vinculos.outroVinculoInformado", 100, "Vínculo", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "citacoes.SciELO.numeroCitacoes", 100, "Citações na Scielo", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "citacoes.SCOPUS.numeroCitacoes", 100, "Citações na Scopus", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "citacoes.Web of Science.numeroCitacoes", 100, "Citações na Web of Science", null, "_term", $_GET, $index_cv);
                    $facets->facet(basename(__FILE__), "citacoes.outras.numero_citacoes", 100, "Citações em outras bases", null, "_term", $_GET, $index_cv);

                    $facets->facet(basename(__FILE__), "data_atualizacao", 100, "Data de atualização do currículo", null, "_term", $_GET, $index_cv);

                    ?>
                    </ul>
                    <!-- Limitar por data - Início -->
                    <form action="result.php?" method="GET">
                        <h5 class="mt-3">Filtrar por ano de publicação</h5>
                        <?php
                        parse_str($_SERVER["QUERY_STRING"], $parsedQuery);
                        foreach ($parsedQuery as $k => $v) {
                            if (is_array($v)) {
                                foreach ($v as $v_unit) {
                                    echo '<input type="hidden" name="' . $k . '[]" value="' . htmlentities($v_unit) . '">';
                                }
                            } else {
                                if ($k == "initialYear") {
                                    $initialYearValue = $v;
                                } elseif ($k == "finalYear") {
                                    $finalYearValue = $v;
                                } else {
                                    echo '<input type="hidden" name="' . $k . '" value="' . htmlentities($v) . '">';
                                }
                            }
                        }

                        if (!isset($initialYearValue)) {
                            $initialYearValue = "";
                        }
                        if (!isset($finalYearValue)) {
                            $finalYearValue = "";
                        }

                        ?>
                        <div class="form-group">
                            <label for="initialYear">Ano inicial</label>
                            <input type="text" class="form-control" id="initialYear" name="initialYear" pattern="\d{4}" placeholder="Ex. 2010" value="<?php echo $initialYearValue; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="finalYear">Ano final</label>
                            <input type="text" class="form-control" id="finalYear" name="finalYear" pattern="\d{4}" placeholder="Ex. 2020" value="<?php echo $finalYearValue; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                    <!-- Limitar por data - Fim -->
                    <hr>
                </div>
            </div>
    </main>

    <?php include('inc/footer.php'); ?>

</body>

</html>