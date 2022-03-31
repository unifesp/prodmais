<!DOCTYPE html>
<?php

    require '../inc/config.php';
    require '../inc/functions.php';

    $query["query"]["bool"]["must_not"][0]["term"]["doi.keyword"] = "";
    $query["query"]["bool"]["must_not"][1]["query_string"]["query"] = "_exists_:ExternalData.crossref";
    

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

        $clientCrossref = new RenanBr\CrossRefClient();
        $clientCrossref->setUserAgent('GroovyBib/1.1 (https://unifesp.br/prodmais/; mailto:tiago.murakami@unifesp.br)');
        $exists = $clientCrossref->exists('works/'.$r["_source"]["doi"].'');
        
        if ($exists == true) {

            $work = $clientCrossref->request('works/'.$r["_source"]["doi"].'');
            echo "<br/><br/>";
            $body["doc"]["ExternalData"]["crossref"] = $work;
            $body["doc_as_upsert"] = true;            
            unset($body["doc"]["ExternalData"]["crossref"]["message"]["assertion"]);
            //var_dump($body);
            $resultado_crossref = Elasticsearch::update($r["_id"], $body);
            print_r($resultado_crossref);
            sleep(2);
            ob_flush();
            flush();

        } else {
            $body["doc"]["ExternalData"]["crossref"]["notFound"] = true;
            unset($body["doc"]["ExternalData"]["crossref"]["notFound"]["message"]["assertion"]);
            $body["doc_as_upsert"] = true;
            $resultado_crossref = Elasticsearch::update($r["_id"], $body);
            print_r($resultado_crossref);
            sleep(2);
            ob_flush();
            flush();
        }

    }
?>