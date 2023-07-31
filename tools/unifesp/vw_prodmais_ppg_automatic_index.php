<?php

// Include essencial files
require '../../inc/config.php';

$conexao = oci_connect($oracle_username, $oracle_password, 'orascan.epm.br/prod', 'AL32UTF8');

if (!$conexao) {
    $erro = oci_error();
    trigger_error(htmlentities($erro['message'], ENT_QUOTES), E_USER_ERROR);
    exit;
}

$consulta_pg_orient = "
    SELECT * FROM CONS_OLAP.VW_PRODMAIS_PPG
";

$stid = oci_parse($conexao, $consulta_pg_orient) or die("erro");
oci_execute($stid);
while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {

    //var_dump($row);

    $queryParams[] = '&ID_CURSO=' . $row["ID_CURSO"] . '';
    $queryParams[] = '&COD_CAPES=' . $row["COD_CAPES"] . '';
    $queryParams[] = '&NOME_CAMPUS=' . $row["NOME_CAMPUS"] . '';
    $queryParams[] = '&SIGLA_CAMARA=' . $row["SIGLA_CAMARA"] . '';
    $queryParams[] = '&NOME_CAMARA=' . $row["NOME_CAMARA"] . '';
    $queryParams[] = '&NOME_PPG=' . $row["NOME_PPG"] . '';
    $queryParams[] = '&INI_PPG=' . $row["INI_PPG"] . '';
    $queryParams[] = '&PPG_SITE=' . $row["PPG_SITE"] . '';
    $queryParams[] = '&PPG_EMAIL=' . $row["PPG_EMAIL"] . '';
    $queryParams[] = '&PRODMAIS_DSPACE=' . $row["PRODMAIS_DSPACE"] . '';
    $queryParams[] = '&PRODMAIS_DATAVERSE=' . $row["PRODMAIS_DATAVERSE"] . '';
    $queryParams[] = '&DT_INI_COORD=' . $row["DT_INI_COORD"] . '';
    $queryParams[] = '&CONCEITO_CAPES=' . $row["CONCEITO_CAPES"] . '';
    $queryParams[] = '&NIVEL=' . $row["NIVEL"] . '';
    $queryParams[] = '&NOME_COORDENADOR=' . $row["NOME_COORDENADOR"] . '';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/include_ppg.php');
    curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);

    unset($queryParams);
}

oci_close($conexao);
