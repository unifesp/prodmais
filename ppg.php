<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php

require 'inc/config.php';

if (!empty($_REQUEST["ID"])) {

    $params = [];
    $params["index"] = $index_ppg;
    $params["id"] = $_REQUEST["ID"];
    $cursor = $client->get($params);
    $ppg = $cursor["_source"];

    echo "<br/><br/><br/><br/><pre>" . print_r($ppg, true) . "</pre>";
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
    require 'inc/config.php';
    require 'inc/meta-header.php';
    require 'inc/functions.php';
    require 'inc/components/GraphBar.php';
    require 'inc/components/SList.php';
    require 'inc/components/Who.php';
    require 'inc/components/PPGBadges.php';
    require 'inc/components/TagCloud.php';
    require '_fakedata.php';
    ?>
    <meta charset="utf-8" />
    <title><?php echo $branch; ?> - PPG <?php echo $ppg["NOME_PPG"]; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Prodmais Unifesp." />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />

</head>

<body data-theme="<?php echo $theme; ?>" class="c-wrapper-body">
    <?php if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    } ?>

    <?php require 'inc/navbar.php'; ?>
    <main class="c-wrapper-container">
        <div class="c-wrapper-paper">

            <div class="c-wrapper-inner">

                <section class="p-ppg__header">
                    <div class="p-ppg__header-1">
                        <i class="i i-ppg-logo p-ppg__logo"></i>
                    </div>

                    <div class="p-ppg__header-2">
                        <h1 class="t t-h1">PPG <?php echo $ppg["NOME_PPG"]; ?></h1>
                        <h2 class="t t-h2">Programa de Pós Graduação em <?php echo $ppg["NOME_PPG"]; ?></h2>
                        <p class="t t-b ty-light-a">
                            <span>Campus <?php echo $ppg["NOME_CAMPUS"]; ?></span><br />
                            <span><?php echo $ppg["NOME_CAMARA"]; ?></span>
                        </p>
                        <!--
            <div class="d-icon-text t-gray u-mb-10">
              <i class="i i-sm i-mapmarker p-ppg__i"></i>
              <b>Estrada do Caminho Velho nª 123 - Bairro, Cidade - SP</b>
            </div>
            -->

                        <div class="d-icon-text">
                            <i class='i i-people-manager'></i>
                            <span class="t t-gray t-b">Coordenação: <?php echo $ppg["NOME_COORDENADOR"]; ?></span>
                        </div>

                        <!--
            <p class="t t-gray t-b">Secretaria:</p>
            <p class="t t-gray">Maria Oliveira</p>
            <p class="t t-gray">Olívia Maria</p>
            -->


                        <div class="d-icon-text t-gray">
                            <i class="i i-sm i-mail p-ppg__i"></i> <?php echo $ppg["PPG_EMAIL"]; ?>
                        </div>


                        <!--
            <div class="d-icon-text t-gray">
              <i class="i i-sm i-phone p-ppg__i"></i> (11) 5555-5555
            </div>
            -->

                        <a href="<?php echo $ppg["PPG_SITE"]; ?>" target="blank">
                            <div class="d-icon-text t-gray">
                                <i class="i i-sm i-web p-ppg__i"></i> <?php echo $ppg["PPG_SITE"]; ?>
                            </div>
                        </a>
                    </div>

                    <div class="p-ppg__badges">
                        <div class="dh d-hc">
                            <?php echo PPGBadges::students(
                                $rate = 20,
                                $title = 'Em Curso',
                                $ico = 'student2'
                            ); ?>

                            <?php echo PPGBadges::students(
                                $rate = 100,
                                $title = 'Formados',
                                $ico = 'formado'
                            ); ?>
                        </div>
                        <div class="p-ppg__badges-capes">
                            <?php echo PPGBadges::capes(
                                $rate = 4,
                                $title = 'Mestrado acadêmico'
                            ); ?>

                            <?php echo PPGBadges::capes(
                                $rate = 6,
                                $title = 'Doutorado acadêmico'
                            ); ?>

                            <?php echo PPGBadges::capes(
                                $rate = 6,
                                $title = 'Outro '
                            ); ?>
                        </div>
                    </div>
                </section>


                <hr class="c-line u-my-20" />


                <section class="l-ppg">
                    <h3 class="t t-h3">Palavras chave recorrentes</h3>

                    <div>
                        <?php Tag::cloud($categorysFake, $hasLink = false); ?>

                    </div> <!-- end -->


                </section>

                <hr class="c-line u-my-20" />

                <section class="l-ppg">
                    <?php
                    $legends = array(
                        "Artigos publicados",
                        "Textos em jornais e revistas",
                        "Participação em eventos",
                        "Outras produções"
                    );

                    GraphBar::graph(
                        $title = 'Produção nos últimos anos',
                        $arrData = $infosToGraph,
                        $arrLegends = $legends,
                        $lines = 30
                    );
                    ?>
                </section>


                <section class="l-ppg u-my-3">
                    <?php
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

                <hr class="c-line u-my-20" />

                <section class="l-ppg">
                    <h3 class='t t-h3'>Orientadores e orientadoras</h3>

                    <ul class="p-ppg__orientadores">

                        <li>
                            <?php
                            Who::ppg(
                                $picture = "inc/images/tmp/profile.jpg",
                                $name = 'Sebastião',
                                $title = 'Linha de pesquisa',
                                $link = 'https://unifesp.br/prodmais/index.php'
                            )
                            ?>
                        </li>
                        <li>
                            <?php
                            Who::ppg(
                                $picture = "inc/images/tmp/profile.jpg",
                                $name = 'Sócrates',
                                $title = 'Linha de pesquisa',
                                $link = 'https://unifesp.br/prodmais/index.php'
                            )
                            ?>
                        </li>
                        <li>
                            <?php
                            Who::ppg(
                                $picture = "inc/images/tmp/profile.jpg",
                                $name = 'Sêneca',
                                $title = 'Linha de pesquisa',
                                $link = 'https://unifesp.br/prodmais/index.php'
                            )
                            ?>
                        </li>
                        <li>
                            <?php
                            Who::ppg(
                                $picture = "inc/images/tmp/profile.jpg",
                                $name = 'Salomão',
                                $title = 'Linha de pesquisa',
                                $link = 'https://unifesp.br/prodmais/index.php'
                            )
                            ?>
                        </li>
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
                    <?php echo PPG::externos(
                        $type = 'Repositório de dados de pesquisa',
                        $link = $ppg["PRODMAIS_DATAVERSE"]
                    ); ?>

                    <?php echo PPG::externos(
                        $type = 'Repositório institucional',
                        $link = $ppg["PRODMAIS_DSPACE"]
                    ); ?>
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

                <p class="t t-lastUpdate t-right">Atualização Lattes em </p>
                <p class="t t-lastUpdate t-right">Processado em </p>

            </div> <!-- c-wrapper-inner -->
        </div> <!-- c-wrapper-paper -->
    </main> <!-- c-wrapper-container -->


    </div> <!-- end result-container -->

    <?php include('inc/footer.php'); ?>
    <script>

    </script>
</body>


</html>