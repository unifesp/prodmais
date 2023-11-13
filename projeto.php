<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php

require 'inc/config.php';
require 'inc/functions.php';
require 'inc/meta-header.php';
require 'inc/components/SList.php';
require 'inc/components/TagCloud.php';
require 'inc/components/Who.php';


// get data from elasticsearch by id
$params = [];
$params['index'] = $index_projetos;
$params['id'] = $_GET['ID'];
$result = $client->get($params);
$projeto_array = $result['_source'];



if (isset($projeto_array['DADOS-DO-PROJETO'][0])) {
    $projeto_array['DADOS-DO-PROJETO'] = $projeto_array['DADOS-DO-PROJETO'][0];
}

$period = $projeto_array['DADOS-DO-PROJETO']['@attributes']['ANO-INICIO'];

if (!empty($projeto_array['DADOS-DO-PROJETO']['@attributes']['ANO-FIM'])) {
    $period = $period . ' a ' . $projeto_array['DADOS-DO-PROJETO']['@attributes']['ANO-FIM'];
} else {
    $period = 'Em andamento desde ' . $period;
}

if (isset($projeto_array['DADOS-DO-PROJETO']['PRODUCOES-CT-DO-PROJETO']['PRODUCAO-CT-DO-PROJETO'])) {
    if (is_array($projeto_array['DADOS-DO-PROJETO']['PRODUCOES-CT-DO-PROJETO']['PRODUCAO-CT-DO-PROJETO'])) {
        $num_producoes = count($projeto_array['DADOS-DO-PROJETO']['PRODUCOES-CT-DO-PROJETO']['PRODUCAO-CT-DO-PROJETO']);
    } else {
        $num_producoes = 0;
    }
} else {
    $num_producoes = 0;
}
//echo "<pre>" . print_r($projeto_array, true) . "</pre>";



?>

