<?php

//$file="sitemaps.txt";
//header('Content-type: text/plain');
//header("Content-Disposition: attachment; filename=$file");

// Set directory to ROOT
chdir('../');
// Include essencial files
include('inc/config.php');
include('inc/functions.php');

$query["query"]["query_string"]["query"] = "*";

$params = [];
$params["index"] = $index_cv;
$params["size"] = 10000;
$params["_source_includes"] = ["_id"];
$params["body"] = $query;


$cursor = $client->search($params);

foreach ($cursor["hits"]["hits"] as $r) {
    $record_blob[] = '' . $url_base . ' /profile/ ' . $r['_id'] . '|';
}
foreach ($record_blob as $record) {
    $record_array = explode('|', $record);
    echo implode("<br/>", $record_array);
}