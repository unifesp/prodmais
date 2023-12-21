<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php
    require 'inc/functions.php';
    require 'inc/config.php';
    require 'inc/meta-header.php';

    ?>
    <meta charset="utf-8" />
    <title>Produções</title>
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
                <h2>Produções</h2>
                <div class="md-container">
                    <iframe src="https://unifesp.br/prodmais/kibana/app/dashboards#/view/7cc146e0-9f9c-11ee-b989-9f6cdcc63b63?embed=true&_g=(refreshInterval:(pause:!t,value:10000),time:(from:now-15m,to:now))&_a=()" height="600" width="800"></iframe>
                </div>
            </div>

        </div>
        </div>
    </main>





    <?php include('inc/footer.php'); ?>

</body>

</html>