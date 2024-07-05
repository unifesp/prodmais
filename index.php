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
    <script src="inc/js/axios.min.js"></script>
    <script src="/prodmais/inc/js/axios.min.js"></script>
    <?php
    require_once 'inc/config.php';
    require_once 'inc/meta-header.php';
    require_once 'inc/functions.php';
    ?>
    <title><?php echo $branch ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Produção científica e acadêmica" />
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
    // Verifica se o arquivo do Google Analytics existe antes de incluí-lo
    $google_analytics_path = 'inc/google_analytics.php';
    if (file_exists($google_analytics_path)) {
        include $google_analytics_path;
    }
    ?>


    <!-- NAV -->
    <?php require_once 'inc/navbar.php'; ?>
    <!-- /NAV -->

    <main class="p-home-wrapper" id="home">
        <transition name="homeeffect">
            <div class="c-tips" v-if="showTips">
                <a class="u-skip" href="#aftertips">Pular dicas de pesquisa</a>

                <h4>Dicas de como pesquisar</h4>
                <p>Use * para busca por radical. Exemplo: biblio*.</p>
                <p>Para buscas exatas, coloque entre "". Exemplo: "Direito civil"</p>
                <p>Por padrão, o sistema utiliza o operador booleano OR. Caso necessite deixar a busca mais específica,
                    utilize
                    o operador AND (em maiúscula).</p>


                <h4>Buscando o perfil de um pesquisador</h4>
                <p>É possível também obter perfis detalhados dos pesquisadores. Esta opção está na opção "Pesquisadores"
                    <img class="c-manual-img__in-text"
                        src="<?php echo $url_base ?>/inc/images/manual/btn_pesquisadores.png" alt="botão pesquisadores"
                        height="28px" />, no menu principal, no cabeçalho do Prodmais.
                </p>


                <span id="aftertips"></span>
            </div>
        </transition>


        <!-- <img class="p-home-logo" src="inc/images/logos/logo_main.svg" loading="lazy" /> -->
        <i class="i i-prodmais .p-home-gradient"></i>
        <h2 class="p-home-slogan .p-home-gradient"><?php echo ($slogan); ?></h2>
        <!-- <h3 class="p-home-instituicao">< ?php echo ($instituicao); ?></h3> -->

        <?php if (paginaInicial::contar_registros_indice($index) == 0) : ?>
        <div class="alert alert-warning" role="alert">
            O Prod+ está em manutenção!
        </div>
        <?php endif; ?>

        <div class="p-home-search">

            <form class="p-home-form" class="" action="result.php" title="Pesquisa simples" method="post">

                <div class="c-searcher">
                    <input id="mainseach" name="search" type="search"
                        placeholder="Pesquise por palavras chave ou nomes de autores" aria-label="Pesquisar">
                    <button class="c-searcher__btn" type="submit" title="Buscar">
                        <i class="i i-lupa c-searcher__btn-ico"></i>
                    </button>
                </div>

            </form>
        </div><!-- end p-home-search -->


        <button class="c-btn--tip p-home__tips-btn" @mouseover="showTips = true" @mouseleave="showTips = false"
            title="Mostrar dicas de pesquisa">
            <i class="i i-btn i-sm i-help"></i>
        </button>
        <a class="u-skip" href="#mainseach">Voltar à barra de pesquisa principal</a>
    </main>
    <?php include('inc/footer.php'); ?>

    <script>
    var app = new Vue({
        el: '#home',

        data: {
            showTips: false,
            accOpened: '0'
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