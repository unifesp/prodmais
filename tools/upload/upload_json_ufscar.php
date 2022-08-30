<?php

require '../../inc/config.php';
require '../../inc/functions.php';

if (isset($_FILES['file'])) {

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 8192, "\t");

    while (($row = fgetcsv($fh, 8192, "\t")) !== false) {
        //print_r($row);
        $record_json = json_decode($row[0], true);
        $doc["doc"] = $record_json;
        $doc["doc_as_upsert"] = true;

        if (isset($doc["doc"]["dc.title"][0]["value"])) {
            $sha256 = hash('sha256', '' . $doc["doc"]["dc.title"][0]["value"] . '');

            if (!is_null($sha256)) {
                $result_elastic = Elasticsearch::update($sha256, $doc);
            }

            echo "<br/><br/>";
            print_r($result_elastic);
            echo "<br/><br/>";
        }

        flush();

        // echo "<br/><br/>";
        // print("<pre>" . print_r($doc, true) . "</pre>");
        // echo "<br/><br/><br/>";
        // print("<pre>" . print_r($sha256, true) . "</pre>");
        // echo "<br/><br/><br/>";

        //$doc = Record::Build($row, $rowNum, $_POST["tag"]);
        //if (!is_null($doc["doc"]["name"]) & !is_null($doc["doc"]["datePublished"])) {
            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
        //}
        //$sha256 = hash('sha256', ''.$doc["doc"]["source_id"].'');
        //print_r($sha256);
        //print("<pre>".print_r($doc, true)."</pre>");
        //if (!is_null($sha256)) {
        //   $resultado = Elasticsearch::update($sha256, $doc);
        //}
        //print_r($resultado);
        // print_r($doc["doc"]["source_id"]);
        // echo "<br/><br/><br/>";
        //flush();

    }
}

//sleep(5);
//echo '<script>window.location = \'result.php?filter[]=type:"Work"&filter[]=tag:"'.$_POST["tag"].'"\'</script>';