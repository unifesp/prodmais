<?php

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
com este programa, Se não, veja <http://www.gnu.org/licenses/>.

*/

?>
<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
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

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .jumbotron {
            background-image: url("<?php echo $background_1 ?>");
            background-size: 100%;
            background-repeat: no-repeat;
        }
    </style>
    <link rel="stylesheet" href="inc/css/style.css" />

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

    <main class="main">
        <svg class="logo-home" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 169">
            <path d="M150.7 101.4h-16.2c-0.7 0-1.3-0.6-1.3-1.3v-15c-8.6-0.6-15.5-7.5-16.1-16.1h-15c-9.6 0-17.4-7.8-17.4-17.4 0-9.2 7.2-16.8 16.2-17.4 0.6-18 15.5-32.4 33.6-32.4 6.2 0 12.2 1.7 17.4 4.9 4.4-3.2 9.5-4.9 15-4.9 10.5 0 19.9 6.5 23.7 16.2h0.5c14.1 0 25.5 11.5 25.5 25.5s-11.5 25.5-25.5 25.5h-23.5l-15.8 31.7C151.6 101.2 151.2 101.4 150.7 101.4zM135.8 98.9h14.1l15.8-31.7c0.2-0.4 0.7-0.7 1.1-0.7h24.3c12.7 0 23-10.3 23-23s-10.3-23-23-23h-1.4c-0.5 0-1-0.3-1.2-0.8 -3.2-9.2-12-15.3-21.7-15.3 -5.1 0-10 1.7-14.1 4.9 -0.4 0.3-1 0.4-1.5 0.1 -5-3.2-10.8-4.9-16.8-4.9 -17.2 0-31.1 14-31.1 31.1 0 0.7-0.6 1.3-1.3 1.3 -8.2 0-14.9 6.7-14.9 14.9 0 8.2 6.7 14.9 14.9 14.9h16.2c0.7 0 1.3 0.6 1.3 1.3 0 8.2 6.7 14.9 14.9 14.9 0.7 0 1.3 0.6 1.3 1.3V98.9z" />
            <path d="M150.7 84.6h-16.2c-9.3 0-16.8-7.5-16.8-16.8S125.3 51 134.5 51h16.2c0.3 0 0.6 0.3 0.6 0.6V84C151.3 84.3 151.1 84.6 150.7 84.6zM134.5 52.2c-8.6 0-15.6 7-15.6 15.6s7 15.6 15.6 15.6h15.6V52.2H134.5z" />
            <path d="M166.9 68.4h-64.7c-0.3 0-0.6-0.3-0.6-0.6V35.4c0-18.2 14.8-33 33-33s33 14.8 33 33v32.4C167.5 68.1 167.2 68.4 166.9 68.4zM102.8 67.2h63.5V35.4c0-17.5-14.2-31.7-31.7-31.7 -17.5 0-31.7 14.2-31.7 31.7V67.2z" />
            <path d="M150.7 100.8h-16.2c-0.3 0-0.6-0.3-0.6-0.6V67.8c0-0.3 0.3-0.6 0.6-0.6h32.4c0.2 0 0.4 0.1 0.5 0.3 0.1 0.2 0.1 0.4 0 0.6l-16.2 32.4C151.2 100.7 151 100.8 150.7 100.8zM135.2 99.5h15.2l15.6-31.1h-30.7V99.5z" />
            <path d="M118.3 68.4h-16.2c-9.3 0-16.8-7.5-16.8-16.8 0-9.3 7.5-16.8 16.8-16.8h16.2c0.3 0 0.6 0.3 0.6 0.6v32.4C119 68.1 118.7 68.4 118.3 68.4zM102.2 36.1c-8.6 0-15.6 7-15.6 15.6 0 8.6 7 15.6 15.6 15.6h15.6V36.1H102.2z" />
            <path d="M191.2 68.4h-24.3c-0.3 0-0.6-0.3-0.6-0.6V19.3c0-0.3 0.3-0.6 0.6-0.6h24.3c13.7 0 24.9 11.2 24.9 24.9S204.9 68.4 191.2 68.4zM167.5 67.2h23.6c13 0 23.6-10.6 23.6-23.6s-10.6-23.6-23.6-23.6h-23.6V67.2z" />
            <path d="M191.2 52.2h-48.5c-0.3 0-0.6-0.3-0.6-0.6V27.3c0-13.7 11.2-24.9 24.9-24.9 13.7 0 24.9 11.2 24.9 24.9v24.3C191.8 52 191.5 52.2 191.2 52.2zM143.3 51h47.3V27.3c0-13-10.6-23.6-23.6-23.6s-23.6 10.6-23.6 23.6V51z" />
            <path d="M158.4 167h-32c-6.6 0-12-5.4-12-12v-32c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v32C170.4 161.6 165 167 158.4 167zM126.4 118.4c-2.5 0-4.5 2-4.5 4.5v32c0 2.5 2 4.5 4.5 4.5h32c2.5 0 4.5-2 4.5-4.5v-32c0-2.5-2-4.5-4.5-4.5H126.4z" />
            <path d="M4.7 166.9c-2.1 0-3.8-1.7-3.8-3.8v-48.5c0-2.1 1.7-3.8 3.8-3.8s3.8 1.7 3.8 3.8v48.5C8.5 165.2 6.8 166.9 4.7 166.9z" />
            <path d="M232 133.2c-2.1 0-3.8-1.7-3.8-3.8V80.8c0-2.1 1.7-3.8 3.8-3.8 2.1 0 3.8 1.7 3.8 3.8v48.5C235.7 131.5 234 133.2 232 133.2z" />
            <path d="M272.5 133.2c-2.1 0-3.8-1.7-3.8-3.8V80.8c0-2.1 1.7-3.8 3.8-3.8s3.8 1.7 3.8 3.8v48.5C276.2 131.5 274.5 133.2 272.5 133.2z" />
            <path d="M296.7 108.9h-48.5c-2.1 0-3.8-1.7-3.8-3.8s1.7-3.8 3.8-3.8h48.5c2.1 0 3.8 1.7 3.8 3.8C300.5 107.2 298.8 108.9 296.7 108.9z" />
            <path d="M29 134.5H4.7c-2.1 0-3.8-1.7-3.8-3.8V90.1c0-6.4 5.2-11.7 11.7-11.7H29C44.5 78.4 57 91 57 106.5S44.5 134.5 29 134.5zM8.5 127H29c11.3 0 20.5-9.2 20.5-20.5S40.3 86 29 86H12.6c-2.3 0-4.1 1.8-4.1 4.1V127z" />
            <path d="M224.2 165.5h-16.6c-15.5 0-28.1-12.6-28.1-28.1 0-15.5 12.6-28.1 28.1-28.1H232c2.1 0 3.8 1.7 3.8 3.8V154C235.7 160.4 230.6 165.5 224.2 165.5zM207.7 117c-11.3 0-20.5 9.2-20.5 20.5 0 11.3 9.2 20.5 20.5 20.5h16.6c2.2 0 3.9-1.8 3.9-3.9v-37H207.7z" />
            <path d="M69.6 167c-2.1 0-3.8-1.7-3.8-3.8v-40.6c0-6.5 5.3-11.8 11.8-11.8H102c2.1 0 3.8 1.7 3.8 3.8s-1.7 3.8-3.8 3.8H77.6c-2.3 0-4.2 1.9-4.2 4.2v40.6C73.4 165.3 71.7 167 69.6 167z" />
        </svg>
        <h2 class="textbox">
            Uma ferramenta de busca da produção científica de pesquisadores da UNIFESP.
        </h2>

        <?php if (paginaInicial::contar_registros_indice($index) == 0) : ?>
            <div class="alert alert-warning" role="alert">
                O Prod+ está em manutenção!
            </div>
        <?php endif; ?>

        <div id="mySearch">
            <div class="div-v" v-if="searchPage == 'simple'">
                <form class="myform" class="" action="result.php">
                    <input class="myinput" type="search" placeholder="Pesquisar" aria-label="Pesquisar" name="search">
                    <button type="submit" class="mybtn-pesquisar">
                        <svg class="myicon myicon-pesquisar" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 100 100">
                            <path d="M98.6,86.5L79.2,67c-0.9-0.9-2.1-1.4-3.3-1.4h-3.2c5.4-6.9,8.6-15.6,8.6-25C81.3,18.2,63.1,0,40.6,0
        S0,18.2,0,40.6s18.2,40.6,40.6,40.6c9.4,0,18.1-3.2,25-8.6v3.2c0,1.3,0.5,2.4,1.4,3.3l19.5,19.5c1.8,1.8,4.8,1.8,6.6,0l5.5-5.5
        C100.5,91.3,100.5,88.3,98.6,86.5z M40.6,65.6c-13.8,0-25-11.2-25-25s11.2-25,25-25s25,11.2,25,25S54.5,65.6,40.6,65.6z" />
                        </svg>
                    </button>
                </form>
            </div>
            <div class="div-v" v-if="searchPage == 'advanced'">
                <form class="myform" action="result.php">

                    <input class="myinput" type="search" placeholder="Pesquisar" aria-label="Pesquisar" name="search">

                    <label>Filtrar por Nome do Programa de Pós-Graduação (Opcional):</label>
                    <?php paginaInicial::filter_select("vinculo.ppg_nome"); ?>

                    <input class="myinput" list="datalistOptions" id="authorsDataList" placeholder="Autores (nome ou ID Lattes)" name="filter[]" v-model="query" @input="searchCV()">

                    <datalist class="myinput" id="datalistOptions">
                        <option v-for="author in authors" :key="author._id" :value="'vinculo.lattes_id:' + author._id">{{author._source.nome_completo}}</option>
                    </datalist>

                    <div class="div-h">

                        <input type="text" class="myinput myinput-date" id="initialYear" name="initialYear" pattern="\d{4}" placeholder="19XX" value="">
                        <span> - </span>
                        <input type="text" class="myinput myinput-date" id="finalYear" name="finalYear" pattern="\d{4}" placeholder="20XX" value="">

                    </div>
                    <button type="submit" class="mybtn-pesquisar">
                        <svg class="myicon myicon-pesquisar" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 100 100">
                            <path d="M98.6,86.5L79.2,67c-0.9-0.9-2.1-1.4-3.3-1.4h-3.2c5.4-6.9,8.6-15.6,8.6-25C81.3,18.2,63.1,0,40.6,0
        S0,18.2,0,40.6s18.2,40.6,40.6,40.6c9.4,0,18.1-3.2,25-8.6v3.2c0,1.3,0.5,2.4,1.4,3.3l19.5,19.5c1.8,1.8,4.8,1.8,6.6,0l5.5-5.5
        C100.5,91.3,100.5,88.3,98.6,86.5z M40.6,65.6c-13.8,0-25-11.2-25-25s11.2-25,25-25s25,11.2,25,25S54.5,65.6,40.6,65.6z" />
                        </svg>
                    </button>

                </form>

                <small class="small-info">Dica: Use * para busca por radical. Ex: biblio*.</small><br />
                <small class="small-info">Dica 2: Para buscas exatas, coloque entre "". Ex: "Direito civil"</small><br />
                <small class="small-info">Dica 3: Por padrão, o sistema utiliza o operador booleano OR. Caso necessite deixar a busca mais específica, utilize o operador AND (em maiúscula)</small>
            </div> <!-- advanced -->

            <button v-on:click="changeSearch()" class="mybtn">
                <span v-if="searchPage == 'simple'">
                    <svg class="myicon myicon-changesearch" x="0px" y="0px" viewBox="0 0 80 48">
                        <path class="st0" d="M7.7,10c0.7,0,1.5,0.2,2.2,0.5L39.7,25l30.6-14c2.5-1.1,5.5,0,6.6,2.5c1.1,2.5,0,5.5-2.5,6.6l-32.7,15
          c-1.4,0.6-2.9,0.6-4.3-0.1l-32-15.6C3,18.2,2,15.2,3.2,12.8C4,11,5.8,10,7.7,10z" />
                    </svg>
                </span>
                <span v-if="searchPage == 'advanced'">
                    <svg class="myicon myicon-changesearch" x="0px" y="0px" viewBox="0 0 80 48">
                        <path class="st0" d="M72.3,35.5c-0.7,0-1.5-0.2-2.2-0.5L40.3,20.5l-30.6,14c-2.5,1.1-5.5,0-6.6-2.5c-1.1-2.5,0-5.5,2.5-6.6l32.7-15
          c1.4-0.6,2.9-0.6,4.3,0.1l32,15.6c2.5,1.2,3.5,4.2,2.3,6.7C76,34.5,74.2,35.5,72.3,35.5z" />
                    </svg>
                </span>
            </button>


        </div> <!-- app -->

        <div class="two">
            <div class="container mt-4">
                <div class="row">
                    <div class="col-md-3">
                        <h3 class="uk-h3">Programa de Pós-Graduação</h3>
                        <div class="accordion" id="accordionPPGs">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        Programa de Pós-Graduação
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionPPGs">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            <?php paginaInicial::unidade_inicio("vinculo.ppg_nome"); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h3 class="uk-h3">Tipo de vínculo / material</h3>
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Tipo de vínculo
                                    </button>
                                </h3>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            <?php paginaInicial::unidade_inicio("vinculo.tipvin"); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Tipo de material
                                    </button>
                                </h3>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <ul class="list-group">
                                            <?php paginaInicial::tipo_inicio(); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h3 class="uk-h3">Fonte</h3>
                        <ul class="list-group">
                            <?php paginaInicial::fonte_inicio(); ?>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h3 class="uk-h3">Estatísticas</h3>
                        <ul class="list-group">
                            <li class="list-group-item"><?php echo paginaInicial::contar_registros_indice($index); ?> registros</li>
                            <li class="list-group-item"><?php echo paginaInicial::contar_registros_indice($index_cv);; ?> currículos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <br /><br />

        </div>
        </div>
    </main>
    <?php include('inc/footer.php'); ?>

    <script>
        var app = new Vue({
            el: '#mySearch',

            data: {
                searchPage: 'simple',
                query: "",
                message: "Teste",
                authors: []
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
                changeSearch() {
                    if (this.searchPage == 'simple') this.searchPage = 'advanced'
                    else this.searchPage = 'simple'
                }
            }
        })
    </script>


</body>

</html>