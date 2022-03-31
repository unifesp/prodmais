<!DOCTYPE html>
<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';

$query["query"]["query_string"]["query"] = "datePublished:[2013 TO 2016] AND source:\"Base Lattes\"";
$query['sort'] = [
    ['datePublished.keyword' => ['order' => 'desc']],
];

$params = [];
$params["index"] = "coletaprod";
$params["type"] = "trabalhos";
$params["size"] = 50;
$params["scroll"] = "30s";
$params["body"] = $query;

$cursor = $client_coletaprod->search($params);
$total = $cursor["hits"]["total"];

foreach ($cursor["hits"]["hits"] as $r) {
    $doc["doc"] = $r["_source"];
    $doc["doc"]["source"] = "Lattes";
    unset($doc["doc"]["match"]);
    $doc["doc"]["match"]["tag"][] = "Lattes";
    $doc["doc_as_upsert"] = true;
    $result_elastic = Elasticsearch::update($r["_id"], $doc);
}

while (isset($cursor['hits']['hits']) && count($cursor['hits']['hits']) > 0) {
    $scroll_id = $cursor['_scroll_id'];

    $cursor = $client_coletaprod->scroll([
        "scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
        "scroll" => "30s"           // and the same timeout window
        ]
    );

    foreach ($cursor["hits"]["hits"] as $r) {
        $doc["doc"] = $r["_source"];
        $doc["doc"]["source"] = "Lattes";
        unset($doc["doc"]["match"]);
        $doc["doc"]["match"]["tag"][] = "Lattes";
        $doc["doc_as_upsert"] = true;
        $result_elastic = Elasticsearch::update($r["_id"], $doc);
    }

}

?>
