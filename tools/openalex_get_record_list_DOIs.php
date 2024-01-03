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

$params["size"] = 10;

$cursor = $client->search($params);

echo "Resultado: $total<br/><br/>";
echo "<a href='apis.php'>Retornar para a página de APIs</a><br/>";


$listofdois = [];
foreach ($cursor["hits"]["hits"] as $r) {
    $listofdois[] =  'https://doi.org/' . $r["_source"]["doi"];
    $ids[$r["_source"]["doi"]] = $r["_id"];
}
$complete_list_dois = implode('|', $listofdois);

print("<pre>" . print_r($ids, true) . "</pre>");

$openalex_result = openalexAPIGetListOfDOIs($complete_list_dois);

foreach ($openalex_result["results"] as $result) {
    unset($result['abstract_inverted_index']);
    //print("<pre>" . print_r($result, true) . "</pre>");
    $doi = str_replace('https://doi.org/', '', $result["doi"]);
    $body["doc"]["openalex"] = $result;

    if (isset($result['referenced_works'])) {
        $body["doc"]["openalex_referenced_works"] = array();
        $i = 0;
        foreach ($result['referenced_works'] as $referenced_work) {
            $openalex_result_referenced = openalexAPIID(str_replace("https://openalex.org/", "", $referenced_work), $client);
            //var_dump($openalex_result_referenced);
            //print("<pre>".print_r($openalex_result_referenced, true)."</pre>");
            $body["doc"]["openalex_referenced_works"][$i]['name'] = $openalex_result_referenced['title'];
            $body["doc"]["openalex_referenced_works"][$i]['datePublished'] = (string)$openalex_result_referenced['publication_year'];
            $body["doc"]["openalex_referenced_works"][$i]['authorships'] = $openalex_result_referenced['authorships'];
            $body["doc"]["openalex_referenced_works"][$i]['language'] = $openalex_result_referenced['language'];
            $body["doc"]["openalex_referenced_works"][$i]['source'] = $openalex_result_referenced['primary_location']['source']['display_name'];
            $i++;
        }
    }
    $body["doc_as_upsert"] = true;
    //print("<pre>" . print_r($body, true) . "</pre>");
    $upsert_openalex = Elasticsearch::update($ids[$doi], $body);
    //print("<pre>" . print_r($upsert_openalex, true) . "</pre>");
    ob_flush();
    flush();
}
unset($listofdois);