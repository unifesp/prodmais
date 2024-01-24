<?php

require 'inc/config.php';
require 'inc/functions.php';
require 'inc/components/SList.php';
require 'inc/components/TagCloud.php';
include_once '_fakedata.php';

function lattesID10($lattesID16)
{
    $url = 'https://lattes.cnpq.br/' . $lattesID16 . '';

    $headers = @get_headers($url);

    $lattesID10 = "";
    foreach ($headers as $h) {
        if (substr($h, 0, 87) == 'Location: http://buscatextual.cnpq.br/buscatextual/visualizacv.do?metodo=apresentar&id=') {
            $lattesID10 = trim(substr($h, 87));
            break;
        }
    }
    return $lattesID10;
}

if (!empty($_REQUEST["lattesID"])) {

    if (isset($_GET["filter"])) {
        if (!in_array("type:\"Curriculum\"", $_GET["filter"])) {
            $_GET["filter"][] = "type:\"Curriculum\"";
        }
    } else {
        $_GET["filter"][] = "type:\"Curriculum\"";
    }
    $_GET["filter"][] = 'lattesID:' . $_GET["lattesID"] . '';
    $result_get = Requests::getParser($_GET);
    $limit = $result_get['limit'];
    $page = $result_get['page'];
    $params = [];
    $params["index"] = $index_cv;
    $params["body"] = $result_get['query'];
    $cursorTotal = $client->count($params);
    $total = $cursorTotal["count"];

    if ($total == 0) {
        echo '<script>window.location.href = "index.php";</script>';
        die();
    }

    $params["body"] = $result_get['query'];
    $params["size"] = $limit;
    $params["from"] = $result_get['skip'];
    $cursor = $client->search($params);
    $profile = $cursor["hits"]["hits"][0]["_source"];



    $filter_works["filter"][] = 'vinculo.lattes_id:"' . $_GET["lattesID"] . '"';
    $result_get_works = Requests::getParser($filter_works);
    $params_works = [];
    $params_works["index"] = $index;
    $params_works["body"] = $result_get_works['query'];

    $worksTotal = $client->count($params_works);
    $totalWorks = $worksTotal["count"];

    $params_works["size"] = 9999;
    $params_works["body"]["aggs"]["counts"]["terms"]["field"] = "datePublished.keyword";
    $params_works["body"]["aggs"]["counts"]["terms"]["order"]["_key"] = "desc";
    $cursor_works = $client->search($params_works);

    if (isset($cursor_works["aggregations"])) {
        $works = $cursor_works["aggregations"]["counts"]["buckets"];
        $years_ok = [];
        $years_array_values = [];
        for ($i = date("Y"); $i >= date("Y", strtotime("-4 year")); $i--) {

            for ($j = 0; $j < count($works); $j++) {
                if ($works[$j]["key"] == $i) {
                    $trabalhos_publicados[] = [
                        "year" => $i,
                        "total" => $works[$j]["doc_count"]
                    ];
                    $years_array_values[] = $works[$j]["doc_count"];
                    $years_ok[] = $i;
                }
            }
            if (in_array($i, $years_ok)) {
                continue;
            } else {
                $trabalhos_publicados[] = [
                    "year" => $i,
                    "total" => 0
                ];
            }
        }
        if (count($years_array_values) == 0) {
            $years_array_max = 1;
        } else {
            $years_array_max = max($years_array_values);
        }
    }

    $lattesID10 = lattesID10($_GET["lattesID"]);

    $totalOrientacoes = 0;
    if (isset($profile['orientacoes'])) {
        $totalOrientacoes = $totalOrientacoes + count($profile['orientacoes']);
    }
    if (isset($profile['orientacoesconcluidas'])) {
        $totalOrientacoes = $totalOrientacoes + count($profile['orientacoesconcluidas']);
    }
} else {
    echo '<script>window.location.href = "index.php";</script>';
    die();
}
?>

<!DOCTYPE HTML>

<html lang="pt-br">

<head>
    <?php
    include 'inc/meta-header.php';
    ?>
    <title>
        <?php echo $branch ?> — Perfil do pesquisador -
        <?php echo $profile["nome_completo"] ?>
    </title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Perfil do pesquisador" />
    <meta name="keywords" content="Produção acadêmica, Lattes, ORCID" />
    <link rel="stylesheet" href="<?php echo $url_base; ?>/inc/sass/main.css" />
</head>

