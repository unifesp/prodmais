<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php
  require 'inc/config.php';
  require 'inc/meta-header.php';
  ?>
    <meta charset="utf-8" />
    <title>Manual do Prodmais</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Prodmais." />
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
                <h2>Manual do Prodmais</h2>
                <div class="md-container">

                </div>
                <p>O Prodmais é uma ferramenta que agrega informação de diversas fontes, mas não realiza processamento
                    posterior
                    nelas. Neste manual prático você podrá tirar suas dúvidas sobre como utilizar o Prodmais. É fácil
                    efetuar as
                    suas buscas no Prodmais, e você pode fazer utilizando um dos seguintes caminhos:</p>

                <?php
        $parsedown = new Parsedown();
        $txtm = file_get_contents('inc/md/tips_home.md');
        echo $parsedown->text($txtm);
        ?>
            </div>

        </div>
        </div>
    </main>





    <?php include('inc/footer.php'); ?>

</body>

</html>