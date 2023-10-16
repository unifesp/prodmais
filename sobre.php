<!DOCTYPE HTML>
<html lang="pt-br">

<head>
    <?php
  require 'inc/config.php';
  require 'inc/meta-header.php';
  require 'inc/functions.php';
  require 'inc/components/Who.php';
  ?>
    <title>Prod Mais</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Prodmais" />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />


</head>

<body data-theme="<?php echo $theme; ?>">

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->


    <main class="p-about">

        <section class="p-about-section1">

            <svg class="logo-about" viewBox="0 0 150 115">
                <path d="M120.8,19.4h-0.6C115.9,8.3,105.2,1,93.3,1c-6.1,0-12,1.9-17,5.5C70.3,2.9,63.5,1,56.6,1
  C36,1,19.2,17.4,18.4,37.8C8.2,38.5,0.1,47.1,0.1,57.5c0,10.9,8.9,19.8,19.8,19.8h17c0.7,9.8,8.5,17.6,18.3,18.3v17
  c0,0.8,0.6,1.4,1.4,1.4h18.4c0.5,0,1-0.3,1.3-0.8l18-35.9h26.6c16,0,29-13,29-29S136.8,19.4,120.8,19.4z M120.8,74.4H94V58.2h26.8
  c0.4,0,0.7-0.3,0.7-0.7V30c0-2.7-0.4-5.3-1.1-7.7h0.4c14.4,0,26.1,11.7,26.1,26.1C146.9,62.7,135.2,74.4,120.8,74.4z M56.6,3.9
  c6.4,0,12.6,1.7,18,5C68.7,14,65,21.6,65,30v26.8h-8.5c-8,0-14.8,4.9-17.6,11.8V39.1c0-0.4-0.3-0.7-0.7-0.7H21.3
  C21.7,19.3,37.3,3.9,56.6,3.9z M118.9,22.2c0.8,2.5,1.2,5.1,1.2,7.8v26.8H94V39.1V21.5h24C118.2,21.9,118.5,22.1,118.9,22.2z
   M74.9,56.8h-8.5V30c0-8.2,3.7-15.5,9.4-20.4c0.4,0.1,0.8,0.1,1.1,0c9.4,6.5,15.6,17.3,15.6,29.6v17.6H74.9z M93.3,20.1
  c-0.4,0-0.7,0.3-0.7,0.7v8.1c-2.4-8.3-7.5-15.4-14.3-20.2c4.4-3.1,9.6-4.8,15-4.8c10.6,0,20.1,6.5,24.1,16.2H93.3z M37.5,39.9v34.6
  H20.6v-34c0.2-0.1,0.4-0.3,0.5-0.5H37.5z M19.1,40.6v33.8c-9-0.4-16.2-7.8-16.2-16.9C2.9,48.4,10.1,41,19.1,40.6z M39,74.7
  c0.6-9.2,8.3-16.4,17.6-16.4h9.2h8.5v16.9H56.6H39.4C39.3,74.9,39.2,74.8,39,74.7z M57.8,93.5c-0.1-0.2-0.3-0.4-0.5-0.5V76.6h16.9
  v16.9H57.8z M55.8,76.6v16.2c-8.8-0.4-15.8-7.4-16.2-16.2H55.8z M58,111.1V94.9h16.9c0.4,0,0.7-0.3,0.7-0.7V76.6h15.7L74,111.1H58z
   M75.6,75.1V58.2h16.9v16.4c-0.2,0.1-0.4,0.3-0.5,0.5H75.6z" />
            </svg>

            <h1 class="t t-h2 u-my-10">
                O Prodmais é um software desenvolvido para universidades e centros de
                pesquisa.
            </h1>

            <p class="p-about-text">O Prodmais é uma ferramenta que agrega informações sobre produções acadêmicas de
                diversas
                fontes, e dentre
                elas, principalmente a base Lattes. Permite efetuar pesquisas específicas na base de dados e filtrar os
                resultados com o apoio das diversas opções de filtros que a ferramenta possui. Também permite efetuar
                buscas por
                pesquisadores, e possui filtragens por área de atuação, Campus, idioma, data da publicação, nível de
                formação,
                enfim, várias opções. É possível exportar os resultados utilizando formatos suportados por diversos
                softwares
                bibliográficos, e também é possível exportar toda a informação de um perfil para o ORCID.
            </p>


            <!-- <h4 class="t t-h3 u-my-10">
        Base de dados atual
      </h4>
      <p>
        <?php echo paginaInicial::contar_registros_indice($index); ?> registros
      </p>
      <p>
        <?php echo paginaInicial::contar_registros_indice($index_cv); ?> currículos
      </p> -->

            <h3 class="t t-h3 u-my-10">
                É livre! É código aberto!
            </h3>

            <a href="https://github.com/unifesp/prodmais" target="blank">
                <p class="t t-a">Visite o nosso repositório Github</p>

                <svg title="Github" alt="Acesse o repositório Github" class="p-about-ico"
                    xmlns="https://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64px" height="64px">
                    <path
                        d="M32 6C17.641 6 6 17.641 6 32c0 12.277 8.512 22.56 19.955 25.286-.592-.141-1.179-.299-1.755-.479V50.85c0 0-.975.325-2.275.325-3.637 0-5.148-3.245-5.525-4.875-.229-.993-.827-1.934-1.469-2.509-.767-.684-1.126-.686-1.131-.92-.01-.491.658-.471.975-.471 1.625 0 2.857 1.729 3.429 2.623 1.417 2.207 2.938 2.577 3.721 2.577.975 0 1.817-.146 2.397-.426.268-1.888 1.108-3.57 2.478-4.774-6.097-1.219-10.4-4.716-10.4-10.4 0-2.928 1.175-5.619 3.133-7.792C19.333 23.641 19 22.494 19 20.625c0-1.235.086-2.751.65-4.225 0 0 3.708.026 7.205 3.338C28.469 19.268 30.196 19 32 19s3.531.268 5.145.738c3.497-3.312 7.205-3.338 7.205-3.338.567 1.474.65 2.99.65 4.225 0 2.015-.268 3.19-.432 3.697C46.466 26.475 47.6 29.124 47.6 32c0 5.684-4.303 9.181-10.4 10.4 1.628 1.43 2.6 3.513 2.6 5.85v8.557c-.576.181-1.162.338-1.755.479C49.488 54.56 58 44.277 58 32 58 17.641 46.359 6 32 6zM33.813 57.93C33.214 57.972 32.61 58 32 58 32.61 58 33.213 57.971 33.813 57.93zM37.786 57.346c-1.164.265-2.357.451-3.575.554C35.429 57.797 36.622 57.61 37.786 57.346zM32 58c-.61 0-1.214-.028-1.813-.07C30.787 57.971 31.39 58 32 58zM29.788 57.9c-1.217-.103-2.411-.289-3.574-.554C27.378 57.61 28.571 57.797 29.788 57.9z" />
                </svg>
            </a>
        </section>

        <section class="p-about-section2">

            <h3 id="créditos" class="t t-h3 u-my-10">Realização</h3>

            <p class="t t-md">Universidade Federal de São Paulo</p>
            <p class="t t-md t-gray">Superintendência de Tecnologia da Informação</p>

            <div class="dh">
                <?php if (file_exists("inc/images/logos/sti-branco.svg")) : ?>
                <a href="https://sti.unifesp.br/" target="_blank" title="Visite o site do STI">
                    <img class="p-about-logos" src="<?php echo $url_base; ?>/inc/images/logos/sti.svg"
                        alt="Logo do STI"></a>
                <?php endif ?>

                <?php if (file_exists("inc/images/logos/unifesp-branco.svg")) : ?>
                <a href="https://unifesp.br/" target="_blank" title="Visite o site doa Unifesp">
                    <img class="p-about-logos" src="<?php echo $url_base; ?>/inc/images/logos/unifesp.svg"
                        alt="Logo da Unifesp"></a>
                <?php endif ?>
            </div>

            <h3 id="créditos" class="t t-h3 u-my-10">Equipe</h3>


            <div class="p-about-team">
                <?php
        Who::ppg(
          $picture = "http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id=K4525821T0",
          $name = 'Lidiane Cristina da Silva',
          $title = 'Superintendente da TI',
          $link = 'http://lattes.cnpq.br/2259956816336032'
        );

        Who::ppg(
          $picture = "https://souciencia.unifesp.br/images/equipe/Alexsandro.jpeg",
          $name = 'Alexandro Cardoso Carvalho',
          $title = 'Chefe da Divisão de Gestão da Informação',
          $link = 'http://lattes.cnpq.br/6792408436784536'
        );

        Who::ppg(
          $picture = "https://avatars.githubusercontent.com/u/499115?v=4",
          $name = 'Tiago Rodrigo Marçal Murakami',
          $title = 'Bolsista FAP UNIFESP',
          $link = 'http://lattes.cnpq.br/0306160176168674'
        );

        Who::ppg(
          $picture = "https://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id=K1702294J9",
          $name = 'Ricardo Ireno dos Santos',
          $title = 'Bolsista FAP UNIFESP',
          $link = 'http://lattes.cnpq.br/8604973833723919'
        );
        ?>
            </div>
        </section>

    </main>
    <?php include('inc/footer.php'); ?>

</body>


</html>