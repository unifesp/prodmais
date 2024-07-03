<?php

require '../../inc/config.php';
require '../../inc/functions.php';

/* Exibir erros - Use somente durante os testes */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Abre o arquivo qualis.tsv para leitura
$arquivo = fopen("qualis2017-2020-dedup.tsv", "r");

// Verifica se o arquivo foi aberto com sucesso
if ($arquivo !== FALSE) {
    // Lê cada linha do arquivo
    while (($linha = fgetcsv($arquivo, 0, "\t")) !== FALSE) {
        // Atribui cada coluna a uma variável
        $issn = $linha[0];
        $titulo = $linha[1];
        $area = $linha[2];
        $extrato = $linha[3];


        // Gera o doc para inclusão no elasticsearch
        $doc["doc"]['issn'] = $issn;
        $doc["doc"]['titulo'] = $titulo;
        $doc["doc"]['area'] = explode('|', $area);
        $doc["doc"]['extrato'] = $extrato;
        $doc["doc_as_upsert"] = true;

        $result_upsert = Elasticsearch::update($issn, $doc, 'qualis');
        //print_r($result_upsert);
    }
    // Fecha o arquivo
    fclose($arquivo);
} else {
    echo "Erro ao abrir o arquivo.";
}
