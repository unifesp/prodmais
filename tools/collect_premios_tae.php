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
    WHERE VINCULO = 'TAE_UNIFESP'
";

$stid = oci_parse($conexao, $consulta_pg_orient) or die("erro");
oci_execute($stid);
while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {

    $IDLattes = file_get_contents('http://200.133.208.25/api/proxy_cpf/' . $row["CPF"] . '');


    if (strlen($IDLattes) == 16) {

        shell_exec('php /var/www/html/prodmais/premios.php '. $IDLattes.' >> premios.txt');
        
    } elseif ($row["COD_LATTES_16"] != null) {

        shell_exec('php /var/www/html/prodmais/premios.php '. $row['COD_LATTES_16'] .' >> premios.txt');

    }
    unset($IDLattes);
}

oci_close($conexao);
