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

    //var_dump($row);

    $IDLattes = file_get_contents('http://200.133.208.25/api/proxy_cpf/' . $row["CPF"] . '');


    if (strlen($IDLattes) == 16) {

        $DataAtualizacaoLattes = file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $IDLattes . '');
        $DataAtualizacaoLattes_formatted = '' . substr($DataAtualizacaoLattes, 6, 4) . '-' . substr($DataAtualizacaoLattes, 3, 2) . '';

        echo "Processando $IDLattes";
        echo "<br/><br/>";

        $queryParams[] = '&tag=';
        $queryParams[] = '&campus=' . $row["CAMPUS_NOME"] . '';
        $queryParams[] = '&unidade=' . $row["CAMARA_NOME"] . '';
        $queryParams[] = '&ppg_nome=' . $row["PPG_NOME"] . '';
        $queryParams[] = '&tipvin=' . $row["VINCULO"] . '';
        $queryParams[] = '&genero=' . $row["SEXO"] . '';
        $queryParams[] = '&email=' . $row["EMAIL"] . '';

        if (isset($row["ORIENT_GOOGLE_CITATION"])) {
            $queryParams[] = '&google_citation=' . $row["ORIENT_GOOGLE_CITATION"] . '';
        }
        if (isset($row["ORIENT_RESEARCHER"])) {
            $queryParams[] = '&researcherid=' . $row["ORIENT_RESEARCHER"] . '';
        }        
        
        $queryParams[] = '&lattes10=' . $row["ORIENT_LATTES10"] . '';

        //$queryParams[] = '&departamento=' . $row["DESC_DEPTO"] . '';
        //$queryParams[] = '&divisao=' . $row["DESC_DIV"] . '';
        //$queryParams[] = '&secao=' . $row["DESC_SEC"] . '';
        //$queryParams[] = '&desc_nivel=' . $row["DESCRICAO_NIVEL"] . '';
        //$queryParams[] = '&desc_curso=' . $r['_source']['desc_curso'][0] . '';
        //$queryParams[] = '&desc_gestora=' . $row["DESC_GESTORA"] . '';
        //$queryParams[] = '&dt_atual_lattes=' . $DataAtualizacaoLattes_formatted . '';
        //$queryParams[] = '&dt_credenciamento=' . date("Y", strtotime($row["DT_CREDENC"])) . '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/import_lattes_to_elastic_dedup.php?lattesID=' . $IDLattes . '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        unset($queryParams);
        
    } elseif ($row["ORIENT_LATTES16"] != null) {
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
    unset($IDLattes);
}

oci_close($conexao);
