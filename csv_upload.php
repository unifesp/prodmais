<?php

require 'inc/config.php';
require 'inc/functions.php';

if (isset($_FILES['file'])) {

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 108192, "\t");

    foreach ($row as $key => $value) {
        if ($value == "CPF") {
            define("CPF", $key);
        }
        if ($value == "COD_LATTES_16") {
            define("COD_LATTES_16", $key);
        }
        if ($value == "CARGO_REDUZIDO_VINCS") {
            define("CARGO_REDUZIDO_VINCS", $key);
        }
        if ($value == "CAMPUS_NOME") {
            define("CAMPUS_NOME", $key);
        }
        if ($value == "CAMARA_NOME") {
            define("CAMARA_NOME", $key);
        }
        if ($value == "SEXO") {
            define("SEXO", $key);
        }
        if ($value == "PPG_NOME (PROGRAMA)") {
            define("PPG_NOME", $key);
        }
        if ($value == "DESCRICAO") {
            define("DESCRICAO", $key);
        }
        if ($value == "DESC_GESTORA") {
            define("DESC_GESTORA", $key);
        }
        if ($value == "DESC_ACADEMICA") {
            define("DESC_ACADEMICA", $key);
        }
        if ($value == "DESC_DEPTO") {
            define("DESC_DEPTO", $key);
        }
        if ($value == "DESC_DIV") {
            define("DESC_DIV", $key);
        }
        if ($value == "DESC_SEC") {
            define("DESC_SEC", $key);
        }
        if ($value == "DESCR_CURSO") {
            define("DESCR_CURSO", $key);
        }
    }

    while (($row = fgetcsv($fh, 108192, "\t")) !== false) {
        $paramsFunction["COD_LATTES_16"] = $row[COD_LATTES_16];

        if ($row[CPF] == "000000000000") {
            if (!empty($row[COD_LATTES_16])) {
                $IDLattes = $row[$rowNum[COD_LATTES_16]];
            }
        } else {
            $url = 'http://200.133.208.25/api/proxy_cpf/'.substr($row[CPF], 1, 11).'';
            $IDLattes = file_get_contents('http://200.133.208.25/api/proxy_cpf/'.substr($row[CPF], 1, 11).'');
        }

        if (!empty($_REQUEST["tag"])) {
            $queryParams[] = '&tag=' . $_REQUEST["tag"] . '';
        } else {
            $queryParams[] = '&tag=';
        }
        if (!empty($row[CAMARA_NOME])) {
            $queryParams[] = '&unidade=' . $row[CAMARA_NOME] . '';
        } else {
            $queryParams[] = '&unidade=';
        }

        if (!empty($row[DESC_DEPTO])) {
            $queryParams[] = '&departamento=' . $row[DESC_DEPTO] . '';
        } else {
            $queryParams[] = '&departamento=';
        }

        if (!empty($row[CARGO_REDUZIDO_VINCS])) {
            $queryParams[] = '&tipvin=' . $row[CARGO_REDUZIDO_VINCS] . '';
        } else {
            $queryParams[] = '&tipvin=';
        }

        if (!empty($row[DESC_DIV])) {
            $queryParams[] = '&divisao=' . $row[DESC_DIV] . '';
        } else {
            $queryParams[] = '&divisao=';
        }

        if (!empty($row[DESC_SEC])) {
            $queryParams[] = '&secao=' . $row[DESC_SEC] . '';
        } else {
            $queryParams[] = '&secao=';
        }

        if (!empty($row[PPG_NOME])) {
            $queryParams[] = '&ppg_nome=' . $row[PPG_NOME] . '';
        } else {
            $queryParams[] = '&ppg_nome=';
        }

        if (!empty($row[SEXO])) {
            $queryParams[] = '&genero=' . $row[SEXO] . '';
        } else {
            $queryParams[] = '&genero=';
        }

        if (!empty($r["_source"]["desc_nivel"][0])) {
            $queryParams[] = '&desc_nivel=' . $r['_source']['desc_nivel'][0] . '';
        } else {
            $queryParams[] = '&desc_nivel=';
        }

        if (!empty($row[DESCR_CURSO])) {
            $queryParams[] = '&desc_curso=' . $row[DESCR_CURSO] . '';
        } else {
            $queryParams[] = '&desc_curso=';
        }

        if (!empty($row[CAMPUS_NOME])) {
            $queryParams[] = '&campus=' . $row[CAMPUS_NOME] . '';
        } else {
            $queryParams[] = '&campus=';
        }

        if (!empty($row[DESC_GESTORA])) {
            $queryParams[] = '&desc_gestora=' . $row[DESC_GESTORA] . '';
        } else {
            $queryParams[] = '&desc_gestora=';
        }
        $queryParams[] = '&numfuncional='. $IDLattes. '';

        if (isset($IDLattes)) {
            curlLattes($url_base, $IDLattes, $queryParams);
        }
        unset($queryParams);
        unset($row);
    }
    fclose($fh);
}

function curlLattes($url_base, $IDLattes, $queryParams) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ''. $url_base .'/import_lattes_to_elastic_dedup.php?lattesID=' . $IDLattes . '');
    curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    var_dump($output);
    curl_close($ch);
}