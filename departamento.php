<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php
class Departamento {
  static function repos($type, $link) {
    $ico = '';
    $text = '';

    switch ($type) {
      case 'Sucupira':
        $ico = 'sucupira.png';
        $text = '';
        break;
      case 'Repositório de dados de esquisa':
        $ico = 'dataverse.png';
        $text = 'Repositório de dados de esquisa';
        break;
      case 'Repositório institucional':
        $ico = 'DSpace.svg';
        $text = 'Repositório institucional';
        break;
    }

    echo 
    "<a class='p-departamento-externos' href='$link' target='blank'>
      <img class='p-departamento-plataforms' src='inc/images/logos/$ico' title='$text'/>
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
    <title><?php echo $branch; ?> - Departamento Letras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Departamento" />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />


</head>

<body data-theme="<?php echo $theme; ?>" class="c-wrapper-body">
    <?php if(file_exists('inc/google_analytics.php')){include 'inc/google_analytics.php';}?>

    <?php require 'inc/navbar.php'; ?>
    <main class="c-wrapper-container">
        <div class="c-wrapper-paper">

            <div class="c-wrapper-inner">

                <section class="p-departamento-header">
                    <div class="p-departamento-header-one">
                        <i class="i i-building p-departamento-logo"></i>
                    </div>

                    <div class="p-departamento-header-two">
                        <h1 class="t t-h1">Departamento X</h1>
                        <p class="t t-b ty-light-a">
                            <span>Campus Guarulhos</span>
                            <span>Escola de Filosofia, Letras e ciências Humanas</span>
                        </p>
                        <div class="d-icon-text t-gray u-mb-10">
                            <i class="i i-sm i-mapmarker p-departamento-i"></i>
                            <b>Estrada do Caminho Velho nª 123 - Bairro, Cidade - SP</b>
                        </div>

                    </diV>
                    <div class="p-departamento-header-three">
                        <div class="p-departamento-header-three-inner">
                            <?php
              Who::mini(
                $picture = "inc/images/tmp/profile.jpg",
                $name = 'Sócrates',
                $title = 'Coordenador',
                $link = 'https://unifesp.br/prodmais/index.php'
                )
                ?>
                        </div>
                        <div class="p-departamento-header-three-inner">
                            <p class="t t-gray t-b">Secretaria:</p>
                            <p class="t t-gray">Maria Oliveira</p>
                            <p class="t t-gray">Olívia Maria</p>
                        </div>

                        <div class="p-departamento-header-three-inner">
                            <a href="" target="blank">
                                <div class="d-icon-text t-gray">
                                    <i class="i i-sm i-mail p-departamento-i"></i> email@email.com
                                </div>
                            </a>

                            <div class="d-icon-text t-gray">
                                <i class="i i-sm i-phone p-departamento-i"></i> (11) 5555-5555
                            </div>

                            <a href="" target="blank">
                                <div class="d-icon-text t-gray">
                                    <i class="i i-sm i-web p-departamento-i"></i> site
                                </div>
                            </a>
                        </div>


                    </div>

                </section>


                <hr class="c-line u-my-20" />


                <section class="l-ppg">
                    <h3 class="t t-h3">Palavras chave recorrentes</h3>

                    <?php Tag::cloud( $categorysFake, $hasLink = false ); ?>

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

                <hr class="c-line u-my-20" />

                <section class="l-ppg">
                    <h3 class='t t-h3'>Orientadores e orientadoras</h3>

                    <ul class="p-departamento-orientadores">

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


                <section class="p-departamento-repos">
                    <?php echo Departamento::repos(
            $type = 'Sucupira',
            $link = 'https://repositorio.unifesp.br/handle/11600/6108'
            ); ?>
                    <?php echo Departamento::repos(
            $type = 'Repositório de dados de esquisa',
            $link = 'https://repositoriodedados.unifesp.br/dataverse/eflch'
            ); ?>

                    <?php echo Departamento::repos(
            $type = 'Repositório institucional',
            $link = 'https://sucupira.capes.gov.br/sucupira/public/consultas/coleta/programa/viewPrograma.xhtml;jsessionid=OLRUfmVYapfO6QJKy+Wf0KS1.sucupira-218?popup=true&cd_programa=33009015089P5'
            ); ?>
                </section>


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