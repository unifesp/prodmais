<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '-_exists_:doi -_exists_:openalex_doi';

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
echo "<a href='apis.php'>Retornar para a página de APIs</a><br/>";


foreach ($cursor["hits"]["hits"] as $r) {
    $openalex_result = openalexGetDOI($r["fields"]["name"][0]);
    unset($openalex_result["results"][0]["abstract_inverted_index"]);
    if ($openalex_result["meta"]["count"] === 1) {
        //$body["doc"]["openalex"] = $openalex_result["results"][0];
        // echo "Título original: " . $r["fields"]["name"][0];
        // echo '<br/>';
        // echo "Título obtido: " . $openalex_result["results"][0]["title"];
        // echo '<br/>';
        $sim = similar_text(strtolower($r["fields"]["name"][0]), strtolower($openalex_result["results"][0]["title"]), $perc);
        // echo "similarity: $sim ($perc %)\n";
        // echo '<br/>';
        // echo "DOI obtido: " . $openalex_result["results"][0]["doi"];
        // echo '<br/>';
        //var_dump($openalex_result["results"]);
        //echo '<br/>';
        if ($perc > 90) {
            $body["doc"]["openalex"] = $openalex_result["results"][0];
            if (!is_null($openalex_result["results"][0]["doi"])) {
                $body["doc"]["doi"] = str_replace("https://doi.org/", "", $openalex_result["results"][0]["doi"]);;
            }
            if (isset($openalex_result["results"][0]['referenced_works'])) {
                $body["doc"]["openalex_referenced_works"] = array();
                $i = 0;
                foreach ($openalex_result["results"][0]['referenced_works'] as $referenced_work) {
                    $openalex_result_referenced = openalexAPIID(str_replace("https://openalex.org/", "", $referenced_work), $client);
                    $body["doc"]["openalex_referenced_works"][$i]['name'] = $openalex_result_referenced['title'];
                    $body["doc"]["openalex_referenced_works"][$i]['datePublished'] = (string)$openalex_result_referenced['publication_year'];
                    $body["doc"]["openalex_referenced_works"][$i]['authorships'] = $openalex_result_referenced['authorships'];
                    $body["doc"]["openalex_referenced_works"][$i]['language'] = $openalex_result_referenced['language'];
                    $body["doc"]["openalex_referenced_works"][$i]['source'] = $openalex_result_referenced['primary_location']['source']['display_name'];
                    $i++;
                }
            }
            $body["doc"]["openalex_doi"]["empty"] = false;
            $body["doc_as_upsert"] = true;
        } else {
            $body["doc"]["openalex_doi"]["empty"] = true;
            $body["doc_as_upsert"] = true;
        }

        // echo '<br/><br/>';
        // echo "<pre>".print_r($body, true)."</pre>";
        // echo '<br/>';
        $upsert_openalex = Elasticsearch::update($r["_id"], $body);
        //var_dump($upsert_openalex);
    } else {
        //echo "Não recuperou doi";
        $body["doc"]["openalex_doi"]["empty"] = true;
        $body["doc_as_upsert"] = true;
        $upsert_openalex = Elasticsearch::update($r["_id"], $body);
        //echo '<br/>';
        //var_dump($upsert_openalex);
    }
    unset($openalex_result);
    unset($body);
}