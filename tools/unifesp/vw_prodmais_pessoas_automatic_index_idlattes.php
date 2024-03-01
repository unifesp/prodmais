<?php

// Include essencial files
require '../../inc/config.php';

$conexao = oci_connect($oracle_username, $oracle_password, 'orascan.epm.br/prod', 'AL32UTF8');

if (!$conexao) {
    $erro = oci_error();
    trigger_error(htmlentities($erro['message'], ENT_QUOTES), E_USER_ERROR);
    exit;
}

$alter = "
    ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/RR'
";


$sql = "
    SELECT * FROM CONS_OLAP.VW_PRODMAIS_PESSOAS
";

$stidalter = oci_parse($conexao, $alter) or die("erro");
oci_execute($stidalter);
$stid = oci_parse($conexao, $sql) or die("erro");
oci_execute($stid);
while (($row = oci_fetch_array($stid)) != false) {

    if ($row["ORIENT_LATTES16"] != null) {
        $DataAtualizacaoLattes = file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $row["ORIENT_LATTES16"] . '');
        $DataAtualizacaoLattes_formatted = '' . substr($DataAtualizacaoLattes, 6, 4) . '-' . substr($DataAtualizacaoLattes, 3, 2) . '';

        echo "Processando " . $row['ORIENT_LATTES16'];
        echo "<br/><br/>";

        $queryParams[] = '&tag=';
        $queryParams[] = '&campus=' . $row["CAMPUS_NOME"] . '';
        $queryParams[] = '&unidade=' . $row["CAMARA_NOME"] . '';
        $queryParams[] = '&ppg_nome=' . $row["PPG_NOME"] . '';
        $queryParams[] = '&tipvin=' . $row["VINCULO"] . '';
        $queryParams[] = '&genero=' . $row["SEXO"] . '';
        $queryParams[] = '&dt_atual_lattes=' . $DataAtualizacaoLattes_formatted . '';

        $queryParams[] = '&email=' . $row["EMAIL"] . '';
        if (isset($row["ORIENT_GOOGLE_CITATION"])) {
            $queryParams[] = '&google_citation=' . $row["ORIENT_GOOGLE_CITATION"] . '';
        }
        if (isset($row["ORIENT_RESEARCHER"])) {
            $queryParams[] = '&researcherid=' . $row["ORIENT_RESEARCHER"] . '';
        }
        $queryParams[] = '&lattes10=' . $row["ORIENT_LATTES10"] . '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/import_lattes_to_elastic_dedup.php?lattesID=' . $row['ORIENT_LATTES16'] . '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        unset($queryParams);
    }
}

oci_close($conexao);