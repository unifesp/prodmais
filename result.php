<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php
    require 'inc/config.php';
    require 'inc/meta-header.php';
    require 'inc/functions.php';
    require 'inc/components/SList.php';

    if (isset($fields)) {
      $_POST["fields"] = $fields;
    }


    $result_post = Requests::postParser($_POST);
    if (!empty($_POST)) {
      $limit = $result_post['limit'];
      $page = $result_post['page'];
      $params = [];
      $params["index"] = $index;
      $params["body"] = $result_post['query'];
      $cursorTotal = $client->count($params);
      $total = $cursorTotal["count"];
      if (isset($_POST["sort"])) {
        $result_post['query']["sort"][$_POST["sort"]]["unmapped_type"] = "long";
        $result_post['query']["sort"][$_POST["sort"]]["missing"] = "_last";
        $result_post['query']["sort"][$_POST["sort"]]["order"] = "desc";
        $result_post['query']["sort"][$_POST["sort"]]["mode"] = "max";
      } else {
        $result_post['query']['sort']['datePublished.keyword']['order'] = "desc";
        $result_post['query']["sort"]["_uid"]["unmapped_type"] = "long";
        $result_post['query']["sort"]["_uid"]["missing"] = "_last";
        $result_post['query']["sort"]["_uid"]["order"] = "desc";
        $result_post['query']["sort"]["_uid"]["mode"] = "max";
      }
      $params["body"] = $result_post['query'];
      $params["size"] = $limit;
      $params["from"] = $result_post['skip'];
      $cursor = $client->search($params);
    } else {
      $limit = 10;
      $page = 1;
      $total = 0;
      $cursor["hits"]["hits"] = [];
    }

    /*pagination - start*/
    $get_data = $_POST;
    /*pagination - end*/

    ?>
    <meta charset="utf-8" />
    <title>
        <?php echo $branch; ?> - Resultado da busca
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Prodmais Unifesp." />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />


</head>

