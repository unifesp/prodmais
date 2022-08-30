<?php

require 'inc/config.php';
require 'inc/functions.php';

if (isset($_FILES['file'])) {

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 108192, "\t");

    foreach ($row as $key => $value) {
        $rowNum["type"] = 0;
        if ($value == "TI") {
            if (!isset($rowNum["title"])) {
                $rowNum["title"] = $key;
            }
        }
        if ($value == "PY") {
            if (!isset($rowNum["year"])) {
                $rowNum["year"] = $key;
            }            
        }
        if ($value == "UT") {
            $rowNum["EID"] = $key;
        }
        if ($value == "DI") {
            $rowNum["DOI"] = $key;
        }
        if ($value == "LA") {
            $rowNum["language"] = $key;
        }
        if ($value == "SO") {
            $rowNum["sourceTitle"] = $key;
        }
        if ($value == "VL") {
            $rowNum["Volume"] = $key;
        }
        if ($value == "IS") {
            $rowNum["Issue"] = $key;
        }
        if ($value == "BP") {
            $rowNum["PageStart"] = $key;
        }
        if ($value == "EP") {
            $rowNum["PageEnd"] = $key;
        }
        if ($value == "SN") {
            $rowNum["ISSN"] = $key;
        }
        if ($value == "PU") {
            $rowNum["Publisher"] = $key;
        }
        if ($value == "PI") {
            $rowNum["PublisherCity"] = $key;
        }
        if ($value == "AB") {
            $rowNum["Abstract"] = $key;
        }
        if ($value == "FU") {
            $rowNum["FundingDetails"] = $key;
        }
        if ($value == "TC") {
            $rowNum["CitedBy"] = $key;
        }
        if ($value == "CR") {
            $rowNum["References"] = $key;
        }
        if ($value == "DE") {
            $rowNum["AuthorKeywords"] = $key;
        }
        if ($value == "ID") {
            $rowNum["IndexKeywords"] = $key;
        }
        if ($value == "C1") {
            $rowNum["AuthorsWithAffiliations"] = $key;
        }
        if ($value == "AF") {
            $rowNum["Authors"] = $key;
        }
        if ($value == "OI") {
            $rowNum["ORCID"] = $key;
        }
        if ($value == "CT") {
            $rowNum["ConferenceTitle"] = $key;
        }
    }


    while (($row = fgetcsv($fh, 1888192, "\t")) !== false) {
        $doc = Record::Build($row, $rowNum, $_POST["tag"]);
        //if (!is_null($doc["doc"]["name"]) & !is_null($doc["doc"]["datePublished"])) {
        //    $doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
        //}
        if (isset($doc["doc"]["source_id"])) {
            $sha256 = hash('sha256', ''.$doc["doc"]["source_id"].'');
        } else {
            echo "ERRO: SEM ID";
            print_r($row);
            echo "<br/><br/><br/>";
            print_r($doc);
        }

        //print_r($doc);
        //if (!is_null($sha256)) {
            $resultado_wos = Elasticsearch::update($sha256, $doc);
        //}
        //print_r($resultado_wos);
        print_r($doc["doc"]["source_id"]);
        echo "<br/><br/><br/>";
        flush();

    }
}

sleep(5);
echo '<script>window.location = \'result.php?filter[]=type:"Work"&filter[]=tag:"'.$_POST["tag"].'"\'</script>';

class Record
{
    public static function build($row, $rowNum, $tag = "")
    {

        $doc["doc"]["type"] = "Work";
        $doc["doc"]["source"] = "Base Web of Science";
        $doc["doc"]["match"]["tag"][] = "WoS";
        $doc["doc"]["name"] = str_replace('"', '', $row[$rowNum["title"]]);
        $doc["doc"]["datePublished"] = $row[$rowNum["year"]];
        $doc["doc"]["source_id"] = $row[$rowNum["EID"]];
        $doc["doc"]["tag"][] = $tag;
        if (!empty($row[$rowNum["DOI"]]) && $row[$rowNum["DOI"]] != "") {
            $doc["doc"]["doi"] = $row[$rowNum["DOI"]];
        }
        $doc["doc"]["language"] = $row[$rowNum["language"]];
        $doc["doc"]["description"] = $row[$rowNum["Abstract"]];

        if ($row[$rowNum["type"]] == "J") {
            $doc["doc"]["tipo"] = "Article";
        } else {
            $doc["doc"]["tipo"] = $row[$rowNum["type"]];
        }

        $doc["doc"]["isPartOf"]["name"] = $row[$rowNum["sourceTitle"]];
        if (is_numeric($row[$rowNum["Volume"]])) {
            $doc["doc"]["isPartOf"]["volume"] = $row[$rowNum["Volume"]];
        }
        $doc["doc"]["isPartOf"]["fasciculo"] = $row[$rowNum["Issue"]];
        $doc["doc"]["pageStart"] = $row[$rowNum["PageStart"]];
        $doc["doc"]["pageEnd"] = $row[$rowNum["PageEnd"]];
        $doc["doc"]["isPartOf"]["issn"] = $row[$rowNum["ISSN"]];
        $doc["doc"]["publisher"]["organization"]["name"] = $row[$rowNum["Publisher"]];
        $doc["doc"]["publisher"]["organization"]["location"] = $row[$rowNum["PublisherCity"]];
        $doc["doc"]["citedby"] = $row[$rowNum["CitedBy"]];
        $doc["doc"]["wos"]["citedby"] = $row[$rowNum["CitedBy"]];
        $doc["doc"]["wos"]["references"] = $row[$rowNum["References"]];
        $doc["doc"]["EducationEvent"]["name"] = $row[$rowNum["ConferenceTitle"]];


        // Agência de fomento
        $agencia_de_fomento_array = explode(";", $row[$rowNum["FundingDetails"]]);
        $i_funder = 0;
        foreach ($agencia_de_fomento_array as $funder) {
            $funderArray = explode("[", $funder);
            if (count($funderArray) > 1) {
                $doc["doc"]["funder"][$i_funder]["name"] = ''.$funderArray[0].'';
                $projectNumberArray = explode(",", $funderArray[1]);
                foreach ($projectNumberArray as $projectNumber) {
                    $doc["doc"]["funder"][$i_funder]["projectNumber"] = ''.$projectNumber.'';
                }
            } else {
                $doc["doc"]["funder"][$i_funder]["name"] = $funderArray[0];
            }
            $i_funder++;
        }

        // Palavras chave
        $palavras_chave_authors = explode(";", $row[$rowNum["AuthorKeywords"]]);
        $palavras_chave_scopus = explode(";", $row[$rowNum["IndexKeywords"]]);
        $doc["doc"]["about"] = array_merge($palavras_chave_authors, $palavras_chave_scopus);

        // Autores
        $authorsArray = explode(";", $row[$rowNum["Authors"]]);
        $i_autAff=0;
        foreach ($authorsArray as $autAff) {
            $doc["doc"]["author"][$i_autAff]["person"]["name"] = $autAff;
            $i_autAff++;
        }

        // AuthorsWithAffiliations
        $AffiliationsArray = explode(";", $row[$rowNum["AuthorsWithAffiliations"]]);
        foreach ($AffiliationsArray as $Aff) {
            preg_match('/(\[.*?\])(.*)/', $Aff, $output_array);
            if (isset($output_array[2])) {
                $doc["doc"]["institutions"][] = trim($output_array[2]);
            }            
        }

        $doc["doc"]["numOfAuthors"] = count($doc["doc"]["author"]);
        $doc["doc_as_upsert"] = true;
        return $doc;



    }
}

?>
