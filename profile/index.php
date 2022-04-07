<?php
// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';

function lattesID10($lattesID16)
{
    $url = 'http://lattes.cnpq.br/' . $lattesID16 . '';

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

    // $result_get['query']["sort"]["datePublished"]["missing"] = "_last";
    // $result_get['query']["sort"]["datePublished"]["order"] = "asc";
    // $result_get['query']["sort"]["datePublished"]["mode"] = "max";


    // $result_get['query']["sort"]["_uid"]["unmapped_type"] = "long";
    // $result_get['query']["sort"]["_uid"]["missing"] = "_last";
    // $result_get['query']["sort"]["_uid"]["order"] = "desc";
    // $result_get['query']["sort"]["_uid"]["mode"] = "max";

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
    $cursor_works = $client->search($params_works);


    $lattesID10 = lattesID10($_GET["lattesID"]);
} else {
    header("Location: https://unifesp.br/prodmais/index.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php
    include('inc/meta-header.php');
    ?>
    <title>Perfil: <?php echo $profile["nome_completo"] ?></title>
    <link rel="stylesheet" href="../inc/css/style.css" />

    <script src="../inc/js/vega.min.js"></script>


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
    <br /><br />

    <main role="main">
        <div class="container">

            <div class="row">
                <div class="col-12">
<!--
< ?php 
    echo "<pre>".print_r($profile,true)."</pre>";
?>
-->
                    <h1><?php echo $profile["nome_completo"] ?></h1>
                    <br />
                    <img class="rounded img-thumbnail" width="200px" height="200px" src="http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&amp;bcv=true&amp;id=<?php echo $lattesID10; ?>" style="margin-bottom: 10px;">

                    <a class="text-dark" href="http://lattes.cnpq.br/<?php echo $_GET["lattesID"]; ?>">
                        <img src="../inc/images/logo-lattes.png" width="25px" height="25px" alt="Acessar Currículo Lattes">
                    </a>
                    <?php 
                        foreach ($profile["campus"] as $campus) {
                            echo '<p>Campus: ' . $campus . '</p>';
                        } 
                    ?>
                    <?php 
                        foreach ($profile["ppg_nome"] as $nome_ppg) {
                            echo '<p>Programa de Pós-Graduação: ' . $nome_ppg . '</p>';
                        } 
                    ?>
                    <p>Nome em citações bibliográficas: <?php echo $profile["nome_em_citacoes_bibliograficas"] ?></p>
                    <p>ORCID ID: <a href="<?php echo $profile["orcid_id"] ?>" rel="nofollow" target="_blank"><?php echo $profile["orcid_id"] ?></a></p>
                    <p>Data da obtenção dos dados do Lattes: <?php echo $profile["dataDeColeta"] ?></p>
                    

                    <p><a href='../result.php?filter[]=vinculo.lattes_id:"<?php echo $profile['lattesID']; ?>"'>Ver produções indexadas</a></p>

                    <p><a href="../tools/export.php?search=&filter[]=vinculo.lattes_id:<?php echo $profile['lattesID']; ?>&format=bibtex" rel="nofollow">Exportar para ORCID (Formato BibTeX)</a></p>

                    <p><?php echo $profile["resumo_cv"]["texto_resumo_cv_rh"] ?></p>
                    <p>Quantidade de registros: <?php echo $totalWorks ?></p>
                    <?php //var_dump($profile); 
                    ?>
                    <?php //var_dump($cursor_works); 
                    ?>

                    <?php
                    $authorfacets = new AuthorFacets();
                    $authorfacets->query = $result_get['query'];

                    if (!isset($_GET)) {
                        $_GET = null;
                    }

                    $resultauthorfacet = $authorfacets->authorfacet(basename(__FILE__), "tipo", 100, "Tipo de material", null, "_term", $_GET);
                    $resultyearfacet = $authorfacets->authorfacet(basename(__FILE__), "datePublished", 120, "Ano de publicação", "desc", "_term", $_GET);
                    $resultaboutfacet = $authorfacets->authorfacet(basename(__FILE__), "about", 120, "Palavras-chave do autor", null, "_term", $_GET);
                    $resultcitedfacet = $authorfacets->authorfacet(basename(__FILE__), "counts_by_year", 120, "Cited", null, "_term", $_GET);

                    //var_dump($resultcitedfacet);


                    ?>

<?php 
    //var_dump($resultaboutfacet, true);
?>


                    <h2>Distribuição de trabalhos por tipo</h2>
                    <div class="embed">
                        <div id="bar-chart" class="view"></div>
                        <a href="./bar-chart.vg.json">View Source</a>
                        <a id="bar-chart-png" href="#">Export PNG</a>
                        <a id="bar-chart-svg" href="#">Export SVG</a>
                    </div>
                    <script>
                        var spec = {
                            "$schema": "https://vega.github.io/schema/vega/v5.json",
                            "width": 1200,
                            "height": 300,
                            "padding": 5,

                            "data": [{
                                "name": "table",
                                "values": <?= $resultauthorfacet ?>
                            }],

                            "signals": [{
                                "name": "tooltip",
                                "value": {},
                                "on": [{
                                        "events": "rect:mouseover",
                                        "update": "datum"
                                    },
                                    {
                                        "events": "rect:mouseout",
                                        "update": "{}"
                                    }
                                ]
                            }],

                            "scales": [{
                                    "name": "xscale",
                                    "type": "band",
                                    "domain": {
                                        "data": "table",
                                        "field": "category"
                                    },
                                    "range": "width",
                                    "padding": 0.05,
                                    "round": true
                                },
                                {
                                    "name": "yscale",
                                    "domain": {
                                        "data": "table",
                                        "field": "amount"
                                    },
                                    "nice": true,
                                    "range": "height"
                                }
                            ],

                            "axes": [{
                                    "orient": "bottom",
                                    "scale": "xscale"
                                },
                                {
                                    "orient": "left",
                                    "scale": "yscale"
                                }
                            ],

                            "marks": [{
                                    "type": "rect",
                                    "from": {
                                        "data": "table"
                                    },
                                    "encode": {
                                        "enter": {
                                            "x": {
                                                "scale": "xscale",
                                                "field": "category"
                                            },
                                            "width": {
                                                "scale": "xscale",
                                                "band": 1
                                            },
                                            "y": {
                                                "scale": "yscale",
                                                "field": "amount"
                                            },
                                            "y2": {
                                                "scale": "yscale",
                                                "value": 0
                                            }
                                        },
                                        "update": {
                                            "fill": {
                                                "value": "steelblue"
                                            }
                                        },
                                        "hover": {
                                            "fill": {
                                                "value": "red"
                                            }
                                        }
                                    }
                                },
                                {
                                    "type": "text",
                                    "encode": {
                                        "enter": {
                                            "align": {
                                                "value": "center"
                                            },
                                            "baseline": {
                                                "value": "bottom"
                                            },
                                            "fill": {
                                                "value": "#333"
                                            }
                                        },
                                        "update": {
                                            "x": {
                                                "scale": "xscale",
                                                "signal": "tooltip.category",
                                                "band": 0.5
                                            },
                                            "y": {
                                                "scale": "yscale",
                                                "signal": "tooltip.amount",
                                                "offset": -2
                                            },
                                            "text": {
                                                "signal": "tooltip.amount"
                                            },
                                            "fillOpacity": [{
                                                    "test": "isNaN(tooltip.amount)",
                                                    "value": 0
                                                },
                                                {
                                                    "value": 1
                                                }
                                            ]
                                        }
                                    }
                                }
                            ]
                        };

                        function image(view, type) {
                            return function(event) {
                                event.preventDefault();
                                view.toImageURL(type).then(function(url) {
                                    var link = document.createElement(' a');
                                    link.setAttribute('href', url);
                                    link.setAttribute('target', '_blank');
                                    link.setAttribute('download', 'bar-chart.' + type);
                                    link.dispatchEvent(new MouseEvent('click'));
                                }).catch(function(error) {
                                    console.error(error);
                                });
                            };
                        }
                        var view = new vega.View(vega.parse(spec), {
                            loader: vega.loader({
                                baseURL: '/vega/'
                            }),
                            logLevel: vega.Warn,
                            renderer: 'svg'
                        }).initialize('#bar-chart').hover().run();
                        document.querySelector('#bar-chart-png').addEventListener('click', image(view, 'png'));
                        document.querySelector('#bar-chart-svg').addEventListener('click', image(view, 'svg'));
                    </script>
                    <br /><br />
                    <h2>Distribuição de trabalhos por ano de publicação</h2>
                    <div class="embed">
                        <div id="bar-chart2" class="view"></div>
                        <a href="./bar-chart2.vg.json">View Source</a>
                        <a id="bar-chart2-png" href="#">Export PNG</a>
                        <a id="bar-chart2-svg" href="#">Export SVG</a>
                    </div>
                    <script>
                        var spec = {
                            "$schema": "https://vega.github.io/schema/vega/v5.json",
                            "width": 1200,
                            "height": 300,
                            "padding": 5,

                            "data": [{
                                "name": "table",
                                "values": <?= $resultyearfacet ?>
                            }],

                            "signals": [{
                                "name": "tooltip",
                                "value": {},
                                "on": [{
                                        "events": "rect:mouseover",
                                        "update": "datum"
                                    },
                                    {
                                        "events": "rect:mouseout",
                                        "update": "{}"
                                    }
                                ]
                            }],

                            "scales": [{
                                    "name": "xscale",
                                    "type": "band",
                                    "domain": {
                                        "data": "table",
                                        "field": "category"
                                    },
                                    "range": "width",
                                    "padding": 0.05,
                                    "round": true
                                },
                                {
                                    "name": "yscale",
                                    "domain": {
                                        "data": "table",
                                        "field": "amount"
                                    },
                                    "nice": true,
                                    "range": "height"
                                }
                            ],

                            "axes": [{
                                    "orient": "bottom",
                                    "scale": "xscale"
                                },
                                {
                                    "orient": "left",
                                    "scale": "yscale"
                                }
                            ],

                            "marks": [{
                                    "type": "rect",
                                    "from": {
                                        "data": "table"
                                    },
                                    "encode": {
                                        "enter": {
                                            "x": {
                                                "scale": "xscale",
                                                "field": "category"
                                            },
                                            "width": {
                                                "scale": "xscale",
                                                "band": 1
                                            },
                                            "y": {
                                                "scale": "yscale",
                                                "field": "amount"
                                            },
                                            "y2": {
                                                "scale": "yscale",
                                                "value": 0
                                            }
                                        },
                                        "update": {
                                            "fill": {
                                                "value": "steelblue"
                                            }
                                        },
                                        "hover": {
                                            "fill": {
                                                "value": "red"
                                            }
                                        }
                                    }
                                },
                                {
                                    "type": "text",
                                    "encode": {
                                        "enter": {
                                            "align": {
                                                "value": "center"
                                            },
                                            "baseline": {
                                                "value": "bottom"
                                            },
                                            "fill": {
                                                "value": "#333"
                                            }
                                        },
                                        "update": {
                                            "x": {
                                                "scale": "xscale",
                                                "signal": "tooltip.category",
                                                "band": 0.5
                                            },
                                            "y": {
                                                "scale": "yscale",
                                                "signal": "tooltip.amount",
                                                "offset": -2
                                            },
                                            "text": {
                                                "signal": "tooltip.amount"
                                            },
                                            "fillOpacity": [{
                                                    "test": "isNaN(tooltip.amount)",
                                                    "value": 0
                                                },
                                                {
                                                    "value": 1
                                                }
                                            ]
                                        }
                                    }
                                }
                            ]
                        };

                        function image(view, type) {
                            return function(event) {
                                event.preventDefault();
                                view.toImageURL(type).then(function(url) {
                                    var link = document.createElement(' a');
                                    link.setAttribute('href', url);
                                    link.setAttribute('target', '_blank');
                                    link.setAttribute('download', 'bar-chart2.' + type);
                                    link.dispatchEvent(new MouseEvent('click'));
                                }).catch(function(error) {
                                    console.error(error);
                                });
                            };
                        }
                        var view = new vega.View(vega.parse(spec), {
                            loader: vega.loader({
                                baseURL: '/vega/'
                            }),
                            logLevel: vega.Warn,
                            renderer: 'svg'
                        }).initialize('#bar-chart2').hover().run();
                        document.querySelector('#bar-chart2-png').addEventListener('click', image(view, 'png'));
                        document.querySelector('#bar-chart2-svg').addEventListener('click', image(view, 'svg'));
                    </script>
                    <br /><br />
                    <h2>Relação de trabalhos</h2>
                    <?php
                    foreach ($cursor_works["hits"]["hits"] as $works) {
                        //echo "<br /><br />";
                        //var_dump($works);
                        echo '
                            <div class="card">
                                <h5 class="card-header">' . $works["_source"]["tipo"] . '</h5>
                                <div class="card-body">
                                    <h5 class="card-title">' . $works["_source"]["name"] . ' (' . $works["_source"]["datePublished"] . ')</h5>
                            ';
                        if (isset($works["_source"]["EducationEvent"])) {
                            echo '<p style="margin-bottom:0px;">Nome do evento: ' . $works["_source"]["EducationEvent"]["name"] . '</p>';
                        }
                        echo '<p style="margin-bottom:0px;">País de publicação: ' . $works["_source"]["country"] . '</p>';
                        foreach ($works["_source"]["author"] as $author) {
                            $authors[] = $author["person"]["name"];
                        };
                        echo '<p style="margin-bottom:0px;">Autoria: ' . implode('; ', $authors) . '</p>';
                        echo '
                                </div>
                            </div>
                            ';
                        echo "<br />";
                        unset($authors);
                    }
                    ?>
                </div>
            </div>

        </div>
    </main>
</body>