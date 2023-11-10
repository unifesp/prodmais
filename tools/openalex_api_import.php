<?php

require '../inc/config.php';
require '../inc/functions.php';

/* Exibir erros - Use somente durante os testes */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Parse URL to get query string

$openalex_url = $_POST['openalex_expression'];
$openalex_query = str_replace("https://api.openalex.org/works?", "", $openalex_url);
$url_parsed = parse_url($openalex_url);
parse_str($url_parsed['query'], $query_arr);
//print_r($query_arr);

// Get OpenAlex API data

$curl = curl_init();
// Set some options - we are passing in a useragent too here
curl_setopt_array(
    $curl,
    array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $openalex_url,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A'
    )
);
// Send the request & save response to $resp
$resp = curl_exec($curl);
$data = json_decode($resp, true);

//echo "<pre>" . print_r($data, true) . "</pre>";

// Get data from meta

$number_of_results = $data['meta']['count'];
$number_of_pages = intdiv($number_of_results, $data['meta']['per_page']) + 1;


// Get all pages

for ($i_page = 1; $i_page <= $number_of_pages; $i_page++) {

    $openalex_url = "https://api.openalex.org/works?&page=" . $i_page . "&filter=" . $query_arr['filter'];

    //echo "<pre>" . print_r($openalex_url, true) . "</pre><br/><br/>";

    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $openalex_url,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A'
        )
    );
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    $data = json_decode($resp, true);

    foreach ($data['results'] as $openalex_record) {
        unset($openalex_record['abstract_inverted_index']);
        $doc["doc"]["type"] = "Work";
        $doc["doc"]["source"] = "OpenAlex";
        $doc["doc"]["tipo"] = $openalex_record['type'];
        $doc["doc"]["name"] = $openalex_record['title'];
        $i_authorship = 0;
        foreach ($openalex_record['authorships'] as $authorship) {
            $doc['doc']['author'][$i_authorship]['person']['name'] = $authorship['author']['display_name'];
            $i_authorship++;
        }
        $doc["doc"]["language"] = $openalex_record['language'];
        $doc["doc"]["datePublished"] = $openalex_record['publication_year'];
        if (isset($openalex_record['primary_location']['landing_page_url'])) {
            $doc["doc"]["url"] = $openalex_record['primary_location']['landing_page_url'];
        }

        if (filter_var($openalex_record['doi'], FILTER_VALIDATE_URL)) {
            $doi_parsed = parse_url($openalex_record['doi']);
            $doi = ltrim($doi_parsed['path'], '/');
            $doc["doc"]["doi"] = $doi;
        }




        if (isset($openalex_record['primary_location']['source']['display_name'])) {
            $doc["doc"]["isPartOf"]["name"] = $openalex_record['primary_location']['source']['display_name'];
        }
        if (isset($openalex_record['primary_location']['source']['issn_l'])) {
            $doc["doc"]["isPartOf"]["issn"] = $openalex_record['primary_location']['source']['issn_l'];
        }
        $doc["doc"]["pageStart"] = $openalex_record['biblio']['first_page'];
        $doc["doc"]["pageEnd"] = $openalex_record['biblio']['last_page'];
        $doc["doc"]["isPartOf"]["volume"] = $openalex_record['biblio']['volume'];
        $doc["doc"]["isPartOf"]["fasciculo"] = $openalex_record['biblio']['issue'];
        $doc["doc"]['openalex'] = $openalex_record;
        foreach ($openalex_record['keywords'] as $keyword) {
            $doc['doc']['about'][] = $keyword['keyword'];
        }
        $sha256 = hash('sha256', '' . $openalex_record['id'] . '');
        $doc["doc_as_upsert"] = true;
        $result_upsert = Elasticsearch::update($sha256, $doc);
        //echo "<pre>" . print_r($result_upsert, true) . "</pre><br/><br/>";
        //echo "<pre>" . print_r($doc, true) . "</pre><br/><br/>";
        unset($doc);
        unset($sha256);
        unset($openalex_record);
    }
}