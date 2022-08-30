<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/config.php';
require 'inc/functions.php';

$query["query"]["query_string"]["query"] = "*";
$params = [];
$params["index"] = $index_cv;
$params["size"] = 50;
$params["scroll"] = "30s";
$params["_source"] = ['data_atualizacao', 'tag', 'unidade', 'departamento', 'tipvin', 'divisao', 'secao', 'ppg_nome', 'genero', 'desc_nivel', 'desc_curso', 'campus', 'desc_gestora'];
$params["body"] = $query;

$cursor = $client->search($params);

foreach ($cursor["hits"]["hits"] as $r) {
    if (!empty($r["_source"]["tag"])) {
        $queryParams[] = '&tag='.$r['_source']['tag'].'';
    }
    if (!empty($r["_source"]["unidade"][0])) {
        $queryParams[] = '&unidade=' . $r['_source']['unidade'][0] . '';
    }
    if (!empty($r["_source"]["departamento"][0])) {
        $queryParams[] = '&departamento=' . $r['_source']['departamento'][0] . '';
    }
    if (!empty($r["_source"]["tipvin"][0])) {
        $queryParams[] = '&tipvin=' . $r['_source']['tipvin'][0] . '';
    }
    if (!empty($r["_source"]["divisao"][0])) {
        $queryParams[] = '&divisao=' . $r['_source']['divisao'][0] . '';
    }
    if (!empty($r["_source"]["secao"][0])) {
        $queryParams[] = '&secao=' . $r['_source']['secao'][0] . '';
    }
    if (!empty($r["_source"]["ppg_nome"][0])) {
        $queryParams[] = '&ppg_nome=' . $r['_source']['ppg_nome'][0] . '';
    }
    if (!empty($r["_source"]["genero"][0])) {
        $queryParams[] = '&genero=' . $r['_source']['genero'][0] . '';
    }
    if (!empty($r["_source"]["desc_nivel"][0])) {
        $queryParams[] = '&desc_nivel=' . $r['_source']['desc_nivel'][0] . '';
    }
    if (!empty($r["_source"]["desc_curso"][0])) {
        $queryParams[] = '&desc_curso=' . $r['_source']['desc_curso'][0] . '';
    }
    if (!empty($r["_source"]["campus"][0])) {
        $queryParams[] = '&campus=' . $r['_source']['campus'][0] . '';
    }
    if (!empty($r["_source"]["desc_gestora"][0])) {
        $queryParams[] = '&desc_gestora=' . $r['_source']['desc_gestora'][0] . '';
    }
    //var_dump(implode('', $queryParams));

    $formattedRecordDate = date_format(date_create_from_format('Y-m', $r["_source"]["data_atualizacao"]), 'Y-m');
    $lattesDate = substr(file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $r["_id"] . ''), 0, 10);
    $formattedLattesDate = date_format(date_create_from_format('d/m/Y', $lattesDate), 'Y-m');
    if ($formattedRecordDate < $formattedLattesDate) {
        echo "<br/><br/>";
        echo "$formattedRecordDate é menor que $formattedLattesDate";
        echo "<br/><br/>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/import_lattes_to_elastic_dedup.php?lattesID=' . $r["_id"] . '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        echo "<br/><br/>";
        var_dump($output);
        echo "<br/><br/>";
        curl_close($ch);
    } else {
        echo "<br/><br/>";
        echo "$formattedRecordDate é maior ou igual a $formattedLattesDate";
        echo "<br/><br/>";
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
        if (!empty($r["_source"]["tag"])) {
            $queryParams[] = '&tag=' . $r['_source']['tag'] . '';
        }
        if (!empty($r["_source"]["unidade"][0])) {
            $queryParams[] = '&unidade=' . $r['_source']['unidade'][0] . '';
        }
        if (!empty($r["_source"]["departamento"][0])) {
            $queryParams[] = '&departamento=' . $r['_source']['departamento'][0] . '';
        }
        if (!empty($r["_source"]["tipvin"][0])) {
            $queryParams[] = '&tipvin=' . $r['_source']['tipvin'][0] . '';
        }
        if (!empty($r["_source"]["divisao"][0])) {
            $queryParams[] = '&divisao=' . $r['_source']['divisao'][0] . '';
        }
        if (!empty($r["_source"]["secao"][0])) {
            $queryParams[] = '&secao=' . $r['_source']['secao'][0] . '';
        }
        if (!empty($r["_source"]["ppg_nome"][0])) {
            $queryParams[] = '&ppg_nome=' . $r['_source']['ppg_nome'][0] . '';
        }
        if (!empty($r["_source"]["genero"][0])) {
            $queryParams[] = '&genero=' . $r['_source']['genero'][0] . '';
        }
        if (!empty($r["_source"]["desc_nivel"][0])) {
            $queryParams[] = '&desc_nivel=' . $r['_source']['desc_nivel'][0] . '';
        }
        if (!empty($r["_source"]["desc_curso"][0])) {
            $queryParams[] = '&desc_curso=' . $r['_source']['desc_curso'][0] . '';
        }
        if (!empty($r["_source"]["campus"][0])) {
            $queryParams[] = '&campus=' . $r['_source']['campus'][0] . '';
        }
        if (!empty($r["_source"]["desc_gestora"][0])) {
            $queryParams[] = '&desc_gestora=' . $r['_source']['desc_gestora'][0] . '';
        }
        //var_dump(implode('', $queryParams));

        $formattedRecordDate = date_format(date_create_from_format('Y-m', $r["_source"]["data_atualizacao"]), 'Y-m-d');
        $lattesDate = substr(file_get_contents('http://200.133.208.25/api/proxy_data_atualizacao/' . $r["_id"] . ''), 0, 10);
        $formattedLattesDate = date_format(date_create_from_format('d/m/Y', $lattesDate), 'Y-m-d');
        if ($formattedRecordDate < $formattedLattesDate) {
            echo "<br/><br/>";
            echo "$formattedRecordDate é menor que $formattedLattesDate";
            echo "<br/><br/>";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, '' . $url_base . '/import_lattes_to_elastic_dedup.php?lattesID=' . $r["_id"] . '');
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode('', $queryParams));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            echo "<br/><br/>";
            var_dump($output);
            echo "<br/><br/>";
            curl_close($ch);
        } else {
            echo "<br/><br/>";
            echo "$formattedRecordDate é maior ou igual a $formattedLattesDate";
            echo "<br/><br/>";
        }
    }
}