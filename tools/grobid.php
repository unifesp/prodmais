<?php 

$file="aleph.seq";
header("Content-Disposition: attachment; filename=$file");

error_reporting(E_ERROR | E_PARSE);
include('inc/config.php');             
include('inc/functions.php');

if (isset($_FILES['file'])) {    
    $content = file_get_contents($_FILES['file']['tmp_name']);
} elseif (isset($_POST["url"])){
    $content = file_get_contents($_POST["url"]);
} else {
    $content = file_get_contents('https://www.revistas.usp.br/incid/article/download/132103/133889');
}


// initialise the curl request
$request = curl_init('localhost:8070/api/processFulltextDocument');


//$content = file_get_contents('http://www.producao.usp.br/bitstream/handle/BDPI/51488/13068_2017_Article_999.pdf');

// send a file

curl_setopt($request, CURLOPT_POST, true);
curl_setopt(
    $request,
    CURLOPT_POSTFIELDS,
    array(
      'input' => $content
    ));
// output the response
curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
//echo curl_exec($request);

$result = curl_exec($request);

$xml = simplexml_load_string($result);

/* Exportador AlephSequential */

$record_blob[] = '000000001 FMT   L BK\n';
$record_blob[] = '000000001 LDR   L ^^^^^nab^^22^^^^^Ia^4500\n';
$record_blob[] = '000000001 BAS   L $$a04\n';
$record_blob[] = '000000001 008   L ^^^^^^s^^^^^^^^^^^^^^^^^^^^^^000^0^^^^^d\n';

if (isset($xml->teiHeader->fileDesc->sourceDesc->biblStruct->idno)){
    if ($xml->teiHeader->fileDesc->sourceDesc->biblStruct->idno->attributes()->type == "DOI"){
        $record_blob[] = '000000001 0247  L $$a'.(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->idno.'$$2DOI\n';
    }
}



$record_blob[] = '000000001 040   L $$aUSP/SIBI\n';
$record_blob[] = '000000001 0410  L $$a\n';
$record_blob[] = '000000001 044   L $$a\n';

$i_author = 0;
foreach ($xml->teiHeader->fileDesc->sourceDesc->biblStruct->analytic->author as $author) {
    if (isset($author->persName)) {
        foreach ($author->persName->forename as $author_name) {
            $author_name_array[] = $author_name;
        }
        if ($i_author == 0) {           
            $record_blob[] = '000000001 1001  L $$a'.(string)$author->persName->surname.', '.implode(" ",$author_name_array).'$$5$$7$$8$$9\n';
        } else {
            $record_blob[] = '000000001 7001  L $$a'.(string)$author->persName->surname.', '.implode(" ",$author_name_array).'$$5$$7$$8$$9\n';
        }
        unset($author_name_array);
    }
    $i_author++;
}

if (strpos((string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->analytic->title, ":")) {
    $title_array = explode(":",(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->analytic->title);
    $record_blob[] = '000000001 24510 L $$a'.trim($title_array[0]).'$$b'.trim($title_array[1]).'\n';
} else {
    $record_blob[] = '000000001 24510 L $$a'.(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->analytic->title.'\n';
}

/* Serial title */
if (isset($xml->teiHeader->fileDesc->sourceDesc->biblStruct->monogr->imprint->date)){
    $date = (string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->monogr->imprint->date;
} else {
    $date = "";
}
$record_blob[] = '000000001 260   L $$a$$b'.(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->monogr->imprint->publisher.'$$c'.substr($date, 0, 4).'\n';


$record_blob[] = '000000001 300   L $$ap. -\n';

$record_blob[] = '000000001 500   L $$a\n';
$record_blob[] = '000000001 5101  L $$aIndexado no:\n';
$record_blob[] = '000000001 650 7 L $$a\n';
$record_blob[] = '000000001 650 7 L $$a\n';
$record_blob[] = '000000001 650 7 L $$a\n';
$record_blob[] = '000000001 650 7 L $$a\n';

$record_blob[] = '000000001 7730  L $$t'.(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->monogr->title.'$$x'.(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->monogr->idno.'$$hv. , n. , p. - , AAAA\n';

if ($xml->teiHeader->fileDesc->sourceDesc->biblStruct->idno->attributes()->type == "DOI"){
    $record_blob[] = '000000001 8564  L $$zClicar sobre o botão para acesso ao texto completo$$uhttps://doi.org/'.(string)$xml->teiHeader->fileDesc->sourceDesc->biblStruct->idno.'$$3DOI\n';
}

$record_blob[] = '000000001 945   L $$aP$$bARTIGO DE PERIODICO$$c01$$j'.substr($date, 0, 4).'$$l\n';
$record_blob[] = '000000001 946   L $$a\n';

sort($record_blob);

foreach ($record_blob as $record) {
    $record_array = explode('\n',$record);
    echo implode("\n",$record_array);
}

// echo '<br/><br/><br/>';
// print_r($xml);
// echo '<br/><br/><br/>';
// print_r($xml->teiHeader->profileDesc);


// close the session
curl_close($request);

?>