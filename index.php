<?php
// if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
//     $location = 'https://unifesp.br/prodmais/index.php';
//     header('HTTP/1.1 301 Moved Permanently');
//     header('Location: ' . $location);
//     exit;
// }
// if ($_SERVER["REQUEST_URI"] == "/") {
//   header("Location: https://unifesp.br/prodmais/index.php");
// }

/*

Este arquivo é parte do programa Prodmais

Prodmais é um software livre; você pode redistribuí-lo e/ou
modificá-lo dentro dos termos da Licença Pública Geral GNU como
publicada pela Free Software Foundation (FSF); na versão 3 da
Licença, ou (a seu critério) qualquer versão posterior.

Este programa é distribuído na esperança de que possa ser útil,
mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO
a qualquer MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a
Licença Pública Geral GNU para maiores detalhes.

Você deve ter recebido uma cópia da Licença Pública Geral GNU junto
com este programa, Se não, veja <https://www.gnu.org/licenses/>.

*/

?>
<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
  <script src="inc/js/axios.min.js"></script><!-- https://unpkg.com/axios/dist/axios.min.js -->
  <?php
  require 'inc/config.php';
  require 'inc/meta-header.php';
  require 'inc/functions.php';
  ?>
  <title><?php echo $branch ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <meta name="description" content="Indicadores de dados referentes à Unifesp." />
  <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />
  <!-- Facebook Tags - START -->
  <meta property="og:locale" content="pt_BR">
  <meta property="og:url" content="<?php echo $url_base ?>">
  <meta property="og:title" content="<?php echo $branch ?> - Página Principal">
  <meta property="og:site_name" content="<?php echo $branch ?>">
  <meta property="og:description" content="<?php echo $branch_description ?>">
  <meta property="og:image" content="<?php echo $facebook_image ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="800">
  <meta property="og:image:height" content="600">
  <meta property="og:type" content="website">
  <!-- Facebook Tags - END -->





</head>

