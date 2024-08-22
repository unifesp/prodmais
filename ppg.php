<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php

require 'inc/config.php';
require 'inc/functions.php';


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

if (!empty($_REQUEST["ID"])) {

    $params = [];
    $params["index"] = $index_ppg;
    $params["id"] = $_REQUEST["ID"];
    $cursor = $client->get($params);
    $ppg = $cursor["_source"];


    // Get infos to graph

    $params = [];
    $params["index"] = $index;
    $query["query"]["bool"]["filter"][0]["term"]["vinculo.ppg_nome.keyword"] = trim($ppg['NOME_PPG']);
    $params["body"] = $query;
    $cursorTotal = $client->count($params);
    $totalProducoes = $cursorTotal["count"];

    //echo "<br/><br/><br/><br/><pre>" . print_r($totalProducoes, true) . "</pre>";

    $ppgtags = new DataFacets();
    $resultppgtags = json_decode($ppgtags->PPGTags(trim($ppg['NOME_PPG'])), true);
    if (!is_null($resultppgtags)) {
        shuffle($resultppgtags);
    }

    // Quantidade de obras por ano e por tipo
    $facets = new Facets();
    $producoes_ano = $facets->dataFacetbyYear("tipo", $query, 5, $ppg);

    $infosToGraph = [];
    $arrLegends_duplicated = [];
    foreach ($producoes_ano["by_year"]["buckets"] as $producoes) {
        $infosToGraph[$producoes['key']['year']]['year'] = $producoes['key']['year'];
        $infosToGraph[$producoes['key']['year']][$producoes['key']['type']] = $producoes['doc_count'];
    }

    // Orientadores
    $query_orientadores["query"]["bool"]["filter"]["term"]["ppg_nome.keyword"] = trim($ppg['NOME_PPG']);
    $query_orientadores["sort"] = ["nome_completo.keyword" => ["order" => "asc"]];
    $params_orientadores = [];
    $params_orientadores["index"] = $index_cv;
    $params_orientadores["_source"] = ["nome_completo"];
    $params_orientadores["size"] = 500;
    $params_orientadores["body"] = $query_orientadores;
    $cursor_orientadores = $client->search($params_orientadores);
} else {
    echo '<script>window.location.href = "index.php";</script>';
    die();
}


class PPG
{
    static function externos($type, $link)
    {
        $ico = '';
        $text = '';

        switch ($type) {
            case 'Sucupira':
                $ico = 'sucupira.png';
                $text = '';
                break;
            case 'Repositório de dados de pesquisa':
                $ico = 'dataverse.png';
                $text = 'Repositório de dados de pesquisa';
                break;
            case 'Repositório institucional':
                $ico = 'DSpace.svg';
                $text = 'Repositório institucional';
                break;
        }

        echo
        "<a class='p-ppg__externos' href='$link' target='blank'>
            <img class='p-ppg__plataforms' src='inc/images/logos/$ico' title='$text'/>
            <p class='t t-light'><b>$text</b></p>
        </a>";
    }
}
?>

<head>
    <?php
    require 'inc/meta-header.php';
    require 'inc/components/SList.php';
    require 'inc/components/Who.php';
    require 'inc/components/PPGBadges.php';
    require 'inc/components/TagCloud.php';
    ?>
    <meta charset="utf-8" />
    <title><?php echo $branch; ?> - PPG <?php echo $ppg["NOME_PPG"]; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Prodmais" />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
    .bar {
        stroke: #000;
    }

    .legend {
        font-size: 12px;
    }

    body {
        margin: 0;
    }

    svg {
        display: block;
        width: 100%;
    }
    </style>

</head>

