<!DOCTYPE html>
<?php
    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    require 'inc/config.php'; 
    require 'inc/functions.php'; 

    $query["query"]["query_string"]["query"] = "-_exists_:doi AND -_exists_:corrigedoi";
    $query['sort'] = [
        ['datePublished.keyword' => ['order' => 'desc']],
    ];      

    $params = [];
    $params["index"] = $index;
    $params["type"] = $type;
    $params["size"] = 100;
    $params["_source"] = ["doi","match.tag","name","author","datePublished"];   
    $params["body"] = $query;

    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    echo 'Registros faltantes: '.$total.'';
    echo '<br/><br/>';

    foreach ($cursor["hits"]["hits"] as $record) {
        $name = str_replace('"', '', $record["_source"]["name"]);
        $name = str_replace('\\', '', $name);
        $author_name = "";
        corrigedoi($name, $author_name, $record["_source"]["datePublished"], $record["_id"], $record["_source"]["match"]["tag"]);
    }

    function corrigedoi($title, $author_name, $year, $original_id, $matchTagArray) 
    {
        global $index;
        global $type;
        global $client;
        
        $query = '
        {
            "min_score": 10,
            "query":{
                "bool": {
                    "should": [	
                        {
                            "multi_match" : {
                                "query":      "'.str_replace('"', '', $title).'",
                                "type":       "cross_fields",
                                "fields":     [ "name" ],
                                "minimum_should_match": "90%" 
                             }
                        },                        	    
                        {
                            "multi_match" : {
                                "query":      "'.$year.'",
                                "type":       "best_fields",
                                "fields":     [ "datePublished" ],
                                "minimum_should_match": "75%" 
                            }
                        }
                    ],
                    "must_not" : {
                        "term" : { "sysno" : "'.$original_id.'" }
                      },                    
                    "minimum_should_match" : 1              
                }
            }
        }
        ';        
        
        
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["size"] = 1000;
        $params["body"] = $query;    
        $cursor = $client->search($params);
        $total = $cursor["hits"]["total"];         

        foreach ($cursor["hits"]["hits"] as $r) {
            if (isset($r["_source"]["doi"])) {
                $doc["doc"]["doi"] = $r["_source"]["doi"];                
            }          
        }
        if (isset($doc["doc"]["doi"])) {
            $doc["doc"]["corrigedoi"] = "Sim";
        } else {
            $doc["doc"]["corrigedoi"] = "Não";
        }
        $doc["doc_as_upsert"] = true;       
        $result_elastic = Elasticsearch::update($original_id, $doc);
    }
   
    header("Refresh: 0");

?>