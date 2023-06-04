<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '-_exists_:doi -_exists_:openalex';

$params = [];
$params["index"] = $index;
$params["body"] = $query;

$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];



$params['body']['fields'][] = 'name';
$params['body']['_source'] = false;
$params["size"] = $_GET["size"];


$cursor = $client->search($params);

echo "Resultado: $total<br/><br/>";


foreach ($cursor["hits"]["hits"] as $r) {
    $openalex_result = openalexGetDOI($r['fields']['name'][0]);
    unset($openalex_result["results"][0]['abstract_inverted_index']);
    if ($openalex_result['meta']['count'] === 1) {
        //$body["doc"]["openalex"] = $openalex_result["results"][0];
        if (!is_null($openalex_result["results"][0]['doi'])) {
            $body["doc"]['doi'] = str_replace("https://doi.org/", "", $openalex_result["results"][0]['doi']);;
        }
    } else {        
        $body["doc"]["openalex"]['empty'] = true;
        $upsert_openalex = Elasticsearch::update($r["_id"], $body);
    }
    $body["doc_as_upsert"] = true;
    $upsert_openalex = Elasticsearch::update($r["_id"], $body);
}