<body data-theme="<?php echo $theme; ?>" class="c-wrapper-body">
    <?php
    if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    } elseif (file_exists('../inc/google_analytics.php')) {
        include '../inc/google_analytics.php';
    }
    ?>

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->

    <main id="profile" class="c-wrapper-container">
        <div class="c-wrapper-paper">
            <div class="c-wrapper-inner">
                <div id="top"></div>
                <div class="p-profile-header">
                    <div class="p-profile-header-one">

                        <div class="c-who-s">
                            <img class="c-who-s-pic" src="https://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&amp;bcv=true&amp;id=<?php echo $lattesID10; ?>" />
                        </div>

                    </div>

                    <div class="p-profile-header-two">
                        <h1 class="t-h1">
                            <?php echo $profile["nome_completo"] ?>

                            <?php if ($profile["nacionalidade"] == "B") : ?>
                                <img class="country-flag" src="<?php echo $url_base; ?>/inc/images/country_flags/br.svg" alt="nacionalidade brasileira" title="nacionalidade brasileira" />
                            <?php endif; ?>
                        </h1>

                        <!-- <div class="u-mb-20  "></div> -->
                        <?php if (!empty($profile["instituicao"][0])) : ?>
                            <?php foreach ($profile["instituicao"] as $key_instituicao => $instituicao) : ?>
                                <?php if (is_array($instituicao)) : ?>
                                    <h3 class="t t-prof"><?php echo implode(" ", $instituicao) ?></h3>
                                <?php else : ?>
                                    <h3 class="t t-prof"><?php echo $instituicao ?></h3>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <h3 class="t t-prof">Universidade Federal de São Paulo</h3>
                        <?php endif; ?>
                        <?php if (!empty($profile["unidade"][0])) : ?>
                            <p class="t t-prof">
                                <?php echo $profile["unidade"][0] ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($profile["departamento"][0])) : ?>
                            <p class="t t-prof">
                                <?php echo $profile["departamento"][0] ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($profile["ppg_nome"][0])) : ?>
                            <?php foreach ($profile["ppg_nome"] as $key => $ppg_nome) : ?>
                                <p class="t t-prof">Programa de Pós-Graduação:
                                    <?php echo $ppg_nome ?>
                                </p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($profile["email"])) : ?>
                            <p class="t t-prof">E-Mail:
                                <?php echo $profile["email"] ?>
                            </p>
                        <?php endif; ?>

                        <hr class="c-line" />

                        <div class="p-profile-header-numbers">

                            <div class="d-icon-text u-mx-10">
                                <i class="i i-sm i-articlePublished" title="Trabalhos publicados" alt="Trabalhos publicados"></i>
                                <span class="t">
                                    <?php echo $totalWorks; ?>
                                </span>
                            </div>

                            <div class="d-icon-text">
                                <i class="i i-sm i-orientation" title="Orientações " alt="Orientações"></i>
                                <?php echo $totalOrientacoes; ?>
                            </div>
                        </div>

                    </div>

                    <div class="p-profile-header-three">
                        <a class="u-skip" href=”#skipc-graph”>Pular gráfico</a>

                        <div class="c-graph">
                            <div class="c-graph-line">
                                <div class="c-graph-icon"></div>
                                <div class="c-graph-label">De <?php echo date("Y", strtotime("-4 year")); ?> a
                                    <?php echo date("Y"); ?>
                                </div>
                            </div>

                            <div class="c-graph-line">
                                <?php
                                foreach ($trabalhos_publicados as $i => $j) {
                                    if ($j['total'] / $years_array_max <= 1 && $j['total'] / $years_array_max > 0.8) {
                                        $weight = 4;
                                    } elseif ($j['total'] / $years_array_max <= 0.8 && $j['total'] / $years_array_max > 0.6) {
                                        $weight = 3;
                                    } elseif ($j['total'] / $years_array_max <= 0.6 && $j['total'] / $years_array_max > 0.4) {
                                        $weight = 2;
                                    } elseif ($j['total'] / $years_array_max <= 0.4 && $j['total'] / $years_array_max > 0.2) {
                                        $weight = 1;
                                    } else {
                                        $weight = 0;
                                    }
                                    echo "<div class='c-graph-unit' data-weight='{$weight}' title='{$j['year']} — total: {$j['total']}'></div>";
                                }
                                unset($i);
                                unset($j);
                                unset($weight);
                                ?>
                                <span class="c-graph-label">Trabalhos publicados</span>
                            </div>

                        </div>
                        <span class="u-skip" id="skipc-graph”"></span>
                    </div>
                </div>
                <div class="profile-tabs" onload="changeTab('1')">
                    <div class="c-profmenu">
                        <button id="tab-btn-1" class="c-profmenu-btn" v-on:click="changeTab('1')" title="Sobre" alt="Sobre">
                            <i class="i i-sm i-aboutme c-profmenu-ico"></i>
                            <span class="c-profmenu-text">Sobre</span>
                        </button>

                        <button id=" tab-btn-2" class="c-profmenu-btn" v-on:click="changeTab('2')" title="Produção" alt="Produção">
                            <i class="i i-sm i-prodsymbol c-profmenu-ico"></i>
                            <span class="c-profmenu-text">Produção</span>
                        </button>

                        <button id="tab-btn-3" class="c-profmenu-btn" v-on:click="changeTab('3')" title="Atuação" alt="Atuação">
                            <i class="i i-sm i-working c-profmenu-ico"></i>
                            <span class="c-profmenu-text">Atuação</span>
                        </button>

                        <?php if ($totalOrientacoes != 0) : ?>
                            <button id="tab-btn-4" class="c-profmenu-btn" v-on:click="changeTab('4')" title="Ensino" alt="Ensino">
                                <i class="i i-sm i-teaching c-profmenu-ico"></i>
                                <span class="c-profmenu-text">Ensino</span>
                            </button>
                        <?php endif; ?>

                        <button id="tab-btn-5" class="c-profmenu-btn" v-on:click="changeTab('5')" title="Gestão" alt="Gestão">
                            <div class="i i-sm i-managment c-profmenu-ico"></div>
                            <span class="c-profmenu-text">Gestão</span>
                        </button>
                        <button id="tab-btn-6" class="c-profmenu-btn" v-on:click="changeTab('6')" title="Pesquisa" alt="Pesquisa">
                            <div class="i i-sm i-research c-profmenu-ico"></div>
                            <span class="c-profmenu-text">Pesquisa</span>
                        </button>
                    </div><!-- end c-profmenu  -->
                </div> <!-- end profile-tabs -->
                <div class="c-wrapper-inner u-m-20">
                    <transition name="tabeffect">
                        <div id="tab-one" class="c-tab-content" v-if="tabOpened == '1'">
                            <div class="t-justify">
                                <h3 class="t t-h3">Resumo</h3>
                                <p class="t">
                                    <?php echo $profile["resumo_cv"]["texto_resumo_cv_rh"] ?>
                                </p>
                                <p class="t-right ty-light">Fonte: Lattes CNPq</p>
                            </div>
                            <h3 class="t t-h3">Nomes em citações bibliográficas</h3>
                            <p class="t-prof"><?php echo $profile["nome_em_citacoes_bibliograficas"] ?></p>
                            <hr class="c-line u-my-20" />
                            <h3 class="t t-h3">Exportar dados</h3>
                            <p><a href="tools/export_old.php?&format=bibtex&search=vinculo.lattes_id:<?php echo $profile["lattesID"]; ?>" target="_blank" rel="nofollow">Exportar produção no formato BIBTEX</a></p>
                            <hr class="c-line u-my-20" />
                            <p class="t t-b">Perfis na web</p>
                            <div class="dh">
                                <?php if (!empty($profile['lattesID'])) : ?>
                                    <a href="https://lattes.cnpq.br/<?php echo $profile['lattesID']; ?>" target="_blank" rel="external">
                                        <img class="c-socialicon" src="<?php echo $url_base; ?>/inc/images/logos/academic/logo_lattes.svg" alt="Lattes" title="Lattes" />
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($profile['orcid_id'])) : ?>
                                    <a href="<?php echo $profile['orcid_id']; ?>" target="_blank" rel="external">
                                        <img class="c-socialicon" src="<?php echo $url_base; ?>/inc/images/logos/academic/logo_research_id.svg" alt="ORCID" title="ORCID" />
                                    </a>
                                <?php endif; ?>

                            </div>

                            <hr class="c-line u-my-20" />
                            <h3 class="t t-h3">Tags mais usadas</h3>
                            <?php
                            $authorfacets = new DataFacets();
                            $authorfacets->query = $result_get['query'];

                            if (!isset($_GET)) {
                                $_GET = null;
                            }

                            $resultaboutfacet = json_decode($authorfacets->authorfacet("about", 120, "Palavras-chave do autor", null, "_term", $_GET), true);
                            shuffle($resultaboutfacet);

                            Tag::cloud($resultaboutfacet, $hasLink = false);
                            ?>
                            <hr class="c-line u-my-20" />
                            <?php if (isset($profile["idiomas"])) : ?>
                                <div>
                                    <h3 class="t t-h3">Idiomas</h3>
                                    <?php foreach ($profile["idiomas"] as $key => $idioma) : ?>

                                        <div class="s-list">
                                            <div class="s-list-bullet">
                                                <?php
                                                switch ($idioma["descricaoDoIdioma"]) {
                                                    case "Inglês":
                                                        $lang = 'en';
                                                        break;
                                                    case "Espanhol":
                                                        $lang = 'es';
                                                        break;
                                                    case "Português":
                                                        $lang = 'pt';
                                                        break;
                                                    case "Italiano":
                                                        $lang = 'it';
                                                        break;
                                                    case "Francês":
                                                        $lang = 'fr';
                                                        break;
                                                    case "Alemão":
                                                        $lang = 'de';
                                                        break;
                                                    case "Russo":
                                                        $lang = 'ru';
                                                        break;
                                                    case "Mandarin":
                                                        $lang = 'zh';
                                                        break;
                                                    default:
                                                        $lang = 'idioma';
                                                        break;
                                                };
                                                $idi = $idioma["descricaoDoIdioma"];

                                                echo "<i class='i i-lang-$lang i-lang' title='$idi' alt='$idi'></i>"
                                                ?>
                                            </div>

                                            <div class="s-list-content">
                                                <p class="t t-b"><?php echo $idioma["descricaoDoIdioma"] ?></p>
                                                <p class="t u-mb-05">
                                                    Compreende <?php echo strtolower($idioma["proficienciaDeCompreensao"]) ?>,
                                                    Fala <?php echo strtolower($idioma["proficienciaDeFala"]) ?>,
                                                    Lê <?php echo strtolower($idioma["proficienciaDeLeitura"]) ?>,
                                                    Escreve <?php echo strtolower($idioma["proficienciaDeEscrita"]) ?>
                                                </p>
                                            </div>
                                        </div> <!-- end s-list -->
                                    <?php endforeach; ?>
                                </div> <!-- end u-left -->
                            <?php endif; ?>

                            <hr class="c-line u-my-20" />
                            <h3 class="t t-h3">Formação</h3>

                            <!-- Livre Docência -->
                            <?php
                            if (isset($profile["formacao_academica_titulacao_livreDocencia"])) {

                                foreach ($profile["formacao_academica_titulacao_livreDocencia"] as $key => $livreDocencia) {

                                    !empty($livreDocencia["area_do_conhecimento"][0]["nomeDaEspecialidade"]) ?
                                        $b = $livreDocencia["area_do_conhecimento"][0]["nomeDaEspecialidade"] : $b = '';

                                    !empty($livreDocencia["area_do_conhecimento"][0]["nomeDaSubAreaDoConhecimento"]) ?
                                        $c = $livreDocencia["area_do_conhecimento"][0]["nomeDaSubAreaDoConhecimento"] : $c = '';

                                    SList::genericItem(
                                        $type = 'formation',
                                        $itemName = 'Livre Docência',
                                        $itemNameLink = '',
                                        $itemInfoA = $livreDocencia["tituloDoTrabalho"],
                                        $itemInfoB = $b,
                                        $itemInfoC = $c,
                                        $itemInfoD = $livreDocencia["nomeInstituicao"],
                                        $itemInfoE = '',
                                        $authors = '',
                                        $tags = '',
                                        $yearStart = '',
                                        $yearEnd = $livreDocencia["anoDeObtencaoDoTitulo"]
                                    );
                                }
                            }
                            ?>
                            <!-- Doutorado -->
                            <?php
                            if (isset($profile["formacao_academica_titulacao_doutorado"])) {
                                foreach ($profile["formacao_academica_titulacao_doutorado"] as $key => $doutorado) {

                                    !empty($doutorado["area_do_conhecimento"][0]["nomeDaEspecialidade"]) ?
                                        $especialidade = $doutorado["area_do_conhecimento"][0]["nomeDaEspecialidade"] : $especialidade = '';

                                    !empty($doutorado["area_do_conhecimento"][0]["nomeDaSubAreaDoConhecimento"]) ?
                                        $subArea = $doutorado["area_do_conhecimento"][0]["nomeDaSubAreaDoConhecimento"] : $subArea = '';

                                    SList::genericItem(
                                        $type = 'formation',
                                        $itemName = 'Doutorado em ' . $doutorado["nomeCurso"],
                                        $itemNameLink = '',
                                        $itemInfoA = $doutorado["tituloDaDissertacaoTese"],
                                        $itemInfoB = $especialidade,
                                        $itemInfoC = $subArea,
                                        $itemInfoD = 'Orientação: ' . $doutorado["nomeDoOrientador"],
                                        $itemInfoE = $doutorado["nomeInstituicao"],
                                        $authors = '',
                                        $yearStart = $doutorado["anoDeInicio"],
                                        $yearEnd = $doutorado["anoDeConclusao"]
                                    );
                                }
                            }
                            ?>

                            <!-- Mestrado -->
                            <?php
                            if (isset($profile["formacao_academica_titulacao_mestrado"])) {
                                foreach ($profile["formacao_academica_titulacao_mestrado"] as $key => $mestrado) {

                                    !empty($mestrado["area_do_conhecimento"][0]["nomeDaEspecialidade"]) ?
                                        $especialidade = $mestrado["area_do_conhecimento"][0]["nomeDaEspecialidade"] : $especialidade = '';

                                    !empty($mestrado["area_do_conhecimento"][0]["nomeDaSubAreaDoConhecimento"]) ?
                                        $subArea = $mestrado["area_do_conhecimento"][0]["nomeDaSubAreaDoConhecimento"] : $subArea = '';

                                    SList::genericItem(
                                        $type = 'formation',
                                        $itemName = 'Mestrado em ' . $mestrado["nomeCurso"],
                                        $itemNameLink = '',
                                        $itemInfoA = $mestrado["tituloDaDissertacaoTese"],
                                        $itemInfoB = $especialidade,
                                        $itemInfoC = $subArea,
                                        $itemInfoD = 'Orientação: ' . $mestrado["nomeDoOrientador"],
                                        $itemInfoE = $mestrado["nomeInstituicao"],
                                        $authors = '',
                                        $tags = '',
                                        $yearStart = $mestrado["anoDeInicio"],
                                        $yearEnd = $mestrado["anoDeConclusao"]
                                    );
                                }
                            }
                            ?>
                            <!-- Graduação -->
                            <?php
                            if (isset($profile["formacao_academica_titulacao_graduacao"])) {
                                foreach ($profile["formacao_academica_titulacao_graduacao"] as $key => $graduacao) {
                                    $orientador = '';
                                    !empty($graduacao["nomeDoOrientador"]) ?
                                        $orientador = 'Orientação: ' . $graduacao["nomeDoOrientador"] : $orientador = '';
                                    SList::genericItem(
                                        $type = 'formation',
                                        $itemName = 'Graduação em ' . $graduacao["nomeCurso"],
                                        $itemNameLink = '',
                                        $itemInfoA = $graduacao["tituloDoTrabalhoDeConclusaoDeCurso"],
                                        $itemInfoB = $orientador,
                                        $itemInfoC = $graduacao["nomeInstituicao"],
                                        $itemInfoD = '',
                                        $itemInfoE = '',
                                        $authors = '',
                                        $tags = '',
                                        $yearStart = $graduacao["anoDeInicio"],
                                        $yearEnd = $graduacao["anoDeConclusao"]
                                    );
                                }
                            }
                            ?>
                        </div>
                    </transition>
                    <transition name="tabeffect">
                        <div id="tab-two" class="c-tab-content" v-if="tabOpened == '2'">
                            <div class="profile-pi">
                                <h3 class="t t-h3 u-mb-20">Produção</h3>
                                <?php
                                foreach ($cursor_works['hits']['hits'] as $key => $work) {
                                    $works[$work['_source']['datePublished']][] = $work;
                                }
                                for ($i = 2040; $i >= 1900; $i -= 1) {
                                    if (!empty($works[$i])) {
                                        echo '<hr class="c-line"></hr>
                                            <h3 class="t-b c-pi-year">' . $i . '</h3>
                                            <hr class="c-line u-mb-20"></hr> ';

                                        echo '<ul name="Lista de produções no ano de ' . $i . '">';
                                        foreach ($works[$i] as $key => $work) {

                                            $authors = [];
                                            foreach ($work["_source"]["author"] as $author) {
                                                $authors[] = $author["person"]["name"];
                                            }
                                            !empty($work['_source']['url']) ?
                                                $url = $work['_source']['url'] : $url = '';

                                            !empty($work['_source']['doi']) ?
                                                $doi = $work['_source']['doi'] : $doi = '';

                                            !empty($work['_source']['$issn']) ?
                                                $issn = $work['_source']['$issn'] : $issn = '';

                                            !empty($work['_source']['isPartOf']['name']) ?
                                                $refName = $work['_source']['isPartOf']['name'] : $refName = '';

                                            !empty($work['_source']['isPartOf']['volume']) ?
                                                $vol = $work['_source']['isPartOf']['volume'] : $vol = '';

                                            !empty($work['_source']['isPartOf']['fasciculo']) ?
                                                $fascicle = $work['_source']['isPartOf']['fasciculo'] : $fascicle = '';

                                            !empty($work['_source']['pageStart']) ?
                                                $pageStart = $work['_source']['pageStart'] : $pageStart = '';

                                            !empty($work['_source']['datePublished']) ?
                                                $datePublished = $work['_source']['datePublished'] : $datePublished = '';

                                            SList::IntelectualProduction(
                                                $type = $work['_source']['tipo'],
                                                $name = $work['_source']['name'],
                                                $authors = $authors,
                                                $url = $url,
                                                $doi = $doi,
                                                $issn = $issn,
                                                $refName =  $refName,
                                                $refVol = $vol,
                                                $refFascicle =  $fascicle,
                                                $refPage = $pageStart,
                                                $datePublished = $datePublished,
                                                $cited_by_count = '',
                                                $aurorasdg = ''
                                            );
                                        }
                                        unset($authors);
                                        echo '</ul>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </transition>
                    <transition name="tabeffect">
                        <div id="tab-three" class="c-tab-content" v-if="tabOpened == '3'">
                            <h3 class="t t-h3 u-mb-20">Atuações</h3>
                            <?php
                            foreach ($profile['atuacoes_profissionais'] as $key => $atuacoes_profissionais) {
                                foreach ($atuacoes_profissionais as $key => $atuacao_profissional) {
                                    echo '<h4 class="t t-subtitle">' . $atuacao_profissional['@attributes']['NOME-INSTITUICAO'] . '</h4>';
                                    if (isset($atuacao_profissional['VINCULOS'])) {
                                        if (count($atuacao_profissional['VINCULOS']) == 1) {
                                            echo '<ul>';
                                            SList::genericItem(
                                                $type = "professional",
                                                $itemName = $atuacao_profissional['VINCULOS']['@attributes']['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'],
                                                $itemNameLink = '',
                                                $itemInfoA = $atuacao_profissional['VINCULOS']['@attributes']['OUTRO-VINCULO-INFORMADO'],
                                                $itemInfoB = '',
                                                $itemInfoC = '',
                                                $itemInfoD = '',
                                                $itemInfoE = '',
                                                $authors = '',
                                                $tags = '',
                                                $yearStart = $atuacao_profissional['VINCULOS']['@attributes']['ANO-INICIO'],
                                                $yearEnd = $atuacao_profissional['VINCULOS']['@attributes']['ANO-FIM']
                                            );
                                            echo '</ul>';
                                        } else {
                                            echo '<ul>';
                                            for ($i_atuacao_profissional = 0; $i_atuacao_profissional <= (count($atuacao_profissional['VINCULOS']) - 1); $i_atuacao_profissional++) {
                                                SList::genericItem(
                                                    $type = "professional",
                                                    $itemName = $atuacao_profissional['VINCULOS'][$i_atuacao_profissional]['@attributes']['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'],
                                                    $itemNameLink = '',
                                                    $itemInfoA = $atuacao_profissional['VINCULOS'][$i_atuacao_profissional]['@attributes']['OUTRO-VINCULO-INFORMADO'],
                                                    $itemInfoB = '',
                                                    $itemInfoC = '',
                                                    $itemInfoD = '',
                                                    $itemInfoE = '',
                                                    $authors = '',
                                                    $tags = '',
                                                    $yearStart = $atuacao_profissional['VINCULOS'][$i_atuacao_profissional]['@attributes']['ANO-INICIO'],
                                                    $yearEnd = $atuacao_profissional['VINCULOS'][$i_atuacao_profissional]['@attributes']['ANO-FIM']
                                                );
                                            }
                                            echo '</ul>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </div> <!-- end tab-three -->
                    </transition>
                    <transition name="tabeffect">
                        <div id="tab-four" class="c-tab-content" v-if="tabOpened == '4'">
                            <h3 class="t t-h3 u-mb-20">Ensino</h3>
                            <h3 class="t t-h3 u-mb-20">Orientações e supervisões</h3>

                            <?php
                            if (!empty($profile['orientacoes'])) {
                                $orientacoes_andamento_labels = ['Supervisão de pós-doutorado', 'Tese de doutorado', 'Dissertação de mestrado'];
                                foreach ($orientacoes_andamento_labels as $orientacao_andamento_label) {
                                    $i_orientacao_andamento = 0;
                                    foreach ($profile['orientacoes'] as $orientacao_andamento) {
                                        if ($orientacao_andamento['natureza'] == $orientacao_andamento_label) {
                                            $orientacao_andamento_array[$orientacao_andamento_label][$i_orientacao_andamento] = $orientacao_andamento;
                                        }
                                        $i_orientacao_andamento++;
                                    }
                                    if (isset($orientacao_andamento_array[$orientacao_andamento_label])) {
                                        if (count($orientacao_andamento_array[$orientacao_andamento_label]) > 0) {
                                            echo '<h4 class="t t-subtitle u-mb-20">' . $orientacao_andamento_label . ' em andamento</h4>';
                                            echo '<ul>';
                                            foreach ($orientacao_andamento_array[$orientacao_andamento_label] as $orientacao_andamento_echo) {
                                                SList::genericItem(
                                                    $type = 'orientation',
                                                    $itemName = $orientacao_andamento_echo["nomeDoOrientando"],
                                                    $itemNameLink = "https://lattes.cnpq.br/" . $orientacao_andamento_echo["numeroIDOrientado"],
                                                    $itemInfoA = $orientacao_andamento_echo["titulo"],
                                                    $itemInfoB = $orientacao_andamento_echo["nomeDoCurso"],
                                                    $itemInfoC = $orientacao_andamento_echo["nomeDaAgencia"],
                                                    $itemInfoD = $orientacao_andamento_echo["nomeDaInstituicao"],
                                                    $itemInfoE = '',
                                                    $authors = '',
                                                    $tags = '',
                                                    $yearStart = $orientacao_andamento_echo["ano"],
                                                    $yearEnd = ''
                                                );
                                            }
                                            echo '</ul>';
                                        }
                                    }
                                    unset($orientacao_andamento_array);
                                }
                            }
                            ?>

                            <?php
                            if (!empty($profile['orientacoesconcluidas'])) {
                                $orientacoes_concluidas_labels = ['Supervisão de pós-doutorado', 'Tese de doutorado', 'Dissertação de mestrado'];
                                foreach ($orientacoes_concluidas_labels as $orientacao_concluidas_label) {
                                    $i_orientacao_concluidas = 0;
                                    foreach ($profile['orientacoesconcluidas'] as $orientacao_concluidas) {
                                        if ($orientacao_concluidas['natureza'] == $orientacao_concluidas_label) {
                                            $orientacao_concluidas_array[$orientacao_concluidas_label][$i_orientacao_concluidas] = $orientacao_concluidas;
                                        }
                                        $i_orientacao_concluidas++;
                                    }
                                    if (isset($orientacao_concluidas_array)) {
                                        if (count($orientacao_concluidas_array[$orientacao_concluidas_label]) > 0) {
                                            echo '<h4 class="t t-subtitle u-mb-20">' . $orientacao_concluidas_label . ' concluídas</h4>';
                                            echo '<ul>';
                                            foreach ($orientacao_concluidas_array[$orientacao_concluidas_label] as $orientacao_concluidas_echo) {
                                                SList::genericItem(
                                                    $type = 'orientation',
                                                    $itemName = $orientacao_concluidas_echo["nomeDoOrientando"],
                                                    $itemNameLink = "https://lattes.cnpq.br/" . $orientacao_concluidas_echo["numeroIDOrientado"],
                                                    $itemInfoA = $orientacao_concluidas_echo["titulo"],
                                                    $itemInfoB = $orientacao_concluidas_echo["nomeDoCurso"],
                                                    $itemInfoC = $orientacao_concluidas_echo["nomeDaAgencia"],
                                                    $itemInfoD = $orientacao_concluidas_echo["nomeDaInstituicao"],
                                                    $itemInforE = '',
                                                    $authors = '',
                                                    $tags = '',
                                                    $yearStart = '',
                                                    $yearEnd = $orientacao_concluidas_echo["ano"]
                                                );
                                            }
                                            echo '</ul>';
                                        }
                                        unset($orientacao_concluidas_array);
                                    }
                                }
                            }
                            ?>

                        </div> <!-- end tab-four -->
                    </transition>
                    <transition name="tabeffect">
                        <div id="tab-five" class="c-tab-content" v-if="tabOpened == '5'">
                            <h3 class="t t-h3 u-mb-20">Gestão</h3>

                            <?php
                            foreach ($profile['atuacoes_profissionais'] as $key => $atuacoes_profissionais) {
                                foreach ($atuacoes_profissionais as $key => $atuacao_profissional) {
                                    if (isset($atuacao_profissional['ATIVIDADES-DE-DIRECAO-E-ADMINISTRACAO'])) {
                                        echo '<h4 class="t t-subtitle">' . $atuacao_profissional['@attributes']['NOME-INSTITUICAO'] . '</h4>';
                                        if (isset($atuacao_profissional['VINCULOS'])) {
                                            echo '<ul>';
                                            foreach ($atuacao_profissional['ATIVIDADES-DE-DIRECAO-E-ADMINISTRACAO']['DIRECAO-E-ADMINISTRACAO'] as $key => $direcao_e_administracao) {
                                                if (isset($direcao_e_administracao['@attributes']['CARGO-OU-FUNCAO'])) {
                                                    SList::genericItem(
                                                        $type = 'managing',
                                                        $itemName = $direcao_e_administracao['@attributes']['CARGO-OU-FUNCAO'],
                                                        $itemNameLink = '',
                                                        $itemInfoB = $direcao_e_administracao['@attributes']['NOME-ORGAO'],
                                                        $itemInfoC = $direcao_e_administracao['@attributes']['NOME-UNIDADE'],
                                                        $itemInfoD = '',
                                                        $itemInfoE = '',
                                                        $authors = '',
                                                        $tags = '',
                                                        $yearStart = $direcao_e_administracao['@attributes']['ANO-INICIO'],
                                                        $yearEnd = $direcao_e_administracao['@attributes']['ANO-FIM']
                                                    );
                                                } else {
                                                    SList::genericItem(
                                                        $type = 'managing',
                                                        $itemName = $direcao_e_administracao['CARGO-OU-FUNCAO'],
                                                        $itemNameLink = '',
                                                        $itemInfoB = $direcao_e_administracao['NOME-ORGAO'],
                                                        $itemInfoC = $direcao_e_administracao['NOME-UNIDADE'],
                                                        $itemInfoD = '',
                                                        $itemInfoE = '',
                                                        $authors = '',
                                                        $tags = '',
                                                        $yearStart = $direcao_e_administracao['ANO-INICIO'],
                                                        $yearEnd = $direcao_e_administracao['ANO-FIM']
                                                    );
                                                }
                                            }
                                            echo '</ul>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </div>
                    </transition>
                    <transition name="tabeffect">
                        <div id="tab-six" class="c-tab-content" v-if="tabOpened == '6'">
                            <h3 class="t t-h3 u-mb-20">Pesquisa</h3>

                            <?php
                            foreach ($profile['atuacoes_profissionais'] as $key => $atuacoes_profissionais) {
                                foreach ($atuacoes_profissionais as $key => $atuacao_profissional_1) {
                                    if (isset($atuacao_profissional_1['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'])) {
                                        echo '<h4 class="t t-subtitle u-my-20">' . $atuacao_profissional_1['@attributes']['NOME-INSTITUICAO'] . '</h4>';
                                        foreach ($atuacao_profissional_1['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'] as $key => $participacao_em_projeto) {
                                            if (isset($participacao_em_projeto['PROJETO-DE-PESQUISA'])) {
                                                foreach ($participacao_em_projeto['PROJETO-DE-PESQUISA'] as $key => $projeto_de_pesquisa) {
                                                    if (!empty($projeto_de_pesquisa['@attributes'])) {
                                                        foreach ($projeto_de_pesquisa['EQUIPE-DO-PROJETO']['INTEGRANTES-DO-PROJETO'] as $key => $integrante_do_projeto) {
                                                            if (isset($integrante_do_projeto['@attributes']['NOME-COMPLETO'])) {
                                                                $integrantes_do_projeto[] = $integrante_do_projeto['@attributes']['NOME-COMPLETO'];
                                                            }
                                                        }
                                                        echo '<ul>';
                                                        if (!isset($integrantes_do_projeto)) {
                                                            $integrantes_do_projeto = [];
                                                        }
                                                        SList::genericItem(
                                                            $type = 'research',
                                                            $itemName = $projeto_de_pesquisa['@attributes']['NOME-DO-PROJETO'],
                                                            $itemNameLink = '',
                                                            $itemInfoA = $projeto_de_pesquisa['@attributes']['DESCRICAO-DO-PROJETO'],
                                                            $itemInfoB = '',
                                                            $itemInfoC = '',
                                                            $itemInfoD = '',
                                                            $itemInfoE = '',
                                                            $authors = implode(', ', $integrantes_do_projeto),
                                                            $tags = '',
                                                            $yearStart = $projeto_de_pesquisa['@attributes']['ANO-INICIO'],
                                                            $yearEnd = $projeto_de_pesquisa['@attributes']['ANO-FIM']
                                                        );
                                                        echo '</ul>';
                                                        unset($integrantes_do_projeto);
                                                    } else {
                                                        if (isset($projeto_de_pesquisa['INTEGRANTES-DO-PROJETO'])) {
                                                            unset($integrantes_do_projeto);
                                                            if (isset($projeto_de_pesquisa['INTEGRANTES-DO-PROJETO']['@attributes'])) {
                                                                $integrantes_do_projeto[] = $projeto_de_pesquisa['INTEGRANTES-DO-PROJETO']['@attributes']['NOME-COMPLETO'];
                                                            } else {
                                                                foreach ($projeto_de_pesquisa['INTEGRANTES-DO-PROJETO'] as $key => $integrante_do_projeto) {
                                                                    $integrantes_do_projeto[] = $integrante_do_projeto['@attributes']['NOME-COMPLETO'];
                                                                }
                                                            }
                                                        }
                                                        if (isset($projeto_de_pesquisa['NOME-DO-PROJETO'])) {
                                                            (isset($integrantes_do_projeto)) ? $integrantesDoProjeto = implode(', ', $integrantes_do_projeto) : $integrantesDoProjeto = '';
                                                            echo '<ul>';
                                                            SList::genericItem(
                                                                $type = 'research',
                                                                $itemName = $projeto_de_pesquisa['NOME-DO-PROJETO'],
                                                                $itemNameLink = '',
                                                                $itemInfoA = $projeto_de_pesquisa['DESCRICAO-DO-PROJETO'],
                                                                $itemInfoB = '',
                                                                $itemInfoC = '',
                                                                $itemInfoD = '',
                                                                $itemInfoE = '',
                                                                $authors = $integrantesDoProjeto,
                                                                $tags = '',
                                                                $yearStart = $projeto_de_pesquisa['ANO-INICIO'],
                                                                $yearEnd = $projeto_de_pesquisa['ANO-FIM']
                                                            );
                                                            echo '</ul>';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            ?>

                            <h3 class="t t-h3 u-mb-20">Outras atividades técnico científicas</h3>

                        </div>
                    </transition>


                </div> <!-- end profile-inner -->
                <p class="t t-lastUpdate t-right">Atualização Lattes em
                    <?php echo $profile['data_atualizacao']; ?></p>
                <p class="t t-lastUpdate t-right">Processado em <?php echo $profile['dataDeColeta']; ?></p>
            </div>







            <a class="c-back-to-top" href="#top" title="Voltar ao topo">
                <div class="back-to"></div>
            </a>

        </div>

        </div> <!-- end profile-wrapper -->
    </main>


    <?php include('inc/footer.php'); ?>

    <script>
        var app = new Vue({
            el: '#profile',
            data: {
                tabOpened: '2',
                isActive: false

            },
            methods: {
                changeTab(tab) {
                    this.tabOpened = tab
                    var tabs = document.getElementsByClassName("c-profmenu-btn")

                    for (i = 0; i < tabs.length; i++)
                        tabs[i].className = tabs[i].className.replace("c-profmenu-active", "")

                    tabs[Number(tab) - 1].className += " c-profmenu-active"
                }
            },
            mounted: function() {
                this.changeTab(1)
            },
        })
    </script>

</body>

</html>