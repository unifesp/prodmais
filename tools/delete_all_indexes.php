<?php

require '../inc/config.php';
require '../inc/functions.php';

$params = ['index' => $index];
$response = $client->indices()->delete($params);


$params = ['index' => $index_cv];
$response = $client->indices()->delete($params);

$params = ['index' => $index_ppg];
$response = $client->indices()->delete($params);


$params = ['index' => $index_projetos];
$response = $client->indices()->delete($params);


header("Location: $url_base");