<?php

// $file="tematresScrollResult.tsv";
// header('Content-type: text/tab-separated-values; charset=utf-8');
// header("Content-Disposition: attachment; filename=$file");

// Set directory to ROOT
chdir('../');
// Include essencial files
include('inc/config.php'); 
include('inc/functions.php');


//$field = $_GET["field"];
$body["query"]["bool"]["must"][]["query_string"]["query"] = "_exists_:EducationEvent.name";
$body["query"]["bool"]["must"][]["query_string"]["query"] = "-_exists_:tematres.EducationEvent.name";   


$result_get = Requests::getParser($_GET);
$query = $result_get['query'];
$limit = $result_get['limit'];
$page = $result_get['page'];
$skip = $result_get['skip'];

$params = [];
$params["index"] = $index;
$params["size"] = 10;
$params["scroll"] = "30s";
$params["body"] = $body;
$params["_source"] = ["_id","EducationEvent"];

$cursor = $client->search($params);

foreach ($cursor["hits"]["hits"] as $r) {
    //$content[] = var_dump($r);
    //echo "<pre>".print_r($r['_source'], true)."</pre>";
    //echo "<br/><br/>";

    $termCleaned = str_replace("&", "e", $r['_source']["EducationEvent"]["name"]);
    $result_tematres = Authorities::tematresQuery($termCleaned, $tematres_url);
    if ($result_tematres["foundTerm"] == "ND") {
        $body_upsert["doc"]["tematres"]["EducationEvent.name"] = false;
        $body_upsert["doc_as_upsert"] = true;        
    } else {
        $body_upsert["doc"]["EducationEvent"]["name"] = $result_tematres["foundTerm"];
        $body_upsert["doc"]["tematres"]["EducationEvent.name"] = true;
        $body_upsert["doc_as_upsert"] = true;    
    }
    $resultado_upsert = Elasticsearch::update($r["_id"], $body_upsert);
    unset($body_upsert);
    
    sleep(2);
}

while (isset($cursor['hits']['hits']) && count($cursor['hits']['hits']) > 0) {
    $scroll_id = $cursor['_scroll_id'];
    $cursor = $client->scroll(
        [
        "scroll_id" => $scroll_id,
        "scroll" => "30s"
        ]
    );

    foreach ($cursor["hits"]["hits"] as $r) {
        $termCleaned = str_replace("&", "e", $r['_source']["EducationEvent"]["name"]);
        $result_tematres = Authorities::tematresQuery($termCleaned, $tematres_url);    
        if ($result_tematres["foundTerm"] == "ND") {

            $body_upsert["doc"]["tematres"]["EducationEvent.name"] = false;
            $body_upsert["doc_as_upsert"] = true;
        
        } else {
            $body_upsert["doc"]["EducationEvent"]["name"] = $result_tematres["foundTerm"];
            $body_upsert["doc"]["tematres"]["EducationEvent.name"] = true;
            $body_upsert["doc_as_upsert"] = true;    
        }    
        $resultado_upsert = Elasticsearch::update($r["_id"], $body_upsert);
        unset($body_upsert);
        
        sleep(2);
    }
}