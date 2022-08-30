<?php
// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';

$query["query"]["query_string"]["fields"] = ['nome_completo'];
$query["query"]["query_string"]["query"] = '*'.$_REQUEST["query"].'*';

$params = [];
$params["index"] = $index_cv;
$params["size"] = 10;
$params["_source"] = ["nome_completo"];
$params["body"] = $query;

$cursor = $client->search($params);
header('Content-type: application/json');
echo json_encode($cursor, true);
