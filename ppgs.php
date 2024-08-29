<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php


require 'inc/config.php';
require 'inc/functions.php';

$limit = 500;
$params = [];
$params["index"] = $index_ppg;
if (isset($_REQUEST['CONCEITO_CAPES'])) {
    $query["query"]["bool"]["filter"][0]["term"]["CONCEITO_CAPES.keyword"] = $_REQUEST['CONCEITO_CAPES'];
    $params["body"] = $query;
}
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];

$params["size"] = $limit;

$cursor = $client->search($params);

class ListPPGs
{
    static function listAll($data)
    {
        foreach ($data as $ppgs) {
            $ppgs['_source']['ID'] = $ppgs['_id'];
            if ($ppgs['_source']['NOME_CAMPUS']) {
                $unidade = $ppgs['_source']['NOME_CAMPUS'];
            } elseif ($ppgs['_source']['NOME_INSTITUICAO']) {
                $unidade = $ppgs['_source']['NOME_INSTITUICAO'];
            } else {
                $h1 = [];
            }
            $h1[$unidade]['nome'] = $unidade;
            $h1[$unidade]['ppgs'][$ppgs['_id']] = $ppgs['_source'];
        }

        foreach ($h1 as $key => $ppgs) {
            $nome[$key] = $ppgs['nome'];
        }
        array_multisort($nome, SORT_ASC, $h1);

        foreach ($h1 as $ppgs) {

            $nome_ppg = [];
            foreach ($ppgs['ppgs'] as $key => $value) {
                $nome_ppg[$key] = $value['NOME_PPG'];
            }
            if (count($nome_ppg) > 0 && count($ppgs['ppgs']) > 0) {
                array_multisort($nome_ppg, SORT_ASC, $ppgs['ppgs']);
            }
            echo '
            <details class="p-ppgs-item">
                <summary class="p-ppgs-item-header">'
                . $ppgs['nome'] .
                '</summary>
            ';
            foreach ($ppgs['ppgs'] as $key => $value) {
                //echo "<pre>".print_r($key, true)."</pre>";
                //echo "<pre>".print_r($value, true)."</pre>";

                SList::genericItem(
                    $type = 'ppg',
                    $itemName = '<a href="ppg.php?ID=' . $value['ID'] . '">' . $value['NOME_PPG'] . '</a>',
                    $itemNameLink = '',
                    $itemInfoA = ($value['COD_CAPES'] == '') ? '' : 'Código CAPES: ' . $value['COD_CAPES'],
                    $itemInfoB = ($value['PPG_EMAIL'] == '') ? '' : 'E-mail: ' . $value['PPG_EMAIL'],
                    $itemInfoC = ($value['PPG_SITE'] == '') ? '' : 'Site: <a href="' . $value['PPG_SITE'] . '">' . $value['PPG_SITE'] . '</a>',
                    $itemInfoD = ($value['NIVEL'] == '') ? '' : 'Nível: ' . $value['NIVEL'],
                    $itemInfoE = ($value['CONCEITO_CAPES'] == '') ? '' : 'Conceito CAPES: ' . $value['CONCEITO_CAPES'],
                    $authors = '',
                    $tags = '',
                    $yearStart = $value['INI_PPG'],
                    $yearEnd = ''
                );
            }
            echo  '</details>';
        }
    }
}

?>

<head>
    <?php
    require 'inc/meta-header.php';
    require 'inc/components/SList.php';
    require 'inc/components/TagCloud.php';
    ?>
    <meta charset="utf-8" />
    <title><?php echo $branch; ?> Programas de Pós-Graduação </title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <meta name="description" content="Prodmais." />
    <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />

</head>

<body data-theme="<?php echo $theme; ?>" class="c-wrapper-body">
    <?php
    if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    } elseif (file_exists('../inc/google_analytics.php')) {
        include '../inc/google_analytics.php';
    }
    ?>

    <?php require 'inc/navbar.php'; ?>

    <main class="c-wrapper-container">
        <div class="c-wrapper-paper">
            <div class="c-wrapper-inner">
                <h1 class=" t t-h1 u-mb-20">Programas de Pós-Graduação</h1>

                <h4 class=" t t-h4">Filtrar por conceito</h4>
                <div class="p-result-search-ctn">
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <input type="hidden" name="CONCEITO_CAPES" value="2">
                        <button type="submit" class="c-btn" style="width: 1rem;">2</button>
                    </form>
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <input type="hidden" name="CONCEITO_CAPES" value="3">
                        <button type="submit" class="c-btn" style="width: 1rem;">3</button>
                    </form>
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <input type="hidden" name="CONCEITO_CAPES" value="4">
                        <button type="submit" class="c-btn" style="width: 1rem;">4</button>
                    </form>
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <input type="hidden" name="CONCEITO_CAPES" value="5">
                        <button type="submit" class="c-btn" style="width: 1rem;">5</button>
                    </form>
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <input type="hidden" name="CONCEITO_CAPES" value="6">
                        <button type="submit" class="c-btn" style="width: 1rem;">6</button>
                    </form>
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <input type="hidden" name="CONCEITO_CAPES" value="7">
                        <button type="submit" class="c-btn" style="width: 1rem;">7</button>
                    </form>
                    <form class="p-home-form" class="" action="ppgs.php" method="post">
                        <button type="submit" class="c-btn" style="width: 1rem;">Todos</button>
                    </form>
                </div>

                <div class="p-ppg-container">
                    <!-- <div class="p-ppg-tags">
            < ?php echo $bufTags ?>
          </div> -->

                    <div class="p-ppg-main">
                        <?php
                        if (count($cursor['hits']['hits']) == 0) {
                            echo '<div class="u-mt-20 u-mb-20">Não foi encontrado nenhum Programa de Pós-Graduação.</div>';
                        } else {
                            ListPPGs::listAll($cursor['hits']['hits']);
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include('inc/footer.php'); ?>
</body>

</html>