<body data-theme="<?php echo $theme; ?>" class="c-wrapper-body">
    <?php
    if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    } elseif (file_exists('../inc/google_analytics.php')) {
        include '../inc/google_analytics.php';
    }
    ?>

    <?php require 'inc/navbar.php'; ?>
    <main class="c-wrapper-container">
        <div class="c-wrapper-paper">

            <div class="c-wrapper-inner">

                <section class="p-ppg__header">
                    <div class="p-ppg__header-1">
                        <i class="i i-ppg-logo p-ppg__logo"></i>
                    </div>

                    <div class="">
                        <h1 class="t t-h1">Programa de Pós Graduação: <?php echo $ppg["NOME_PPG"]; ?></h1>
                        <p class="t t-b ty-light-a">
                            <span><?php echo $ppg["NOME_CAMPUS"]; ?></span><br />
                            <span><?php echo (isset($ppg["NOME_CAMARA"])) ? $ppg["NOME_CAMARA"] : ''; ?></span>
                        </p>

                        <div class="d-icon-text">
                            <i class='i i-people-manager'></i>
                            <span class="t t-gray t-b">Coordenação: <?php echo $ppg["NOME_COORDENADOR"]; ?></span>
                        </div>
                        <br />

                        <div class="d-icon-text t-gray">
                            <i class="i i-sm i-mail p-ppg__i"></i> <?php echo $ppg["PPG_EMAIL"]; ?>
                        </div>

                        <a href="<?php echo $ppg["PPG_SITE"]; ?>" target="blank">
                            <div class="d-icon-text t-gray">
                                <i class="i i-sm i-web p-ppg__i"></i> <?php echo $ppg["PPG_SITE"]; ?>
                            </div>
                        </a>
                    </div>

                    <div class="p-ppg__badges">
                        <!--
                        <div class="dh d-hc">
                            < ?php echo PPGBadges::students(
                                $rate = 20,
                                $title = 'Em Curso',
                                $ico = 'student2'
                            ); ?>

                            < ?php echo PPGBadges::students(
                                $rate = 100,
                                $title = 'Formados',
                                $ico = 'formado'
                            ); ?>
                        </div>
                        -->
                        <div class="p-ppg__badges-capes">
                            <?php echo PPGBadges::capes(
                                $rate = $ppg["CONCEITO_CAPES"],
                                $title = $ppg["NIVEL"]
                            ); ?>
                        </div>
                    </div>
                </section>


                <hr class="c-line u-my-20" />


                <section class="l-ppg">
                    <h3 class="t t-h3">Palavras chave recorrentes</h3>
                    <div>
                        <?php Tag::cloud($resultppgtags, $ppg["NOME_PPG"], null); ?>

                    </div>
                </section>

                <hr class="c-line u-my-20" />

                <p>
                    Total de produções registradas no Prodmais por pesquisadores vinculados ao PPG:
                    <?php echo $totalProducoes; ?>
                </p>

                <section class="l-ppg">

                    <svg width="100%" height="500"></svg>


                    <script>
                    // Dados em PHP
                    <?php

                        echo "const data = " . json_encode($infosToGraph) . ";";
                        ?>

                    // Transformar os dados em um array de objetos
                    const formattedData = Object.keys(data).map(year => {
                        return {
                            year: parseInt(year),
                            ...data[year]
                        };
                    });

                    const keys = ["Artigo publicado", "Capítulo de livro publicado", "Livro publicado ou organizado",
                        "Patente", "Software", "Textos em jornais de notícias/revistas", "Trabalhos em eventos",
                        "Tradução"
                    ];

                    const margin = {
                            top: 20,
                            right: 300,
                            bottom: 40,
                            left: 100
                        },
                        width = window.innerWidth - margin.left - margin.right,
                        height = 600 - margin.top - margin.bottom;

                    console.log(window.innerWidth);
                    const svg = d3.select("svg")
                        .attr('width', window.innerWidth - margin.left - margin.right)
                        .attr('height', 600)
                        .append("g")
                        .attr("transform", `translate(${margin.left},${margin.top})`);

                    const x = d3.scaleBand()
                        .domain(formattedData.map(d => d.year))
                        .range([0, width])
                        .padding(0.1);

                    const y = d3.scaleLinear()
                        .domain([0, d3.max(formattedData, d => d3.sum(keys, key => d[key]))])
                        .nice()
                        .range([height, 0]);

                    const color = d3.scaleOrdinal()
                        .domain(keys)
                        .range(d3.schemeCategory10);

                    svg.append("g")
                        .selectAll("g")
                        .data(d3.stack().keys(keys)(formattedData))
                        .join("g")
                        .attr("fill", d => color(d.key))
                        .selectAll("rect")
                        .data(d => d)
                        .join("rect")
                        .attr("x", d => x(d.data.year))
                        .attr("y", d => y(d[1]))
                        .attr("height", d => y(d[0]) - y(d[1]))
                        .attr("width", x.bandwidth());

                    svg.append("g")
                        .attr("class", "x-axis")
                        .attr("transform", `translate(0,${height})`)
                        .call(d3.axisBottom(x));

                    svg.append("g")
                        .attr("class", "y-axis")
                        .call(d3.axisLeft(y));

                    // Adicionar legenda
                    const legend = svg.append("g")
                        .attr("class", "legend")
                        .attr("transform", `translate(${width - 100}, 20)`); // Ajuste a posição conforme necessário

                    keys.forEach((key, i) => {
                        const legendRow = legend.append("g")
                            .attr("transform", `translate(0, ${i * 20})`);

                        legendRow.append("rect")
                            .attr("width", 10)
                            .attr("height", 10)
                            .attr("fill", color(key));

                        legendRow.append("text")
                            .attr("x", 20)
                            .attr("y", 10)
                            .attr("text-anchor", "start")
                            .text(key);
                    });
                    </script>
                </section>

                <!--
                <section class="l-ppg u-my-3">
                    < ?php
                    $legends2 = array(
                        "Mestrado profissional",
                        "Mestrado acadêmico",
                        "Doutorado acadêmico"
                    );

                    GraphBar::graph3(
                        $title = 'Orientações',
                        $arrData = $infosToGraph2,
                        $arrLegends = $legends2,
                        $lines = 10
                    );
                    ?>
                </section>
                -->

                <hr class="c-line u-my-20" />

                <section class="l-ppg">
                    <h3 class='t t-h3'>Orientadores e orientadoras</h3>

                    <ul class="p-ppg__orientadores">
                        <?php foreach ($cursor_orientadores["hits"]["hits"] as $key => $value) { ?>
                        <li>
                            <?php
                                $id = $value["_id"];
                                $lattesID10 = lattesID10($value["_id"]);

                                // URL da imagem
                                $imageUrl = "https://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&bcv=true&id=$lattesID10";

                                // Diretório onde a imagem será salva
                                $saveDir = 'data/images/';
                                $imageName = 'foto_' . $lattesID10 . '.jpg';
                                $imagePath = $saveDir . $imageName;

                                // Crie o diretório se não existir
                                if (!file_exists($saveDir)) {
                                    mkdir($saveDir, 0777, true);
                                }

                                // Baixe a imagem e salve no diretório
                                $imageContent = file_get_contents($imageUrl);
                                if ($imageContent !== false) {
                                    file_put_contents($imagePath, $imageContent);
                                }

                                // Verifique se a imagem existe no diretório
                                if (file_exists($imagePath)) {
                                    echo "entrou aqui";
                                    Who::ppg(
                                        $picture = $imagePath,
                                        $name = $value["_source"]["nome_completo"],
                                        $title = "",
                                        $link = "profile.php?lattesID=$id"
                                    );
                                } else {
                                    echo "entrou no else";
                                    Who::ppg(
                                        $picture = "https://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&amp;bcv=true&amp;id=$lattesID10",
                                        $name = $value["_source"]["nome_completo"],
                                        $title = "",
                                        $link = "profile.php?lattesID=$id"
                                    );
                                }

                                ?>
                        </li>
                        <?php } ?>
                    </ul>

                </section>

                <hr class="c-line u-my-20" />

                <section>
                    <div class="dv d-vc">
                        <p class="t t-gray u-my-10"><b>Código CAPES</b></p>
                        <p class="t t-gray u-mb-10"><?php echo $ppg["COD_CAPES"]; ?></p>
                    </div>
                </section>

                <section class="dv d-vc d-md-h d-md-hc">
                    <?php echo PPG::externos(
                        $type = 'Sucupira',
                        $link = 'https://sucupira.capes.gov.br/sucupira/public/consultas/coleta/programa/viewPrograma.xhtml?popup=false&cd_programa=' . $ppg["COD_CAPES"]
                    ); ?>
                    <?php
                    if ($instituicao == "UNIFESP") {
                        echo PPG::externos(
                            $type = 'Repositório de dados de pesquisa',
                            $link = $ppg["PRODMAIS_DATAVERSE"]
                        );
                    } ?>

                    <?php
                    if ($instituicao == "UNIFESP") {
                        echo PPG::externos(
                            $type = 'Repositório institucional',
                            $link = $ppg["PRODMAIS_DSPACE"]
                        );
                    } ?>
                </section>



                <!-- <table>
            <thead>
              <tr class="thead">
                <th>Avaliação</th>
                <th>MA</th>
                <th>DO</th>
                <th>MP</th>
                <th>Portaria/Parecer</th>
                <th>Data D.O.U.</th>
                <th>Seção D.O.U.</th>
                <th>Página D.O.U.</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>Quadrienal 2013/2014/2015/2016</th>
                <th>4</th>
                <th>3</th>
                <th>5</th>
                <th>Portaria MEC 609, de 14/03/2019</th>
                <th>18/03/2019</th>
                <th>1</th>
                <th>78</th>
              </tr>
            </tbody>
          </table> -->



            </div> <!-- c-wrapper-inner -->
        </div> <!-- c-wrapper-paper -->
    </main> <!-- c-wrapper-container -->


    </div> <!-- end result-container -->

    <?php //echo "<pre>" . print_r($ppg, true) . "</pre>"; 
    ?>

    <?php include('inc/footer.php'); ?>
    <script>

    </script>
</body>


</html>