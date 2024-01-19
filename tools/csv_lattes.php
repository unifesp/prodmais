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
        if ($value == "COD_LATTES_16") {
            define("COD_LATTES_16", $key);
        }
        if ($value == "PPG_NOME") {
            define("PPG_NOME", $key);
        }
        if ($value == "TIPVIN") {
            define("TIPVIN", $key);
        }
        if ($value == "GENERO") {
            define("GENERO", $key);
        }
        if ($value == "INSTITUICAO") {
            define("INSTITUICAO", $key);
        }
    }

    while (($row = fgetcsv($fh, 108192, "\t")) !== false) {
        $paramsFunction["COD_LATTES_16"] = $row[COD_LATTES_16];


        if (!empty($row[COD_LATTES_16])) {
            $IDLattes = $row[COD_LATTES_16];
        }
        if (!empty($row[PPG_NOME])) {
            $queryParams[] = '&ppg_nome=' . $row[PPG_NOME] . '';
        } else {
            $queryParams[] = '&ppg_nome=';
        }
        if (!empty($row[TIPVIN])) {
            $queryParams[] = '&tipvin=' . $row[TIPVIN] . '';
        } else {
            $queryParams[] = '&tipvin=';
        }
        if (!empty($row[GENERO])) {
            $queryParams[] = '&genero=' . $row[GENERO] . '';
        } else {
            $queryParams[] = '&genero=';
        }
        if (!empty($row[INSTITUICAO])) {
            $queryParams[] = '&instituicao=' . $row[INSTITUICAO] . '';
        } else {
            $queryParams[] = '&instituicao=';
        }
        curlLattes($url_base, $IDLattes, $queryParams);

        unset($queryParams);
        unset($row);
    }
    fclose($fh);
}

function curlLattes($url_base, $IDLattes, $queryParams)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/lattes_xml_to_elastic_dedup.php?lattes_id=' . $IDLattes . '');
    curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    var_dump($output);
    curl_close($ch);
}