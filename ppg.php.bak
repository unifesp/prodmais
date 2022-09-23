<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php
class PPG {
  static function externos($type, $link) {
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
    "<a class='p-ppg-externos' href='$link' target='blank'>
      <img class='p-ppg-plataforms' src='inc/images/logos/$ico' title='$text'/>
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
  require 'components/GraphBar.php';
  require 'components/Production.php';
  require 'components/Who.php';
  require 'components/PPGBadges.php';
  require 'components/TagCloud.php';
  require '_fakedata.php';
  ?>
  <meta charset="utf-8" />
  <title><?php echo $branch; ?> - PPG Letras</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <meta name="description" content="Prodmais Unifesp." />
  <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />
  <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
  <link rel="stylesheet" href="sass/main.css" />
</head>

<body class="c-wrapper-body">
  <?php if(file_exists('inc/google_analytics.php')){include 'inc/google_analytics.php';}?>

  <?php require 'inc/navbar.php'; ?>
  <main class="c-wrapper-container">
    <div class="c-wrapper-paper">

      <div class="c-wrapper-inner">

        <section class="p-ppg-header">
          <div class="p-ppg-header-one">
            <i class="i i-ppg-logo p-ppg-logo"></i>
          </div>

          <div class="p-ppg-header-two">
            <h1 class="t t-h1">PPG Letras</h1>
            <h2 class="t t-h2">Programa de Pós Graduação em Letras</h2>
            <p class="t t-b ty-light-a">
              <span>Campus Guarulhos</span>
              <span>Escola de Filosofia, Letras e ciências Humanas</span>
            </p>
            <div class="d-icon-text t-gray u-mb-1">
              <i class="i i-mapmarker p-ppg-i"></i>
              <b>Estrada do Caminho Velho nª 123 - Bairro, Cidade - SP</b>
            </div>

            <div class="d-icon-text">
              <i class='i i-icons i-people-manager'></i>
              <span class="t t-gray t-b">Coordenação: Nome do coordenador/coordenadora</span>
            </div>

          </diV>
          <div class="p-ppg-header-three">

            <p class="t t-gray t-b">Secretaria:</p>
            <p class="t t-gray">Maria Oliveira</p>
            <p class="t t-gray">Olívia Maria</p>

            <a href="" target="blank">
              <div class="d-icon-text t-gray">
                <i class="i i-mail p-ppg-i"></i> email@email.com
              </div>
            </a>

            <div class="d-icon-text t-gray">
              <i class="i i-phone p-ppg-i"></i> (11) 5555-5555
            </div>

            <a href="" target="blank">
              <div class="d-icon-text t-gray">
                <i class="i i-web p-ppg-i"></i> site
              </div>
            </a>


          </div>

          <div class="p-ppg-header-four">
            <div class="d-h d-hc">
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
            <div class="p-ppg-badges">
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


        <hr class="c-line u-my-2" />


        <section class="l-ppg">
          <h3 class="t t-title">Palavras chave recorrentes</h3>

          <div>
            <?php Tag::cloud( $categorysFake, $hasLink = false ); ?>

          </div> <!-- end -->


        </section>

        <hr class="c-line u-my-2" />

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

        <hr class="c-line u-my-2" />

        <section class="l-ppg">
          <h3 class='t t-title'>Orientadores e orientadoras</h3>

          <ul class="p-ppg-orientadores">

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

        <hr class="c-line u-my-2" />

        <section>
          <div class="d-v d-vc">
            <p class="t t-gray u-mt-1"><b>Código CAPES</b></p>
            <p class="t t-gray u-mb-1">33009015088p9</p>
          </div>
        </section>

        <section class="d-v d-vc d-md-h d-md-hc">
          <?php echo PPG::externos(
            $type = 'Sucupira',
            $link = 'https://repositorio.unifesp.br/handle/11600/6108'
            ); ?>
          <?php echo PPG::externos(
            $type = 'Repositório de dados de esquisa',
            $link = 'https://repositoriodedados.unifesp.br/dataverse/eflch'
            ); ?>

          <?php echo PPG::externos(
            $type = 'Repositório institucional',
            $link = 'https://sucupira.capes.gov.br/sucupira/public/consultas/coleta/programa/viewPrograma.xhtml;jsessionid=OLRUfmVYapfO6QJKy+Wf0KS1.sucupira-218?popup=true&cd_programa=33009015089P5'
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