<head>
    <meta charset="utf-8" />
    <title><?php echo $branch; ?> - Projeto
        <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NOME-DO-PROJETO'] ?></title>
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
                <section class="p-projeto-header">
                    <div class="p-projeto-header-d1">
                        <i class="i i-sm i-project p-projeto-logo"></i>
                    </div>
                    <div class="p-projeto-header-d2">
                        <h2 class="t t-h5">Projeto de pesquisa</h2>
                        <h1 class="t t-h1 p-projeto-title">
                            <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NOME-DO-PROJETO'] ?>
                        </h1>

                        <div class="p-projeto-header-d4">
                            <i class="i i-sm i-date"></i>
                            <p class="t t-b u-mr-05"> <?php echo $period ?></p>
                            <i class="i i-sm i-production"></i>
                            <p class="t t-b"> <?php echo $num_producoes ?></p>
                            <p class="t t-b t-gray u-ml-05">(Número de produções)</p>
                        </div>
                    </div>
                </section>
                <section class="p-projeto-main">
                    <hr class="c-line u-my-20" />
                    <section class="p-projeto-description">
                        <p class="t t-b u-mb-20">Sobre o projeto de pesquisa</p>
                        <p class="t t-justify">
                            <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['DESCRICAO-DO-PROJETO']; ?></p>

                    </section>
                    <hr class="c-line u-my-20" />
                    <h4 class="t t-h4 u-mb-20">Produções do projeto</h4>
                    <section class="dv d-md-h">
                        <ul class="c-authors-list">
                            <?php if (isset($projeto_array['DADOS-DO-PROJETO']['PRODUCOES-CT-DO-PROJETO']['PRODUCAO-CT-DO-PROJETO'])) : ?>
                            <?php foreach ($projeto_array['DADOS-DO-PROJETO']['PRODUCOES-CT-DO-PROJETO']['PRODUCAO-CT-DO-PROJETO'] as $producao_do_projeto) : ?>
                            <li class="c-card-author t t-b t-md">
                            <li class='s-nobullet'>
                                <div class='s-list'>
                                    <div class='s-list-bullet'>
                                        <i class='i i-articlePublished s-list-ico'></i>
                                    </div>

                                    <div class='s-list-content'>
                                        <?php if (isset($producao_do_projeto['@attributes'])) : ?>
                                        <p class='t t-b'>
                                            <?php echo $producao_do_projeto['@attributes']['TITULO-DA-PRODUCAO-CT'] ?></a>
                                        </p>
                                        <p class='t t-gray'>Tipo de produção:
                                            <?php echo $producao_do_projeto['@attributes']['TIPO-PRODUCAO-CT']; ?>
                                        </p>
                                        <?php else : ?>
                                        <p class='t t-b'>
                                            <?php echo $producao_do_projeto['TITULO-DA-PRODUCAO-CT'] ?></a>
                                        </p>
                                        <p class='t t-gray'>Tipo de produção:
                                            <?php echo $producao_do_projeto['TIPO-PRODUCAO-CT']; ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                            </li>
                            <?php endforeach; ?>
                            <?php else : ?>
                            <p class="t t-justify">Não há produções cadastradas para este projeto.</p>
                            <?php endif; ?>
                        </ul>
                    </section>
                    <hr class="c-line u-my-20" />
                    <h4 class="t t-h4 u-mb-20">Integrantes</h4>
                    <section class="dv d-md-h">
                        <div class="dv">
                            <ul class='p-projeto-integrantes'>
                                <?php
                                foreach ($projeto_array['DADOS-DO-PROJETO']['EQUIPE-DO-PROJETO']['INTEGRANTES-DO-PROJETO'] as $integrante) {
                                    if (isset($integrante['@attributes']['NOME-COMPLETO'])) {
                                        $nome_integrante = $integrante['@attributes']['NOME-COMPLETO'];
                                        echo ("<div class='d-icon-text'>
                                            <i class='i i-project-participant'></i>
                                            <li class=''>$nome_integrante</li>
                                            </div>"
                                        );
                                    } elseif (isset($integrante['NOME-COMPLETO'])) {
                                        $nome_integrante = $integrante['NOME-COMPLETO'];
                                        echo ("<div class='d-icon-text'>
                                            <i class='i i-project-participant'></i>
                                            <li class=''>$nome_integrante</li>
                                            </div>"
                                        );
                                    } else {
                                    }
                                }
                                ?>
                            </ul>
                        </div>

                        <div class="dv">
                            <?php if ($projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-GRADUACAO'] != '') : ?>
                            <div class='d-icon-text'>
                                <i class='i i-project-participant'></i>
                                <p class="t">Alunos de graduação:
                                    <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-GRADUACAO'] ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <?php if ($projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-ESPECIALIZACAO'] != '') : ?>
                            <div class='d-icon-text'>
                                <i class='i i-project-participant'></i>
                                <p class="t">Alunos de especialização:
                                    <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-ESPECIALIZACAO'] ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <?php if ($projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-MESTRADO-ACADEMICO'] != '') : ?>
                            <div class='d-icon-text'>
                                <i class='i i-project-participant'></i>
                                <p class="t">Alunos de mestrado acadêmico:
                                    <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-MESTRADO-ACADEMICO'] ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <?php if ($projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-MESTRADO-PROF'] != '') : ?>
                            <div class='d-icon-text'>
                                <i class='i i-project-participant'></i>
                                <p class="t">Alunos de mestrado profissional:
                                    <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-MESTRADO-PROF'] ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <?php if ($projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-DOUTORADO'] != '') : ?>
                            <div class='d-icon-text'>
                                <i class='i i-project-participant'></i>
                                <p class="t">Alunos de doutorado:
                                    <?php echo $projeto_array['DADOS-DO-PROJETO']['@attributes']['NUMERO-DOUTORADO'] ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </section>
            </div>
        </div>
    </main>

    <?php include('inc/footer.php'); ?>
    <?php //echo "<pre>" . print_r($projeto_array, true) . "</pre>";
    ?>

</body>

</html>