<body data-theme="<?php echo $theme; ?>">

  <?php
  if (file_exists('inc/google_analytics.php')) {
    include 'inc/google_analytics.php';
  }
  ?>

  <!-- NAV -->
  <?php require 'inc/navbar.php'; ?>
  <!-- /NAV -->

  <main class="p-home-wrapper" id="home">

    <!-- <img class="p-home-logo" src="inc/images/logos/logo_main.svg" loading="lazy" /> -->
    <i class="i i-prodmais"></i>
    <h2 class="p-home-slogan">Uma ferramenta de busca da produção científica de pesquisadores</h2>
    <h3 class="p-home-instituicao"><?php echo ($instituicao); ?></h3>

    <?php if (paginaInicial::contar_registros_indice($index) == 0) : ?>
    <div class="alert alert-warning" role="alert">
      O Prod+ está em manutenção!
    </div>
    <?php endif; ?>


    <div class="p-home-search">

      <form class="p-home-form" class="" action="result.php" title="Pesquisa simples" method="post">

        <input class="c-input" type="search" placeholder="Pesquise por palavra chave" aria-label="Pesquisar"
          name="search">

        <button type="button" v-on:click="changeSearchMode()" class="c-btn p-home-form-btn"
          title="Alternar modo de pesquisa">
          <span v-if="searchPage == 'simple'">
            <svg class="c-btn-ico" x="0px" y="0px" viewBox="0 0 80 48">
              <path d="M7.7,10c0.7,0,1.5,0.2,2.2,0.5L39.7,25l30.6-14c2.5-1.1,5.5,0,6.6,2.5c1.1,2.5,0,5.5-2.5,6.6l-32.7,15
                  c-1.4,0.6-2.9,0.6-4.3-0.1l-32-15.6C3,18.2,2,15.2,3.2,12.8C4,11,5.8,10,7.7,10z" />
            </svg>
          </span>
          <span v-if="searchPage == 'advanced'">
            <svg class="c-btn-ico" x="0px" y="0px" viewBox="0 0 80 48">
              <path d="M72.3,35.5c-0.7,0-1.5-0.2-2.2-0.5L40.3,20.5l-30.6,14c-2.5,1.1-5.5,0-6.6-2.5c-1.1-2.5,0-5.5,2.5-6.6l32.7-15
                  c1.4-0.6,2.9-0.6,4.3,0.1l32,15.6c2.5,1.2,3.5,4.2,2.3,6.7C76,34.5,74.2,35.5,72.3,35.5z" />
            </svg>
          </span>
        </button>

        <transition name="homeeffect">
          <div class="d-v" v-if="searchPage == 'advanced'">

            <label class="p-home-info u-mt-1">Mais opções de pesquisa:</label>

            <?php paginaInicial::filter_select("vinculo.ppg_nome"); ?>

            <input class="c-input" type="search" placeholder="Filtrar por Nome do Programa de Pós-Graduação (Opcional)"
              aria-label="Mudar" name="search">

            <input class="c-input" list="datalistOptions" id="authorsDataList"
              placeholder="Filtrar por nome ou ID Lattes do autor" name="filter[]" v-model="query" @input="searchCV()">

            <datalist class="c-input" id="datalistOptions">
              <option v-for="author in authors" :key="author._id" :value="'vinculo.lattes_id:' + author._id">
                {{author._source.nome_completo}}
              </option>
            </datalist>

            <label class="p-home-info u-mt-1">Filtrar por data:</label>
            <div class="d-h d-hc">
              <input type="text" class="c-input--date" id="initialYear" name="initialYear" pattern="\d{4}"
                placeholder="Data inicial" />

              <input type="text" class="c-input--date" id="finalYear" name="finalYear" pattern="\d{4}"
                placeholder="Data final" />
            </div>



          </div> <!-- end advanced -->
        </transition>

        <button type="submit" class="c-btn-search" title="Buscar">
          <svg class="c-btn-search-ico" xmlns="https://www.w3.org/2000/svg" viewbox="0 0 100 100">
            <path
              d="M98.6,86.5L79.2,67c-0.9-0.9-2.1-1.4-3.3-1.4h-3.2c5.4-6.9,8.6-15.6,8.6-25C81.3,18.2,63.1,0,40.6,0
                  S0,18.2,0,40.6s18.2,40.6,40.6,40.6c9.4,0,18.1-3.2,25-8.6v3.2c0,1.3,0.5,2.4,1.4,3.3l19.5,19.5c1.8,1.8,4.8,1.8,6.6,0l5.5-5.5
                  C100.5,91.3,100.5,88.3,98.6,86.5z M40.6,65.6c-13.8,0-25-11.2-25-25s11.2-25,25-25s25,11.2,25,25S54.5,65.6,40.6,65.6z" />
          </svg>
        </button>

      </form>
    </div><!-- end search -->

    <div class="options">
      <button class="c-btn" v-on:click="showTips = !showTips" title="Mostrar dicas de pesquisa">
        Mostrar dicas de pesquisa
      </button>

      <button class="c-btn" v-on:click="showCategories = !showCategories">
        Mostrar pesquisa por categorias
      </button>
    </div>

    <transition name="homeeffect">
      <div class="c-tips" v-if="showTips">

        <h4>Dicas de como pesquisar</h4>
        <p>Use _ para busca por radical. Exemplo: biblio_.</p>
        <p>Para buscas exatas, coloque entre "". Exemplo: "Direito civil"</p>
        <p>Por padrão, o sistema utiliza o operador booleano OR. Caso necessite deixar a busca mais específica, utilize
          o operador AND (em maiúscula).</p>

        <h4>Busca avançada</h4>
        <p>O botão <img class="c-manual-img__in-text"
            src="<?php echo $url_base ?>/inc/images/manual/btn_busca_avancada.png"
            alt="botão alternar para busca avançada" height="28px" />, que se
          parece com uma seta apontando para baixo, permite fazer pesquisas com mais critérios, sendo eles, programa de
          pós-graduação, ID lattes do pesquisador, e período.</p>

        <h4>Consultando as categorias disponíveis</h4>
        <p>O botão <img class="c-manual-img__in-text"
            src="<?php echo $url_base ?>/inc/images/manual/btn_mostrar_pesquisa_categoria.png"
            alt="botão mostrar persquisa por categoria" height="28px" /> lista as produções classificados por Programa
          de Pós-graduação,
          tipo de produção, tipo de vínculo e base de dados, entre outras.</p>

        <h4>Buscando o perfil de um pesquisador</h4>
        <p>É possível também obter perfis detalhados dos pesquisadores. Esta opção está na opção "Pesquisadores" <img
            class="c-manual-img__in-text" src="<?php echo $url_base ?>/inc/images/manual/btn_pesquisadores.png"
            alt="botão pesquisadores" height="28px" />, no menu principal, no cabeçalho do Prodmais.</p>

        <p></p>
        <h4></h4>

      </div>
    </transition>

    <transition name="homeeffect">
      <div class="d-h d-ht u-mb-1" v-if="showCategories">

        <table>
          <thead>
            <tr class="thead">
              <th>Tipo</th>
              <th>Categorias</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th>Programa de Pós-Graduação</th>
              <th>
                <ul class="list-group">
                  <?php paginaInicial::unidade_inicio("vinculo.ppg_nome"); ?>
                </ul>
              </th>
            </tr>
            <tr>
              <th>Tipo de produção</th>
              <th>
                <ul class="list-group">
                  <?php paginaInicial::tipo_inicio(); ?>
                </ul>
              </th>
            </tr>
            <tr>
              <th>Tipo de vínculo</th>
              <th>
                <ul class="list-group">
                  <?php paginaInicial::unidade_inicio("vinculo.tipvin"); ?>
                </ul>
              </th>
            </tr>
            <tr>
              <th>Base de dados </th>
              <th>
                <ul class="list-group">
                  <?php paginaInicial::fonte_inicio(); ?>
                </ul>
              </th>
            </tr>
          </tbody>
        </table>

        <!-- <div>
          <button class="c-btn c-accordion" v-on:click="openAccordion('5')">Base de dados </button>
          <transition name="homeeffect">
            <div class="c-accordion-body" v-if="accOpened == '5'">
              <ul class="list-group">
              </ul>
            </div>
          </transition>
        </div> -->

      </div>
    </transition>




    <a class="u-skip" href="#mainseach">Voltar à barra de pesquisa principal</a>
  </main>
  <?php include('inc/footer.php'); ?>

  <script>
  var app = new Vue({
    el: '#home',

    data: {
      searchPage: 'simple',
      query: "",
      message: "Teste",
      authors: [],
      showCategories: false,
      showTips: false,
      accOpened: '0'

    },
    mounted() {
      this.searchCV();
    },
    methods: {
      searchCV() {
        axios.get(
            'tools/proxy_autocomplete_cv.php?query=' + this.query
          ).then((response) => {
            this.authors = response.data.hits.hits;
          })
          .catch((error) => {
            console.log(error);
            console.error(error);
            this.errored = true;
          })
          .finally(() => (this.loading = false));
      },
      changeSearchMode() {
        this.searchPage == 'simple' ? this.searchPage = 'advanced' : this.searchPage = 'simple'
      },
      openAccordion(acc) {
        this.accOpened == acc ? this.accOpened = '0' : this.accOpened = acc
      }


    }
  })

  let acc = document.getElementsByClassName("c-accordion");
  let i
  for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
      // this.classList.toggle("opened");
      var body = this.nextElementSibling;
      if (body.style.display === "block") {
        body.style.display = "none";
      } else {
        body.style.display = "block";
      }
    });
  }
  </script>


</body>

</html>