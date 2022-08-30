<!DOCTYPE html>
<?php

    require '../inc/config.php';
    require '../inc/functions.php';

    
    $query["query"]["bool"]["must"]["exists"]["field"] = "doi";
    $query["query"]["bool"]["must_not"]["term"]["openalex"] = true;



    $params = [];
    $params["index"] = $index;
    $params["body"] = $query; 

    $cursorTotal = $client->count($params);
    $total = $cursorTotal["count"];

    echo "Registros restantes: $total<br/><br/>";

    $params["size"] = 500;
    $params["from"] = 0;
    $cursor = $client->search($params);

    foreach ($cursor["hits"]["hits"] as $r) {   


        $url = 'https://api.openalex.org/works/https://doi.org/' . $r["_source"]["doi"] . '?mailto=tiago.murakami@unifesp.br';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        
        if ($httpcode == 200) {

            //var_dump($r);

            $work = file_get_contents($url);
            //$work_converted = json_decode($work);
            $body["doc"]["ExternalData"]["openalex"] = json_decode($work, true);
            $body["doc"]["openalex"] = true;
            $body["doc"]["counts_by_year"] = $body["doc"]["ExternalData"]["openalex"]["counts_by_year"];
            $body["doc_as_upsert"] = true;
            //echo "<pre>".print_r($body, true)."</pre>";     
            unset($body["doc"]["ExternalData"]["openalex"]["abstract_inverted_index"]);
            //var_dump($body);
            $resultado_openalex = Elasticsearch::update($r["_id"], $body);
            print_r($resultado_openalex);
            //sleep(2);
            ob_flush();
            flush();

        } else {

            $body["doc"]["openalex"] = true;
            //unset($body["doc"]["ExternalData"]["openalex"]["notFound"]["message"]["assertion"]);
            $body["doc_as_upsert"] = true;
            $resultado_openalex = Elasticsearch::update($r["_id"], $body);
            print_r($resultado_openalex);
            //sleep(2);
            ob_flush();
            flush();
        }

    }
?>