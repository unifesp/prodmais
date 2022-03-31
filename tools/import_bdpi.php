<!DOCTYPE html>
<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';

$query["query"]["query_string"]["query"] = "datePublished:[2013 TO 2016] AND base:\"Produção científica\"";
$query['sort'] = [
    ['datePublished.keyword' => ['order' => 'desc']],
];

$params = [];
$params["index"] = "bdpi";
$params["type"] = "producao";
$params["size"] = 50;
$params["scroll"] = "30s";
$params["_source"] = ["doi","name","author","datePublished","type","language","country","isPartOf","unidadeUSP","releasedEvent","USP.titleSearchCrossrefDOI"];
$params["body"] = $query;

$cursor = $client_bdpi->search($params);

foreach ($cursor["hits"]["hits"] as $r) {
    $doc["doc"] = $r["_source"];
    $doc["doc"]["source"] = "BDPI";
    $doc["doc"]["tipo"] = $r["_source"]["type"];
    $doc["doc"]["type"] = "Work";
    unset($doc["doc"]["match"]["tag"]);
    $doc["doc"]["match"]["tag"][] = "BDPI";
    if (isset($r["_source"]["USP"]["titleSearchCrossrefDOI"])) {
        $doc["doc"]["doi"] = $r["_source"]["USP"]["titleSearchCrossrefDOI"];
    }
    $doc["doc_as_upsert"] = true;
    $result_elastic = Elasticsearch::update($r["_id"], $doc);
    //print_r($result_elastic);
    //echo "<br/><br/><br/>";
}

while (isset($cursor['hits']['hits']) && count($cursor['hits']['hits']) > 0) {
    $scroll_id = $cursor['_scroll_id'];

    $cursor = $client_bdpi->scroll([
        "scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
        "scroll" => "30s"           // and the same timeout window
        ]
    );

    foreach ($cursor["hits"]["hits"] as $r) {
        $doc["doc"] = $r["_source"];
        $doc["doc"]["source"] = "BDPI";
        $doc["doc"]["tipo"] = $r["_source"]["type"];
        $doc["doc"]["type"] = "Work";
        unset($doc["doc"]["match"]["tag"]);
        $doc["doc"]["match"]["tag"][] = "BDPI";
        if (isset($r["_source"]["USP"]["titleSearchCrossrefDOI"])) {
            $doc["doc"]["doi"] = $r["_source"]["USP"]["titleSearchCrossrefDOI"];
        }
        $doc["doc_as_upsert"] = true;
        $result_elastic = Elasticsearch::update($r["_id"], $doc);
        //print_r($result_elastic);
        //echo "<br/><br/><br/>";
    }

}

?>
