<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '-_exists_:aurorasdg';

$params = [];
$params["index"] = $index;
$params["body"] = $query;

$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];

$params["size"] = $_GET["size"];

$cursor = $client->search($params);

echo "Resultado: $total<br/><br/>";
echo "<a href='apis.php'>Retornar para a página de APIs</a><br/>";

foreach ($cursor["hits"]["hits"] as $r) {
    // //print("<pre>".print_r($r, true)."</pre>");
    // //print("<pre>".print_r($r["_source"]["doi"], true)."</pre>");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://aurora-sdg.labs.vu.nl/classifier/classify/elsevier-sdg-multi');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"text": "' . $r["_source"]["name"] . '"}');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);

    curl_close($ch);

    // Sort response to get the most probable SDG

    $response = json_decode($response, true);
    usort($response["predictions"], function ($a, $b) {
        return $b['prediction'] <=> $a['prediction'];
    });

    $body["doc"]["aurorasdg"] = $response;
    $body["doc"]["aurorasdg"]["most_probable_sdg"] = $response["predictions"][0]["sdg"]["name"];
    $body["doc_as_upsert"] = true;

    //print("<pre>" . print_r($body, true) . "</pre>");
    $upsert_aurorasdg = Elasticsearch::update($r["_id"], $body);
    //print("<pre>" . print_r($upsert_openalex, true) . "</pre>");
    ob_flush();
    flush();
}