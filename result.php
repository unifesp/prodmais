<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php

  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Credentials: true");
  header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
  header('Access-Control-Max-Age: 1000');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

  require 'inc/config.php';
  require 'inc/meta-header.php';
  require 'inc/functions.php';
  require 'inc/components/SList.php';

  if (isset($fields)) {
    $_POST["fields"] = $fields;
  }

  if (empty($_POST)) {
    $_POST['search'] = "";
  }

  $result_post = Requests::postParser($_POST);
  if (!empty($_POST)) {
    $limit_records = $result_post['limit'];
    $page = $result_post['page'];
    $params = [];
    $params["index"] = $index;
    $params["body"] = $result_post['query'];
    $cursorTotal = $client->count($params);
    $total_records = $cursorTotal["count"];
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
    $params["size"] = $limit_records;
    $params["from"] = $result_post['skip'];
    $cursor = $client->search($params);
  } else {
    $limit_records = 50;
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
    <meta name="description" content="Prodmais" />
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
          $facets = new Facets();
          $facets->query = $result_post['query'];

          if (!isset($_POST)) {
            $_POST = null;
          }

          if ($mostrar_instituicao) {
            echo ($facets->facet(basename(__FILE__), "vinculo.instituicao", 100, "Instituição", null, "_key", $_POST, "result.php"));
          }
          echo ($facets->facet(basename(__FILE__), "vinculo.ppg_nome", 100, "Nome do PPG", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.tipvin", 100, "Tipo de vínculo", null, "_key", $_POST, "result.php"));
          if ($mostrar_area_concentracao) {
            echo ($facets->facet(basename(__FILE__), "vinculo.area_concentracao", 100, "Área de concentração", null, "_key", $_POST, "result.php"));
          }
          echo ($facets->facet(basename(__FILE__), "tipo", 100, "Tipo de material", null, "_key", $_POST, "result.php"));
          echo ($facets->facet_author(basename(__FILE__), "author.person.name", 100, "Nome completo do autor", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.nome", 100, "Nome do autor vinculado à instituição", null, "_key", $_POST, "result.php"));
          //echo ($facets->facet(basename(__FILE__), "vinculo.lattes_id", 100, "ID do Lattes", null, "_key", $_POST, "result.php"));

          echo ($facets->facet(basename(__FILE__), "country", 200, "País de publicação", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "datePublished", 120, "Ano de publicação", "desc", "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "language", 40, "Idioma", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "lattes.natureza", 100, "Natureza", null, "_key", $_POST, "result.php"));
          // echo ($facets->facet(basename(__FILE__), "lattes.meioDeDivulgacao", 100, "Meio de divulgação", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "about", 100, "Palavras-chave", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "agencia_de_fomento", 100, "Agências de fomento", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeGrandeAreaDoConhecimento", 100, "Nome da grande área do conhecimento", null, "_key", $_POST, "result.php"));
          //echo($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaAreaDoConhecimento", 100, "Nome da Área do Conhecimento", null, "_key", $_POST, "result.php"));
          //echo($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaSubAreaDoConhecimento", 100, "Nome da Sub Área do Conhecimento", null, "_key", $_POST, "result.php"));
          //echo($facets->facet(basename(__FILE__), "area_do_conhecimento.nomeDaEspecialidade", 100, "Nome da Especialidade", null, "_key", $_POST, "result.php"));

          echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.classificacaoDoEvento", 100, "Classificação do evento", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "EducationEvent.name", 100, "Nome do evento", null, "_key", $_POST, "result.php"));
          //echo ($facets->facet(basename(__FILE__), "publisher.organization.location", 100, "Cidade", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.anoDeRealizacao", 100, "Ano de realização do evento", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.tituloDosAnaisOuProceedings", 100, "Título dos anais", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.isbn", 100, "ISBN dos anais", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.nomeDaEditora", 100, "Editora dos anais", null, "_key", $_POST, "result.php"));
          //echo ($facets->facet(basename(__FILE__), "trabalhoEmEventos.cidadeDaEditora", 100, "Cidade da editora", null, "_key", $_POST, "result.php"));

          echo ($facets->facet(basename(__FILE__), "isPartOf.name", 100, "Título do periódico", null, "_key", $_POST, "result.php"));

          // echo($facets->facetExistsField(basename(__FILE__), "ExternalData.crossref.message.title", 100, "Dados coletados da Crossref?", null, "_key", $_POST, "result.php"));
          // echo($facets->facet(basename(__FILE__), "ExternalData.crossref.message.author.affiliation.name", 100, "Crossref - Afiliação", null, "_key", $_POST, "result.php"));
          // echo($facets->facet(basename(__FILE__), "ExternalData.crossref.message.funder.name", 100, "Crossref - Agência de financiamento", null, "_key", $_POST, "result.php"));
          // echo($facets->facet(basename(__FILE__), "ExternalData.crossref.message.funder.DOI", 100, "Crossref - Agência de financiamento - DOI", null, "_key", $_POST, "result.php"));
          // echo($facets->facet_range(basename(__FILE__), "ExternalData.crossref.message.is-referenced-by-count", 100, "Crossref - Número de citações obtidas", null, "_key", $_POST, "result.php"));

          echo ($facets->facet(basename(__FILE__), "vinculo.campus", 100, "Campus", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.desc_gestora", 100, "Gestora", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.unidade", 100, "Unidade", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.departamento", 100, "Departamento", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.divisao", 100, "Divisão", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.secao", 100, "Seção", null, "_key", $_POST, "result.php"));

          echo ($facets->facet(basename(__FILE__), "vinculo.genero", 100, "Gênero", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.desc_nivel", 100, "Nível", null, "_key", $_POST, "result.php"));
          echo ($facets->facet(basename(__FILE__), "vinculo.desc_curso", 100, "Curso", null, "_key", $_POST, "result.php"));

          if ($mostrar_existe_doi) {
            echo ($facets->facetExistsField(basename(__FILE__), "doi", 2, "Possui DOI preenchido?", null, "_key", $_POST, "result.php"));
          }

          if ($mostrar_openalex) {
            echo ($facets->facet(basename(__FILE__), "openalex.sustainable_development_goals.display_name", 100, "Sustainable Development Goals", null, "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex.open_access.oa_status", 100, "Status de acesso aberto segundo o OpenAlex?", null, "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex.authorships.institutions.display_name", 100, "Instituição normalizada - OpenAlex", null, "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex_referenced_works.name", 50, "Trabalhos mais citados - OpenAlex", null, "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex_referenced_works.datePublished", 50, "Ano de publicação dos trabalhos mais citados - OpenAlex", "desc", "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex_referenced_works.authorships.author.display_name", 50, "Autores mais citados - OpenAlex", null, "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex_referenced_works.language", 10, "Idioma dos trabalhos mais citados - OpenAlex", null, "_key", $_POST, "result.php"));
            echo ($facets->facet(basename(__FILE__), "openalex_referenced_works.source", 50, "Nome da publicação dos trabalhos mais citados - OpenAlex", null, "_key", $_POST, "result.php"));
            //echo ($facets->facetcited(basename(__FILE__), "openalex.referenced_works", 5, "5 trabalhos mais citados - OpenAlex", null, "_key", $_POST, "result.php"));
            echo ($facets->facet_range(basename(__FILE__), "openalex.cited_by_count", 100, "Número de citações obtidas - OpenAlex", null, "_key", $_POST, "result.php"));
          }

          ?>

                </div>
            </details>
        </nav>

        <main class="p-result-main">
            <?php if (!empty($_REQUEST['search'])) : ?>
            <div class="c-term">Termo pesquisado:
                <?php print_r($_REQUEST['search']); ?>
            </div>
            <?php endif ?>
            <?php
      if (isset($_REQUEST['filter'])) {
        foreach ($_REQUEST['filter'] as $filter) {
          $filter_array[] = '<div class="c-term">' . $filter . '</div>';
        }
        echo '<div class="c-term">Filtro: ' . implode('', $filter_array) . '</div>';
      }
      ?>

            <?php ui::newpagination($page, $total_records, $limit_records, $_POST, "result", 'result'); ?>
            <br />

            <?php if ($total_records == 0) : ?>
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
        if (isset($r["_source"]["author"])) {
          foreach ($r["_source"]["author"] as $author) {
            $authors[] = $author["person"]["name"];
          }
        } else {
          $authors[] = '';
        }


        !empty($r["_source"]['url']) ? $url = $r["_source"]['url'] : $url = '';
        !empty($r["_source"]['doi']) ? $doi = $r["_source"]['doi'] : $doi = '';
        !empty($r['_source']['isPartOf']['issn']) ? $issn = $r['_source']['isPartOf']['issn'] : $issn = '';
        !empty($r['_source']['isPartOf']['name']) ? $refName = $r['_source']['isPartOf']['name'] : $refName = '';
        !empty($r['_source']['datePublished']) ? $published = $r['_source']['datePublished'] : $published = '';
        isset($r['_source']['openalex']['cited_by_count']) ? $cited_by_count = strval($r['_source']['openalex']['cited_by_count']) : $cited_by_count = '';

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
          $id = '',
          $cited_by_count
        );
        unset($authors);
      }


      (!empty($datePublished) && !empty($id)) ? $query = DadosInternos::queryProdmais($name, $datePublished, $id) : $query = '';

      ui::newpagination($page, $total_records, $limit_records, $_POST, 'result');
      ?>

        </main>

    </div> <!-- end result-container -->

    <?php include('inc/footer.php'); ?>
    <script src="inc/js/pages/result.js"></script>

    <!-- PlumX Script -->
    <script type="text/javascript" src="//cdn.plu.mx/widget-details.js"></script>

    <!-- Aurora Widget -->
    <script type="text/javascript" src="https://aurora-sdg.labs.vu.nl/resources/widget.js"></script>

</body>

</html>