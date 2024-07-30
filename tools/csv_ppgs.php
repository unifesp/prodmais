<?php

/* Exibir erros - Use somente durante os testes */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../inc/config.php';
require '../inc/functions.php';

if (isset($_FILES['file'])) {

    //echo "<pre>" . print_r($_FILES['file'], true) . "</pre>";

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 108192, "\t");



    foreach ($row as $key => $value) {
        if ($value == "COD_CAPES") {
            define("COD_CAPES", $key);
        }
        if ($value == "NOME_PPG") {
            define("NOME_PPG", $key);
        }
        if ($value == "INSTITUICAO") {
            define("INSTITUICAO", $key);
        }
        if ($value == "NIVEL") {
            define("NIVEL", $key);
        }
        if ($value == "CONCEITO_CAPES") {
            define("CONCEITO_CAPES", $key);
        }
        if ($value == "NOME_CAMPUS") {
            define("NOME_CAMPUS", $key);
        }
        if ($value == "PPG_EMAIL") {
            define("PPG_EMAIL", $key);
        }
        if ($value == "NOME_COORDENADOR") {
            define("NOME_COORDENADOR", $key);
        }
        if ($value == "PPG_SITE") {
            define("PPG_SITE", $key);
        }
    }

    while (($row = fgetcsv($fh, 108192, "\t")) !== false) {
        if (!empty($row[COD_CAPES])) {
            $queryParams[] = 'ID_CURSO=' . $row[COD_CAPES] . '';
        }
        if (!empty($row[COD_CAPES])) {
            $queryParams[] = '&COD_CAPES=' . $row[COD_CAPES] . '';
        } else {
            $queryParams[] = '&COD_CAPES=';
        }
        if (!empty($row[NOME_PPG])) {
            $queryParams[] = '&NOME_PPG=' . $row[NOME_PPG] . '';
        } else {
            $queryParams[] = '&NOME_PPG=';
        }
        if (!empty($row[INSTITUICAO])) {
            $queryParams[] = '&NOME_INSTITUICAO=' . $row[INSTITUICAO] . '';
        } else {
            $queryParams[] = '&NOME_INSTITUICAO=';
        }
        if (!empty($row[NIVEL])) {
            $queryParams[] = '&NIVEL=' . $row[NIVEL] . '';
        } else {
            $queryParams[] = '&NIVEL=';
        }
        if (!empty($row[CONCEITO_CAPES])) {
            $queryParams[] = '&CONCEITO_CAPES=' . $row[CONCEITO_CAPES] . '';
        } else {
            $queryParams[] = '&CONCEITO_CAPES=';
        }
        if (!empty($row[NOME_CAMPUS])) {
            $queryParams[] = '&NOME_CAMPUS=' . $row[NOME_CAMPUS] . '';
        } else {
            $queryParams[] = '&NOME_CAMPUS=';
        }
        if (!empty($row[PPG_EMAIL])) {
            $queryParams[] = '&PPG_EMAIL=' . $row[PPG_EMAIL] . '';
        } else {
            $queryParams[] = '&PPG_EMAIL=';
        }
        if (!empty($row[NOME_COORDENADOR])) {
            $queryParams[] = '&NOME_COORDENADOR=' . $row[NOME_COORDENADOR] . '';
        } else {
            $queryParams[] = '&NOME_COORDENADOR=';
        }
        if (!empty($row[PPG_SITE])) {
            $queryParams[] = '&PPG_SITE=' . $row[PPG_SITE] . '';
        } else {
            $queryParams[] = '&PPG_SITE=';
        }
        $queryParams[] = '&INI_PPG=';

        curlLattes($url_base, $queryParams);

        unset($queryParams);
        unset($row);
    }
    fclose($fh);
    echo '<script>window.location = \'ppgs.php?\'</script>';
}

function curlLattes($url_base, $queryParams)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/include_ppg.php');
    curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    var_dump($output);
    curl_close($ch);
}