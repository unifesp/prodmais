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

if (isset($fields)) {
    $_GET["fields"] = $fields;
}

$result_get = Requests::getParser($_GET);
$limit = $result_get['limit'];
$page = $result_get['page'];
$params = [];
$params["index"] = $index;
$params["body"] = $result_get['query'];
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];
if (isset($_GET["sort"])) {
    $result_get['query']["sort"][$_GET["sort"]]["unmapped_type"] = "long";
    $result_get['query']["sort"][$_GET["sort"]]["missing"] = "_last";
    $result_get['query']["sort"][$_GET["sort"]]["order"] = "desc";
    $result_get['query']["sort"][$_GET["sort"]]["mode"] = "max";
} else {
    $result_get['query']['sort']['datePublished.keyword']['order'] = "desc";
    $result_get['query']["sort"]["_uid"]["unmapped_type"] = "long";
    $result_get['query']["sort"]["_uid"]["missing"] = "_last";
    $result_get['query']["sort"]["_uid"]["order"] = "desc";
    $result_get['query']["sort"]["_uid"]["mode"] = "max";
}
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
    require 'inc/meta-header.php';
    ?>
    <title><?php echo $branch; ?> - Resultado da busca</title>

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

    <main role="main" class="mt-5">
        <div class="container">

            <div class="row">
                <div class="col-md-8">

                    <!-- Navegador de resultados - Início -->
                    <?php ui::pagination($page, $total, $limit); ?>
                    <!-- Navegador de resultados - Fim -->


                    <!-- List of filters - Start -->
                    <?php
                    if (!empty($_SERVER["QUERY_STRING"])) {
                        $filters = ActiveFilters::Filters($_GET, $url_base);
                        echo implode("", $filters);
                    }
                    ?>
                    <!-- List of filters - End -->



                    <?php if ($total == 0) : ?>
                        <br />
                        <div class="alert alert-info" role="alert">
                            Sua busca não obteve resultado. Você pode refazer sua busca abaixo:<br /><br />
                            <form action="result.php">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control" id="searchQuery" aria-describedby="searchHelp" placeholder="Pesquise por termo ou autor">
                                    <small id="searchHelp" class="form-text text-muted">Dica: Use * para busca por radical. Ex: biblio*.</small>
                                    <small id="searchHelp" class="form-text text-muted">Dica 2: Para buscas exatas, coloque entre ""</small>
                                    <small id="searchHelp" class="form-text text-muted">Dica 3: Você também pode usar operadores booleanos: AND, OR</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Pesquisar</button>

                            </form>
                        </div>
                        <br /><br />

                    <?php endif; ?>

                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>

                        <?php //print_r($r); 
                        ?>
                        <?php if (empty($r["_source"]['datePublished'])) {
                            $r["_source"]['datePublished'] = "";
                        }
                        ?>

                        <div class="card">
                            <div class="card-body">

                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $r["_source"]['tipo']; ?> | <?php echo $r["_source"]['source']; ?></h6>
                                <h5 class="card-title text-dark"><?php echo $r["_source"]['name']; ?> (<?php echo $r["_source"]['datePublished']; ?>)</h5>


                                <?php
                                if (!empty($r["_source"]["concluido"])) {
                                    $r["_source"]["concluido"] == "Sim" ? print_r('<span class="badge badge-warning">Concluído</span>') : false;
                                }
                                ?>

                                <p class="text-muted"><b>Autoria:</b>
                                    <?php if (!empty($r["_source"]['author'])) : ?>
                                        <?php foreach ($r["_source"]['author'] as $autores) {
                                            $authors_array[] = '' . $autores["person"]["name"] . '';
                                        }
                                        $array_aut = implode(", ", $authors_array);
                                        unset($authors_array);
                                        print_r($array_aut);
                                        ?>
                                    <?php endif; ?>
                                </p>


                                <?php if (!empty($r["_source"]['isPartOf']['name'])) : ?>
                                    <p class="text-muted"><b>Fonte:</b> <a href="result.php?filter[]=isPartOf.name:&quot;<?php echo $r["_source"]['isPartOf']['name']; ?>&quot;"><?php echo $r["_source"]['isPartOf']['name']; ?></a></p>
                                <?php endif; ?>
                                <?php if (!empty($r["_source"]['isPartOf']['issn'])) : ?>
                                    <p class="text-muted"><b>ISSN:</b> <a href="result.php?filter[]=isPartOf.issn:&quot;<?php echo $r["_source"]['isPartOf']['issn']; ?>&quot;"><?php echo $r["_source"]['isPartOf']['issn']; ?></a></li>
                                    <?php endif; ?>
                                    <?php if (!empty($r["_source"]['EducationEvent']['name'])) : ?>
                                    <p class="text-muted"><b>Nome do evento:</b> <?php echo $r["_source"]['EducationEvent']['name']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($r["_source"]['doi'])) : ?>
                                    <p class="text-muted"><b>DOI:</b> <a href="https://doi.org/<?php echo $r["_source"]['doi']; ?>"><span id="<?php echo $r['_id'] ?>"><?php echo $r["_source"]['doi']; ?></span></a><!-- <a class="btn btn-warning" href="doi_to_elastic.php?doi=< ?php echo $r['_source']['doi'];?>&tag=< ?php echo $r['_source']['tag'][0];?>">Coletar dados da Crossref</a>-->
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($r["_source"]['url'])) : ?>
                                    <p class="text-muted"><b>URL:</b> <a href="<?php echo str_replace("]", "", str_replace("[", "", $r["_source"]['url'])); ?>"><?php echo str_replace("]", "", str_replace("[", "", $r["_source"]['url'])); ?></a></p>
                                <?php endif; ?>
                                <?php if (!empty($r["_source"]['ExternalData']['crossref']['message']['is-referenced-by-count'])) : ?>
                                    <p class="text-muted"><b>Citações na Crossref:</b> <?php echo $r["_source"]['ExternalData']['crossref']['message']['is-referenced-by-count']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($r["_source"]['ids_match'])) : ?>
                                    <?php foreach ($r["_source"]['ids_match'] as $id_match) : ?>
                                        <?php compararRegistros::match_id($id_match["id_match"], $id_match["nota"]); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php
                                if ($instituicao == "USP") {
                                    DadosExternos::query_bdpi($r["_source"]['name'], $r["_source"]['datePublished'], $r['_id']);
                                }
                                if (isset($index_source)) {
                                    DadosExternos::querySource($r["_source"]['name'], $r["_source"]['datePublished'], $r['_id']);
                                }

                                DadosInternos::queryProdmais($r["_source"]['name'], $r["_source"]['datePublished'], $r['_id']);

                                ?>

                                <?php if (!empty($r["_source"]['doi'])) : ?>
                                    <?php if (isset($elsevier_apikey)) : ?>
                                        <!--
                                        <object height="50" data="https://api.elsevier.com/content/abstract/citation-count?doi=< ?php echo $r["_source"]['doi']; ?>&httpAccept=text/html&apiKey=< ?php echo $elsevier_apikey; ?>"></object>
                                        -->
                                    <?php endif; ?>
                                <?php endif; ?>


                                <!--
                                    <div class="btn-group mt-3" role="group" aria-label="Botoes">

                                        <form method="post">
                                            < ?php if(isset($r["_source"]["concluido"])) : ?>
                                                < ?php if($r["_source"]["concluido"] == "Sim") : ?>
                                                    
                                                        <label><input type='hidden' value='Não' name="< ?php echo $r['_id'];?>"></label>
                                                        <button class="btn btn-primary">Desmarcar como concluído</button>
                                                
                                                < ?php else : ?>

                                                        <label><input type='hidden' value='Sim' name="< ?php echo $r['_id'];?>"></label>
                                                        <button class="btn btn-primary">Marcar como concluído</button>
                                                    
                                                < ?php endif; ?>
                                            < ?php else : ?>
                                                    
                                                        <label><input type='hidden' value='Sim' name="< ?php echo $r['_id'];?>"></label>
                                                        <button class="btn btn-primary">Marcar como concluído</button>
                                                    
                                             < ?php endif; ?>
                                            
                                        </form>
                                        
                                        < ?php
                                        if (!empty($dspaceRest)) {
                                            echo '<form action="dspaceConnect.php" method="get">
                                                <input type="hidden" name="createRecord" value="true" />
                                                <input type="hidden" name="_id" value="'.$r['_id'].'" />
                                                <button class="btn btn-secondary" name="btn_submit">Criar registro no DSpace</button>
                                                </form>';  
                                        }
                                        ?>
                                        
                                        < ?php 
                                        if ($instituicao == "USP") {
                                            echo '<a href="tools/export.php?search[]=_id:'.$r['_id'].'&format=alephseq" class="btn btn-secondary">Exportar Alephseq</a>';
                                        }
                                        ?>

                                    </div>
                                    -->

                            </div>
                        </div>
                    <?php endforeach; ?>


                    <!-- Navegador de resultados - Início -->
                    <?php ui::pagination($page, $total, $limit); ?>
                    <!-- Navegador de resultados - Fim -->

                </div>
                <div class="col-md-4">

                    <hr>
                    <h3>Refinar resultados</h3>
                    <hr>

                    <?php if (isset($result_get["query"]["query"]["bool"]["filter"][0]["term"]["vinculo.lattes_id.keyword"])) : ?>
                        <p><a class="btn btn-primary" href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=bibtex" rel="nofollow">Exportar para ORCID (BibTeX)</a></p>
                    <?php endif ?>
                    <!-- Limitar por data - Início -->
                    <form action="result.php?" method="GET">
                        <h5 class="mt-3">Filtrar por intervalo de data de publicação</h5>
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
                        <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
                    </form>
                    <hr>
                    <!-- Limitar por data - Fim -->

                    <!-- Facetas - Início -->
                    <div class="accordion" id="facets">
                        <?php
                        $facets = new facets();
                        $facets->query = $result_get['query'];

                        if (!isset($_GET)) {
                            $_GET = null;
                        }

                        $facets->facet(basename(__FILE__), "vinculo.ppg_nome", 100, "Nome do PPG", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "tipo", 100, "Tipo de material", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "author.person.name", 100, "Nome completo do autor", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.nome", 100, "Nome do autor vinculado à instituição", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.lattes_id", 100, "ID do Lattes", null, "_term", $_GET);

                        $facets->facet(basename(__FILE__), "country", 200, "País de publicação", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "datePublished", 120, "Ano de publicação", "desc", "_term", $_GET);
                        $facets->facet(basename(__FILE__), "language", 40, "Idioma", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "lattes.natureza", 100, "Natureza", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "lattes.meioDeDivulgacao", 100, "Meio de divulgação", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "about", 100, "Palavras-chave", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "agencia_de_fomento", 100, "Agências de fomento", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "area_do_conhecimento.nomeGrandeAreaDoConhecimento", 100, "Nome da grande área do conhecimento", null, "_term", $_GET);
                        //$facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaAreaDoConhecimento", 100, "Nome da Área do Conhecimento", null, "_term", $_GET);
                        //$facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaSubAreaDoConhecimento", 100, "Nome da Sub Área do Conhecimento", null, "_term", $_GET);
                        //$facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaEspecialidade", 100, "Nome da Especialidade", null, "_term", $_GET);

                        $facets->facet(basename(__FILE__), "trabalhoEmEventos.classificacaoDoEvento", 100, "Classificação do evento", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "EducationEvent.name", 100, "Nome do evento", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "publisher.organization.location", 100, "Cidade", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "trabalhoEmEventos.anoDeRealizacao", 100, "Ano de realização do evento", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "trabalhoEmEventos.tituloDosAnaisOuProceedings", 100, "Título dos anais", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "trabalhoEmEventos.isbn", 100, "ISBN dos anais", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "trabalhoEmEventos.nomeDaEditora", 100, "Editora dos anais", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "trabalhoEmEventos.cidadeDaEditora", 100, "Cidade da editora", null, "_term", $_GET);

                        $facets->facet(basename(__FILE__), "isPartOf.name", 100, "Título do periódico", null, "_term", $_GET);

                        // $facets->facetExistsField(basename(__FILE__), "ExternalData.crossref.message.title", 100, "Dados coletados da Crossref?", null, "_term", $_GET);
                        // $facets->facet(basename(__FILE__), "ExternalData.crossref.message.author.affiliation.name", 100, "Crossref - Afiliação", null, "_term", $_GET);
                        // $facets->facet(basename(__FILE__), "ExternalData.crossref.message.funder.name", 100, "Crossref - Agência de financiamento", null, "_term", $_GET);
                        // $facets->facet(basename(__FILE__), "ExternalData.crossref.message.funder.DOI", 100, "Crossref - Agência de financiamento - DOI", null, "_term", $_GET);
                        // $facets->facet_range(basename(__FILE__), "ExternalData.crossref.message.is-referenced-by-count", 100, "Crossref - Número de citações obtidas", null, "_term", $_GET);

                        $facets->facet(basename(__FILE__), "vinculo.campus", 100, "Campus", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.desc_gestora", 100, "Gestora", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.unidade", 100, "Unidade", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.departamento", 100, "Departamento", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.divisao", 100, "Divisão", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.secao", 100, "Seção", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.tipvin", 100, "Tipo de vínculo", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.genero", 100, "Gênero", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.desc_nivel", 100, "Nível", null, "_term", $_GET);
                        $facets->facet(basename(__FILE__), "vinculo.desc_curso", 100, "Curso", null, "_term", $_GET);

                        ?>
                        </ul>
                        <hr>
                        <h3>Exportar</h3>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=ris" rel="nofollow">Exportar em formato RIS</a></p>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=bibtex" rel="nofollow">Exportar em formato BIBTEX</a></p>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=dspace" rel="nofollow">Exportar em formato CSV para o DSpace</a></p>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=capesprint" rel="nofollow">Exportar em formato CSV para o CapesPrint</a></p>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=authorNetwork" rel="nofollow">Exportar em formato CSV para o Gephi da Rede de Co-Autoria incluindo publicações</a></p>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=authorNetworkWithoutPapers" rel="nofollow">Exportar em formato CSV para o Gephi da Rede de Co-Autoria sem publicações</a></p>
                        <p><a href="tools/export.php?<?php echo $_SERVER["QUERY_STRING"] ?>&format=ppgNetworkWithoutPapers" rel="nofollow">Exportar em formato CSV para o Gephi da Rede de PPGs</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include('inc/footer.php'); ?>

    <script>
        function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();
        }
    </script>

</body>

</html>