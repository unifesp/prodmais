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
        if ($value == "EMAIL") {
            define("EMAIL", $key);
        }
        if ($value == "ETNIA") {
            define("ETNIA", $key);
        }
        if ($value == "DIVISAO") {
            define("DIVISAO", $key);
        }
        if ($value == "SECAO") {
            define("SECAO", $key);
        }
        if ($value == "UNIDADE") {
            define("UNIDADE", $key);
        }
        if ($value == "DEPARTAMENTO") {
            define("DEPARTAMENTO", $key);
        }
        if ($value == "NUMFUNCIONAL") {
            define("NUMFUNCIONAL", $key);
        }
        if ($value == "TAG") {
            define("TAG", $key);
        }
        if ($value == "NIVEL") {
            define("NIVEL", $key);
        }
        if ($value == "CURSO") {
            define("CURSO", $key);
        }
        if ($value == "ANO_INGRESSO") {
            define("ANO_INGRESSO", $key);
        }
        if ($value == "CAMPUS") {
            define("CAMPUS", $key);
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
        if (!empty($row[EMAIL])) {
            $queryParams[] = '&email=' . $row[EMAIL] . '';
        } else {
            $queryParams[] = '&email=';
        }
        if (!empty($row[ETNIA])) {
            $queryParams[] = '&etnia=' . $row[ETNIA] . '';
        } else {
            $queryParams[] = '&etnia=';
        }
        if (!empty($row[DIVISAO])) {
            $queryParams[] = '&divisao=' . $row[DIVISAO] . '';
        } else {
            $queryParams[] = '&divisao=';
        }
        if (!empty($row[SECAO])) {
            $queryParams[] = '&secao=' . $row[SECAO] . '';
        } else {
            $queryParams[] = '&secao=';
        }
        if (!empty($row[UNIDADE])) {
            $queryParams[] = '&unidade=' . $row[UNIDADE] . '';
        } else {
            $queryParams[] = '&unidade=';
        }
        if (!empty($row[DEPARTAMENTO])) {
            $queryParams[] = '&departamento=' . $row[DEPARTAMENTO] . '';
        } else {
            $queryParams[] = '&departamento=';
        }
        if (!empty($row[NUMFUNCIONAL])) {
            $queryParams[] = '&numfuncional=' . $row[NUMFUNCIONAL] . '';
        } else {
            $queryParams[] = '&numfuncional=';
        }
        if (!empty($row[TAG])) {
            $queryParams[] = '&tag=' . $row[TAG] . '';
        } else {
            $queryParams[] = '&tag=';
        }
        if (!empty($row[NIVEL])) {
            $queryParams[] = '&desc_nivel=' . $row[NIVEL] . '';
        } else {
            $queryParams[] = '&desc_nivel=';
        }
        if (!empty($row[CURSO])) {
            $queryParams[] = '&desc_curso=' . $row[CURSO] . '';
        } else {
            $queryParams[] = '&desc_curso=';
        }
        if (!empty($row[ANO_INGRESSO])) {
            $queryParams[] = '&ano_ingresso=' . $row[ANO_INGRESSO] . '';
        } else {
            $queryParams[] = '&ano_ingresso=';
        }
        if (!empty($row[CAMPUS])) {
            $queryParams[] = '&campus=' . $row[CAMPUS] . '';
        } else {
            $queryParams[] = '&campus=';
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
    curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/import_lattes_to_elastic_dedup.php?lattes_id=' . $IDLattes . '');
    curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    var_dump($output);
    curl_close($ch);
}
