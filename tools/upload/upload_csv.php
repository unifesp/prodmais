<?php

# To run: php upload_csv.php FILE SEPARATOR NAME_OF_INDEX_IN_ELASTICSEARCH

require '../../inc/config.php';
require '../../inc/functions.php';


/* Connect to Elasticsearch - Index */
try {
    $client = \Elastic\Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    $indexParams['index']  = $argv[3];
    $testIndex = $client->indices()->exists($indexParams);
} catch (Exception $e) {
    echo "Índice no elasticsearch não foi encontrado";
}
if (isset($argv[3]) && $testIndex == false) {
    Elasticsearch::createIndex($argv[3], $client);
}

$line = fgets(fopen($argv[1], 'r'));
$mappingsArray = explode($argv[2], $line);
define("CONSTANT_ARRAY", $mappingsArray);

foreach ($mappingsArray as $mappingString) {
    $mappingsParamsArray[$mappingString]["type"] = "text";
    $mappingsParamsArray[$mappingString]["fields"]["keyword"]["type"] = "keyword";
    $mappingsParamsArray[$mappingString]["fields"]["keyword"]["ignore_above"] = 256;
}

$mappingsParams["index"] = $argv[3];
$mappingsParams["body"]["properties"] = $mappingsParamsArray;
$client->indices()->putMapping($mappingsParams);

$row = 1;
if (($handle = fopen($argv[1], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, $argv[2])) !== FALSE) {
        $num = count($data);
        for ($c=1; $c < $num; $c++) {
            $docArray[CONSTANT_ARRAY[$c]] = $data[$c];
            $doc["doc"] = array_filter($docArray);
            $doc["doc_as_upsert"] = true;
        }

        unset($sha256);
        unset($shaText);
        unset($shaArray);
        $shaTextString = implode(",", $data);
        $sha256 = hash('sha256', $shaTextString);
        
        if (!is_null($doc)) {
            $resultado = Elasticsearch::update($sha256, $doc, $argv[3]);
        }
        print_r($resultado);
        unset($doc);
    }
    fclose($handle);
}
?>