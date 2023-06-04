<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '_exists_:doi doi:1* -_exists_:openalex';

$params = [];
$params["index"] = $index;
$params["body"] = $query;

$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];

$params["size"] = $_GET["size"];

$cursor = $client->search($params);

echo "Resultado: $total";

foreach ($cursor["hits"]["hits"] as $r) {
    // //print("<pre>".print_r($r, true)."</pre>");
    // //print("<pre>".print_r($r["_source"]["doi"], true)."</pre>");    
    $openalex_result = openalexAPI($r["_source"]["doi"]);
    unset($openalex_result['abstract_inverted_index']);
    // //print("<pre>".print_r($openalex_result, true)."</pre>");
    if (empty($openalex_result)) {
        $body["doc"]["openalex"]['empty'] = true;
    } else {
        $body["doc"]["openalex"] = $openalex_result;
    }
    if (isset($openalex_result['referenced_works'])){
        $body["doc"]["openalex_referenced_works"] = array();
        foreach ($openalex_result['referenced_works'] as $referenced_work) {
            $openalex_result_referenced = openalexAPIID(str_replace("https://openalex.org/", "", $referenced_work));
            $body["doc"]["openalex_referenced_works"][] = $openalex_result_referenced['title'];
        }
    }
    $body["doc_as_upsert"] = true;
    //print("<pre>".print_r($body, true)."</pre>");
    $upsert_openalex = Elasticsearch::update($r["_id"], $body);
    //print("<pre>" . print_r($upsert_openalex, true) . "</pre>");
    ob_flush();
    flush();
}