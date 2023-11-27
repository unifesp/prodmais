<?php

// /* Exibir erros */
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(1);

$file = "export_sdg_aurora.tsv";
header('Content-type: text/tab-separated-values; charset=utf-8');
header("Content-Disposition: attachment; filename=$file");

$content[] = "título\tODS\tProbabilidade";

if (isset($_FILES['file'])) {
    //echo "<pre>" . print_r($_FILES['file'], true) . "</pre>";

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 108192, "\t");

    foreach ($row as $key => $value) {
        if ($value == "TITULO") {
            define("TITULO", $key);
        }
        if ($value == "RESUMO") {
            define("RESUMO", $key);
        }
    }

    while (($row = fgetcsv($fh, 108192, "\t")) !== false) {
        $titulo_resumo = $row[TITULO] . ' ' . $row[RESUMO];
        $json_titulo_resumo = str_replace('"', '', json_encode($titulo_resumo, JSON_UNESCAPED_UNICODE));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://aurora-sdg.labs.vu.nl/classifier/classify/elsevier-sdg-multi');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"text": "' . $json_titulo_resumo . '"}');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        curl_close($ch);

        //print("<pre>" . print_r($response, true) . "</pre>");

        // Sort response to get the most probable SDG

        $response = json_decode($response, true);
        usort($response["predictions"], function ($a, $b) {
            return $b['prediction'] <=> $a['prediction'];
        });

        $content[] = $row[TITULO] . "\t" . $response["predictions"][0]["sdg"]["name"] . "\t" . $response["predictions"][0]["prediction"];

        // $body["doc"]["aurorasdg"] = $response;
        // $body["doc"]["aurorasdg"]["most_probable_sdg"] = $response["predictions"][0]["sdg"]["name"];
        // $body["doc_as_upsert"] = true;

        // print("<pre>" . print_r($body, true) . "</pre>");
        ob_flush();
        flush();
    }
    fclose($fh);

    echo implode("\n", $content);
}