<body id="app-result" data-theme="<?php echo $theme; ?>">
    <?php
    if (file_exists('inc/google_analytics.php')) {
      include 'inc/google_analytics.php';
    }
    ?>
    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->

    <div class="p-result-container">

        <nav class="p-result-nav">
            <details id="filterlist" class="c-filterlist" onload="resizeMenu" open="">
                <summary class="c-filterlist__header">
                    <h3 class="c-filterlist__title">Refinar resultados</h3>
                </summary>

                <div class="c-filterlist__content">

                    <?php
                    $facets = new FacetsNew();
                    $facets->query = $result_post['query'];

                    if (!isset($_POST)) {
                      $_POST = null;
                    }

                    echo ($facets->facet(basename(__FILE__), "vinculo.ppg_nome", 100, "Nome do PPG", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "tipo", 100, "Tipo de material", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "author.person.name", 100, "Nome completo do autor", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.nome", 100, "Nome do autor vinculado à instituição", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.lattes_id", 100, "ID do Lattes", null, "_term", $_POST));

                    echo ($facets->facet(basename(__FILE__), "country", 200, "País de publicação", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "datePublished", 120, "Ano de publicação", "desc", "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "language", 40, "Idioma", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "lattes.natureza", 100, "Natureza", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "lattes.meioDeDivulgacao", 100, "Meio de divulgação", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "about", 100, "Palavras-chave", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "agencia_de_fomento", 100, "Agências de fomento", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeGrandeAreaDoConhecimento", 100, "Nome da grande área do conhecimento", null, "_term", $_POST));
                    //echo($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaAreaDoConhecimento", 100, "Nome da Área do Conhecimento", null, "_term", $_POST));
                    //echo($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaSubAreaDoConhecimento", 100, "Nome da Sub Área do Conhecimento", null, "_term", $_POST));
                    //echo($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaEspecialidade", 100, "Nome da Especialidade", null, "_term", $_POST));
                    
                    echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.classificacaoDoEvento", 100, "Classificação do evento", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "EducationEvent.name", 100, "Nome do evento", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "publisher.organization.location", 100, "Cidade", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.anoDeRealizacao", 100, "Ano de realização do evento", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.tituloDosAnaisOuProceedings", 100, "Título dos anais", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.isbn", 100, "ISBN dos anais", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.nomeDaEditora", 100, "Editora dos anais", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.cidadeDaEditora", 100, "Cidade da editora", null, "_term", $_POST));

                    echo ($facets->facet(basename(__FILE__), "isPartOf.name", 100, "Título do periódico", null, "_term", $_POST));

                    // echo($facets->facetExistsField(basename(__FILE__), "ExternalData.crossref.message.title", 100, "Dados coletados da Crossref?", null, "_term", $_POST));
                    // echo($facets->facet(basename(__FILE__), "ExternalData.crossref.message.author.affiliation.name", 100, "Crossref - Afiliação", null, "_term", $_POST));
                    // echo($facets->facet(basename(__FILE__), "ExternalData.crossref.message.funder.name", 100, "Crossref - Agência de financiamento", null, "_term", $_POST));
                    // echo($facets->facet(basename(__FILE__), "ExternalData.crossref.message.funder.DOI", 100, "Crossref - Agência de financiamento - DOI", null, "_term", $_POST));
                    // echo($facets->facet_range(basename(__FILE__), "ExternalData.crossref.message.is-referenced-by-count", 100, "Crossref - Número de citações obtidas", null, "_term", $_POST));
                    
                    echo ($facets->facet(basename(__FILE__), "vinculo.campus", 100, "Campus", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.desc_gestora", 100, "Gestora", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.unidade", 100, "Unidade", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.departamento", 100, "Departamento", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.divisao", 100, "Divisão", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.secao", 100, "Seção", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.tipvin", 100, "Tipo de vínculo", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.genero", 100, "Gênero", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.desc_nivel", 100, "Nível", null, "_term", $_POST));
                    echo ($facets->facet(basename(__FILE__), "vinculo.desc_curso", 100, "Curso", null, "_term", $_POST));

                    ?>

                </div>
            </details>
        </nav>

        <main class="p-result-main">
            <?php if (!empty($_REQUEST['search'])): ?>
            <div class="c-term">Termo pesquisado:
                <?php print_r($_REQUEST['search']); ?>
            </div>
            <?php endif ?>
            <?php
            if (isset($_REQUEST['filter'])) {
              echo '<div class="c-term">Filtro: ' . $_REQUEST['filter'][0] . '</div>';
            }
            ?>

            <?php ui::newpagination($page, $total, $limit, $_POST, 'result'); ?>
            <br />

            <?php if ($total == 0): ?>
            <br />
            <div class="alert alert-info" role="alert">
                Sua busca não obteve resultado. Você pode refazer sua busca abaixo:<br /><br />
                <form action="result.php">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" id="searchQuery"
                            aria-describedby="searchHelp" placeholder="Pesquise por termo ou autor">
                        <small id="searchHelp" class="form-text text-muted">Dica: Use * para busca por radical. Ex:
                            biblio*.</small>
                        <small id="searchHelp" class="form-text text-muted">Dica 2: Para buscas exatas, coloque entre
                            ""</small>
                        <small id="searchHelp" class="form-text text-muted">Dica 3: Você também pode usar operadores
                            booleanos:
                            AND, OR</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Pesquisar</button>

                </form>
            </div>
            <br /><br />
            <?php endif; ?>

            <?php

            foreach ($cursor["hits"]["hits"] as $r) {
              foreach ($r["_source"]["author"] as $author) {
                $authors[] = $author["person"]["name"];
              }

              !empty($r["_source"]['url']) ? $url = $r["_source"]['url'] : $url = '';
              !empty($r["_source"]['doi']) ? $doi = $r["_source"]['doi'] : $doi = '';
              !empty($r['_source']['isPartOf']['issn']) ? $issn = $r['_source']['isPartOf']['issn'] : $issn = '';
              !empty($r['_source']['isPartOf']['name']) ? $refName = $r['_source']['isPartOf']['name'] : $refName = '';
              !empty($r['_source']['datePublished']) ? $published = $r['_source']['datePublished'] : $published = '';

              SList::IntelectualProduction(
                $type = $r['_source']['tipo'],
                $name = $r['_source']['name'],
                $nAuthors = $authors,
                $doi,
                $url,
                $issn,
                $refName,
                $refVol = '',
                $refFascicle = '',
                $refPage = '',
                $evento = '',
                $datePublished = $published,
                $id = ''
              );
              unset($authors);
            }

            (!empty($datePublished) && !empty($id)) ? $query = DadosInternos::queryProdmais($name, $datePublished, $id) : $query = '';

            ui::newpagination($page, $total, $limit, $_POST, 'result');
            ?>

        </main>

    </div> <!-- end result-container -->

    <?php include('inc/footer.php'); ?>
    <script src="inc/js/pages/result.js"></script>
</body>

</html>