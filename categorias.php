<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php
            require 'inc/config.php';
            require 'inc/meta-header.php';
            require 'inc/functions.php';
        ?>
    <title><?php echo $branch ?> — Busca por categoria</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Categorias de dados acadêmicos" />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />
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
    <main class="p-categorias" id="home">
        <h3 class="t t-h3 u-mb-20">Busca por categorias</h3>
        <div class="d-grid">
            <div class="row">
                <div class="col-12 col-md-6">
                    <h4 class="t t-h4">Programa de Pós-Graduação</h4>
                    <ul class="p-categorias-list">
                        <?php paginaInicial::unidade_inicio("vinculo.ppg_nome"); ?>
                    </ul>
                    <?php if($mostrar_instituicao): ?>
                    <h4 class="t t-h4">Instituição</h4>
                    <ul class="p-categorias-list">
                        <?php paginaInicial::unidade_inicio("vinculo.instituicao"); ?>
                    </ul>
                    <?php endif; ?>

                </div>
                <div class="col-12 col-md-6">
                    <h4 class="t t-h4">Tipo de produção</h4>
                    <?php paginaInicial::tipo_inicio(); ?>
                </div>
            </div>
        </div>
        <div class="d-container">
            <div class="row">
                <div class="col-12 col-md-6">
                    <h4 class="t t-h4">Tipo de vínculo</h4>
                    <?php paginaInicial::unidade_inicio("vinculo.tipvin"); ?>
                </div>
            </div>
        </div>
    </main>
    <?php include('inc/footer.php'); ?>
</body>

</html>