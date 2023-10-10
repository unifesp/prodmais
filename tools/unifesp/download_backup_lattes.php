<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';


$conexao = oci_connect($oracle_username, $oracle_password, 'orascan.epm.br/prod', 'AL32UTF8');

if (!$conexao) {
    $erro = oci_error();
    trigger_error(htmlentities($erro['message'], ENT_QUOTES), E_USER_ERROR);
    exit;
}


$consulta_pg_orient = "
    SELECT * FROM CONS_OLAP.unifesp_coleta_prod
    WHERE DATA_ADMISSAO IS NOT NULL 
    AND DATA_DEMISSAO IS NULL
    AND VINCULO = 'PG ORIENTADORES'
    AND SIT_CRED IN ('Credenciamento Pleno','Recredenciamento Pleno')
    AND DT_TERMINO IS NULL
";

$stid = oci_parse($conexao, $consulta_pg_orient) or die("erro");
oci_execute($stid);
while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {

    $IDLattes = file_get_contents('http://200.133.208.25/api/proxy_cpf/' . $row["CPF"] . '');

    if (strlen($IDLattes) == 16) {

        $DataAtualizacaoLattes = file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $IDLattes . '');
        $DataAtualizacaoLattes_formatted = '' . substr($DataAtualizacaoLattes, 6, 4) . '-' . substr($DataAtualizacaoLattes, 3, 2) . '';

        echo "Processando $IDLattes";
        echo "<br/><br/>";

        $xmlLattes = file_get_contents('http://200.133.208.25/api/proxy/' . $IDLattes . '');

        $myfile = fopen("data/$IDLattes.xml", "w") or die("Unable to open file!");
        fwrite($myfile, $xmlLattes);
        fclose($myfile);
    }
    unset($IDLattes);
    unset($xmlLattes);
}

$consulta_docente = "
    SELECT DISTINCT CPF, VINCULO, NOME, GENERO, DATA_ADMISSAO, DATA_DEMISSAO, DESC_GESTORA, DESC_ACADEMICA, DESC_DEPTO, DESC_DIV, DESC_SEC, CARGO_REDUZIDO, CAMPUS_DESCRICAO, SECRETARIA_ACADEMICA, DATA_ATUALIZACAO_CARGA FROM CONS_OLAP.unifesp_coleta_prod
    WHERE DATA_ADMISSAO IS NOT NULL
    AND DATA_DEMISSAO IS NULL
    AND VINCULO = 'DOCENTE'
";

$stid_1 = oci_parse($conexao, $consulta_docente) or die("erro");
oci_execute($stid_1);
while (($row = oci_fetch_array($stid_1, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {

    $IDLattes = file_get_contents('http://200.133.208.25/api/proxy_cpf/' . $row["CPF"] . '');

    if (strlen($IDLattes) == 16) {

        $DataAtualizacaoLattes = file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $IDLattes . '');
        $DataAtualizacaoLattes_formatted = '' . substr($DataAtualizacaoLattes, 6, 4) . '-' . substr($DataAtualizacaoLattes, 3, 2) . '';

        echo "Processando $IDLattes";
        echo "<br/><br/>";

        $xmlLattes = file_get_contents('http://200.133.208.25/api/proxy/' . $IDLattes . '');

        $myfile = fopen("data/$IDLattes.xml", "w") or die("Unable to open file!");
        fwrite($myfile, $xmlLattes);
        fclose($myfile);
    }
    unset($IDLattes);
    unset($xmlLattes);

}

//exec('zip -r backup/archive'.date("Ymd").'.zip "data"');

oci_close($conexao);
