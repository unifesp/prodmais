<?php

// Set directory to ROOT
chdir('../../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';


$conexao = oci_connect($oracle_username, $oracle_password, 'orascan.epm.br/prod', 'AL32UTF8');

if (!$conexao) {
    $erro = oci_error();
    trigger_error(htmlentities($erro['message'], ENT_QUOTES), E_USER_ERROR);
    exit;
}


$consulta_tae = "
    SELECT * FROM CONS_OLAP.unifesp_coleta_prod
    WHERE VINCULO = 'TAE_UNIFESP'
";

$stid = oci_parse($conexao, $consulta_tae) or die("erro");
oci_execute($stid);
while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {

    $IDLattes = file_get_contents('http://200.133.208.25/api/proxy_cpf/' . $row["CPF"] . '');

    if (strlen($IDLattes) == 16) {

        $DataAtualizacaoLattes = file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $IDLattes . '');
        $DataAtualizacaoLattes_formatted = '' . substr($DataAtualizacaoLattes, 6, 4) . '-' . substr($DataAtualizacaoLattes, 3, 2) . '';

        echo "Processando $IDLattes";
        echo "<br/><br/>";

        $queryParams[] = '&tag=';
        $queryParams[] = '&unidade=' . $row["DESC_ACADEMICA"] . '';
        $queryParams[] = '&departamento=' . $row["DESC_DEPTO"] . '';
        $queryParams[] = '&tipvin=' . $row["CARGO_REDUZIDO"] . '';
        $queryParams[] = '&divisao=' . $row["DESC_DIV"] . '';
        $queryParams[] = '&secao=' . $row["DESC_SEC"] . '';
        $queryParams[] = '&ppg_nome=' . $row["PPG_NOME_PROGRAMA"] . '';
        $queryParams[] = '&genero=' . $row["GENERO"] . '';
        $queryParams[] = '&desc_nivel=' . $row["DESCRICAO_NIVEL"] . '';
        //$queryParams[] = '&desc_curso=' . $r['_source']['desc_curso'][0] . '';
        $queryParams[] = '&campus=' . $row["CAMPUS_DESCRICAO"] . '';
        $queryParams[] = '&desc_gestora=' . $row["DESC_GESTORA"] . '';
        $queryParams[] = '&dt_atual_lattes=' . $DataAtualizacaoLattes_formatted . '';
        $queryParams[] = '&dt_credenciamento=' . date("Y", strtotime($row["DT_CREDENC"])) . '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/import_lattes_to_elastic_dedup.php?lattesID=' . $IDLattes . '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        unset($queryParams);
    }
    unset($IDLattes);
}

oci_close($conexao);
