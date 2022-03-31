<!DOCTYPE html>
<?php
    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    require 'inc/config.php'; 
    require 'inc/functions.php'; 

    $query["query"]["query_string"]["query"] = "-_exists_:match.tag AND source:\"Base Lattes\"";
    $query['sort'] = [
        ['datePublished.keyword' => ['order' => 'desc']],
    ];      

    $params = [];
    $params["index"] = $index;
    $params["size"] = 5000;
    $params["body"] = $query;

    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"]["value"];

    echo 'Registros faltantes: '.$total.'';
    echo '<br/><br/>';

    foreach ($cursor["hits"]["hits"] as $r) {

        //print_r($r);
        unset($doc["doc"]["match"]);
        $doc["doc"]["match"]["tag"][] = "Lattes";
        $doc["doc_as_upsert"] = true;
        $sysno = $r["_id"];
        $result_elastic = Elasticsearch::update($sysno, $doc);
        print_r($result_elastic); 

    }

?>