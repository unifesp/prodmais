<?php

$file = "export_field.tsv";
header('Content-type: text/tab-separated-values; charset=utf-8');
header("Content-Disposition: attachment; filename=$file");
// Set directory to ROOT
chdir('../');
// Include essencial files
include 'inc/config.php';

if (!empty($_GET["field"])) {
    $query["query"]["bool"]["must"]["query_string"]["query"] = "*";
    $params = [];
    $params["index"] = $index;
    $params["size"] = 2;
    $params["scroll"] = "30s";
    $params["_source"] = ["_id", $_GET['field']];
    $params["body"] = $query;
    $cursor = $client->search($params);

    $total = $cursor["hits"]["total"];
    //$content[] = $_GET["field"];
    foreach ($cursor["hits"]["hits"] as $r) {
        unset($fields);
        $fieldArray = explode(".", $_GET["field"]);
        $count = count($fieldArray);
        if (isset($r["_source"][$fieldArray[0]])) {
            if ($count == 1) {
                $fields[] = $r["_source"][$fieldArray[0]];
            } elseif ($count == 2) {
                if(is_array($r["_source"][$fieldArray[0]])) {
                    foreach ($r["_source"][$fieldArray[0]] as $field) {
                        if (is_array($fieldArray[1])) {
                            $fields[] = $field[$fieldArray[1]];
                        }
                    }                  
                    $fields[] = $r["_source"][$fieldArray[0]][$fieldArray[1]];
                }           
            } elseif ($count == 3) {
                foreach ($r["_source"][$fieldArray[0]] as $field) {
                    $fields[] = $field[$fieldArray[1]][$fieldArray[2]];
                }                
            } else {
                foreach ($r["_source"][$fieldArray[0]] as $field) {
                    $fields[] = $field[$fieldArray[1]][$fieldArray[2]][$fieldArray[3]];
                }
            }
        }
        if (isset($fields)) {
            $content[] = implode("\n", $fields);
            unset($fields);
        }

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
            unset($fields);
            $fieldArray = explode(".", $_GET["field"]);
            $count = count($fieldArray);
            if (isset($r["_source"][$fieldArray[0]])) {
                if ($count == 1) {
                    $fields[] = $r["_source"][$fieldArray[0]];
                } elseif ($count == 2) {
                    if(isset($r["_source"][$fieldArray[0]])) {
                        foreach ($r["_source"][$fieldArray[0]] as $field) {
                            if (is_array($fieldArray[1])) {
                                $fields[] = $field[$fieldArray[1]];
                            }
                        }                  
                        $fields[] = $r["_source"][$fieldArray[0]][$fieldArray[1]];  
                    }              
                } elseif ($count == 3) {
                    foreach ($r["_source"][$fieldArray[0]] as $field) {
                        $fields[] = $field[$fieldArray[1]][$fieldArray[2]];
                    }                
                } else {
                    foreach ($r["_source"][$fieldArray[0]] as $field) {
                        $fields[] = $field[$fieldArray[1]][$fieldArray[2]][$fieldArray[3]];
                    }
                }
            }
            if (isset($fields)) {
                $content[] = implode("\n", $fields);
                unset($fields);
            }
        }
    }
    echo implode("\n", $content);
}


