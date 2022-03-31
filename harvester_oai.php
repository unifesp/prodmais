<?php

include('inc/config.php');
include('inc/functions.php');

if (isset($_GET["oai"])) {

    $oaiUrl = $_GET["oai"];
    $client_harvester = new \Phpoaipmh\Client(''.$oaiUrl.'');
    $myEndpoint = new \Phpoaipmh\Endpoint($client_harvester);
    // Result will be a SimpleXMLElement object

    $identify = $myEndpoint->identify();
    echo '<pre>';

    // Results will be iterator of SimpleXMLElement objects
    $results = $myEndpoint->listMetadataFormats();
    $metadata_formats = [];
    foreach($results as $item) {
        $metadata_formats[] = $item->{"metadataPrefix"};
    }

    if (isset($_GET["metadataPrefix"])) { 

        if ($_GET["metadataPrefix"] == "oai_dc") {  

            if (isset($_GET["set"])) {
                $recs = $myEndpoint->listRecords('oai_dc', null, null, $_GET["set"]);
            } else {
                $recs = $myEndpoint->listRecords('oai_dc');
            } 
            foreach($recs as $rec) {

                $data = $rec->metadata->children('http://www.openarchives.org/OAI/2.0/oai_dc/');
                $rows = $data->children('http://purl.org/dc/elements/1.1/');
                //var_dump($rows);


                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');
                $query["doc"]["type"] = "Work";
                $query["doc"]["tipo"] = (string)$rows->type[0];
                $query["doc"]["tag"] = $_GET['tag'];
                $query["doc"]["source"] = $_GET['source'];
                $query["doc"]["name"] =  (string)$rows->title[0];
                $query["doc"]["datePublished"] = substr((string)$rows->date[2], 0, 4);
                $query["doc"]["language"][] = (string)$rows->language[0];

                $i = 0;
                foreach ($rows->creator as $authors) {
                    $query["doc"]["author"][$i]["person"]["name"] = (string)$authors;
                    $i++;
                }                

                foreach ($rows->subject as $subject) {
                    $query["doc"]["about"][] = (string)$subject;
                }
                foreach ($rows->description as $description) {
                    $query["doc"]["description"][] = (string)$description;
                }
                foreach ($rows->publisher as $publisher) {
                    $query["doc"]["publisher"]["organization"]["name"] = (string)$publisher;
                }

                foreach ($rows->identifier as $identifier) {
                    if (strpos($identifier, 'doi:') !== false) {
                        $query["doc"]["doi"] = str_replace("doi:", "", (string)$identifier);
                    } else {
                        $query["doc"]["url"][] = (string)$identifier;
                    }
                    
                }
                
                $query["doc_as_upsert"] = true;

                //var_dump($query);
                //echo "<br/><br/>";

                $resultado = Elasticsearch::update($sha256, $query, $index_source);
                //print_r($resultado);
                unset($query);
                flush();                
                
            }

        } else {
            echo "Formato de metadados não aceito!";
        }     

     

    } else {

      if (in_array("nlm", $metadata_formats)) {

        $recs = $myEndpoint->listRecords('nlm');


        foreach($recs as $rec) {
            print_r($rec);
            if ($rec->{'header'}->attributes()->{'status'} != "deleted"){

                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');
                $query["doc"]["source"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'journal-title'};
                $query["doc"]["tag"] = $_GET['tag'];
                $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};
                $query["doc"]["tipo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-categories'}->{'subj-group'}->{'subject'};
                $query["doc"]["name"] = str_replace('"','',(string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'title-group'}->{'article-title'});
                $query["doc"]["datePublished"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'pub-date'}[0]->{'year'};
                $query["doc"]["doi"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1];
                $query["doc"]["resumo"] = str_replace('"','',(string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'abstract'}->{'p'});
                // Palavras-chave
                if (isset($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'})) {
                    foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'} as $palavra_chave) {
                        $palavraschave_array = explode(".", (string)$palavra_chave);
                        foreach ($palavraschave_array  as $pc) {
                            $query["doc"]["palavras_chave"][] = trim($pc);
                        }
                    }
                }
                $i = 0;
                foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'contrib-group'}->{'contrib'} as $autores) {
                    if ($autores->attributes()->{'contrib-type'} == "author"){
                        $query["doc"]["autores"][$i]["nomeCompletoDoAutor"] = (string)$autores->{'name'}->{'given-names'}.' '.$autores->{'name'}->{'surname'};
                        $query["doc"]["autores"][$i]["nomeParaCitacao"] = (string)$autores->{'name'}->{'surname'}.', '.$autores->{'name'}->{'given-names'};
                        if(isset($autores->{'aff'})) {
                            $query["doc"]["autores"][$i]["afiliacao"] = (string)$autores->{'aff'};
                        }
                        if(isset($autores->{'uri'})) {
                            $query["doc"]["autores"][$i]["nroIdCnpq"] = (string)$autores->{'uri'};
                        }
                        $i++;
                    }
                }
                $query["doc"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"] = str_replace('"','',(string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'journal-title'});
                $query["doc"]["trabalhoEmEventos"]["issn"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'};
                $query["doc"]["trabalhoEmEventos"]["volume"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'};
                $query["doc"]["trabalhoEmEventos"]["fasciculo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'};
                $query["doc"]["trabalhoEmEventos"]["nomeDoEvento"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'};
                $query["doc"]["url_principal"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'self-uri'}->attributes('http://www.w3.org/1999/xlink');
                $query["doc_as_upsert"] = true;
                foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'self-uri'} as $self_uri) {
                    $query["doc"]["relation"][]=(string)$self_uri->attributes('http://www.w3.org/1999/xlink');
                }
                //print_r($query);
                $resultado = Elasticsearch::update($sha256, $query, $index_source);
                print_r($resultado);
                unset($query);
                flush();
            }
        }
    } elseif (in_array("oai_marcxml", $metadata_formats)) {

      if (isset($_GET["set"])) {
          $recs = $myEndpoint->listRecords('oai_marcxml', null, null, $_GET["set"]);
      } else {
          $recs = $myEndpoint->listRecords('oai_marcxml');
      }

      //$recs = $myEndpoint->listRecords('oai_marcxml');
      //$recs = $myEndpoint->getRecord('oai:butantan.com.br:9','oai_marcxml');

      //print_r($recs);
      foreach ($recs as $rec) {
      //foreach($recs->{'GetRecord'} as $rec) {
          //print_r($rec);
          //echo '<br/><br/>';
          //print_r($rec->{'record'});

          $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');
          $query["doc"]["type"] = "Work";
          $query["doc"]["tipo"] = "Article";
          $query["doc"]["source"] = $_GET['source'];
          $query["doc"]["source_id"] = (string)$rec->{'header'}->{'identifier'};
          $query["doc"]["tag"] = $_GET['source'];
          $query["doc"]["match"]["tag"][] = (string)$_GET['source'];
          $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};

          foreach ($rec->{'metadata'}->{'collection'}->{'record'}->{'datafield'} as $datafield) {
            $i_autAff=0;
            switch ($datafield->attributes()->{'tag'}) {
              case 024:
                  if (isset($datafield->{'subfield'}[0])) {
                    if ($datafield->{'subfield'}[0]->attributes()->{'code'} == 'a') {
                      $query["doc"]["doi"] = (string)$datafield->{'subfield'}[0];
                    }
                  }

                  break;
                case 100:
                  foreach ($datafield->{'subfield'} as $authorSubfield) {
                    if ($authorSubfield->attributes()->{'code'} == 'a') {
                      $query["doc"]["author"][$i_autAff]["person"]["name"] = (string)$authorSubfield;
                    }
                    if ($authorSubfield->attributes()->{'code'} == 'u') {
                      $query["doc"]["author"][$i_autAff]["person"]["affiliation"]["name"] = (string)$authorSubfield;
                      $query["doc"]["institutions"][] = (string)$authorSubfield;
                    }
                  }
                  $i_autAff++;
                  break;
                case 110:
                  foreach ($datafield->{'subfield'} as $authorSubfield) {
                    if ($authorSubfield->attributes()->{'code'} == 'a') {
                      $query["doc"]["author"][$i_autAff]["person"]["name"] = (string)$authorSubfield;
                    }
                    if ($authorSubfield->attributes()->{'code'} == 'u') {
                      $query["doc"]["author"][$i_autAff]["person"]["affiliation"]["name"] = (string)$authorSubfield;
                      $query["doc"]["institutions"][] = (string)$authorSubfield;
                    }
                  }
                  $i_autAff++;
                  break;
                case 245:
                  if (isset($datafield->{'subfield'}[1])) {
                    if ($datafield->{'subfield'}[1]->attributes()->{'code'} == 'b'){
                      $query["doc"]["name"] = ''.(string)$datafield->{'subfield'}[0].': ' . (string)$datafield->{'subfield'}[1].'';
                    } else {
                      $query["doc"]["name"] = (string)$datafield->{'subfield'}[0];
                    }
                  } else {
                    $query["doc"]["name"] = (string)$datafield->{'subfield'}[0];
                  }
                    break;
                case 260:
                    if (isset($datafield->{'subfield'}[2])) {
                      if ($datafield->{'subfield'}[2]->attributes()->{'code'} == 'c') {
                        $query["doc"]["datePublished"] = (string)$datafield->{'subfield'}[2];
                      }
                    }

                    break;
                case 650:
                  if (isset($datafield->{'subfield'}[0])) {
                    if ($datafield->{'subfield'}[0]->attributes()->{'code'} == 'a') {
                      $query["doc"]["about"][] = (string)$datafield->{'subfield'}[0];
                    }
                  }

                  break;
                case 700:
                  foreach ($datafield->{'subfield'} as $authorSubfield) {
                    if ($authorSubfield->attributes()->{'code'} == 'a') {
                      $query["doc"]["author"][$i_autAff]["person"]["name"] = (string)$authorSubfield;
                    }
                    if ($authorSubfield->attributes()->{'code'} == 'u') {
                      $query["doc"]["author"][$i_autAff]["person"]["affiliation"]["name"] = (string)$authorSubfield;
                      $query["doc"]["institutions"][] = (string)$authorSubfield;
                    }
                  }
                  $i_autAff++;
                  break;
                case 773:
                    foreach ($datafield->{'subfield'} as $journalInformation) {
                      if ($journalInformation->attributes()->{'code'} == 't') {
                        $query["doc"]["isPartOf"]["name"] = (string)$journalInformation;
                      }
                    }
                    break;
                case 852:
                echo $datafield;
                    if (isset($datafield->{'subfield'}[1])) {
                      if ($datafield->{'subfield'}[1]->attributes()->{'code'} == 'b') {
                        $query["doc"]["source"] = (string)$datafield->{'subfield'}[1];
                        $query["doc"]["tag"] = (string)$datafield->{'subfield'}[1];
                        unset($query["doc"]["match"]["tag"]);
                        $query["doc"]["match"]["tag"][] = (string)$datafield->{'subfield'}[1];
                      }
                    }
                    break;
                case 2:
                    print_r($datafield);
                    break;
            }

          }

          $query["doc_as_upsert"] = true;

          //echo '<br/><br/>';
          //print_r($query);

          $resultado = Elasticsearch::update($sha256, $query, $index_source);
          //print_r($resultado);
          unset($query);
          flush();

          //exit;


          //$rec->{'header'}->attributes()->{'status'}

      }

    } else {

        $recs = $myEndpoint->listRecords('rfc1807');
        var_dump($recs);
        foreach($recs as $rec) {
            if ($rec->{'header'}->attributes()->{'status'} != "deleted"){
                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');
                $query["doc"]["source"] = (string)$identify->Identify->repositoryName;
                    $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};
                    if (isset($_GET["qualis2015"])) {
                        $query["doc"]["qualis2015"] = $_GET["qualis2015"];
                    }
                    $query["doc"]["tipo"] = "Trabalhos em eventos";
                    $query["doc"]["titulo"] = str_replace('"','',(string)$rec->{'metadata'}->{'rfc1807'}->{'title'});
                    $query["doc"]["ano"] = substr((string)$rec->{'metadata'}->{'rfc1807'}->{'entry'},0,4);
                    print_r($rec->{'metadata'}->{'rfc1807'});
    //                $query["doc"]["doi"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1];
                    $query["doc"]["resumo"] = str_replace('"','',(string)$rec->{'metadata'}->{'rfc1807'}->{'abstract'});
                    $query["doc"]["evento"]["titulo_dos_anais"] = str_replace('"','',(string)$rec->{'metadata'}->{'rfc1807'}->{'organization'}[0]);
    //
                    // Palavras-chave
                    if (isset($rec->{'metadata'}->{'rfc1807'}->{'keyword'})) {
                        foreach ($rec->{'metadata'}->{'rfc1807'}->{'keyword'} as $palavra_chave) {
                            $pc_array = [];
                            $pc_array = explode(".", (string)$palavra_chave);
                            foreach ($pc_array as $pc_explode){
                                $pc_array_dot = explode("-", $pc_explode);
                            }
                            foreach ($pc_array_dot as $pc_dot){
                                $pc_array_end = explode(".", $pc_dot);
                            }
                            foreach ($pc_array_end as $pc) {
                                $query["doc"]["palavras_chave"][] = trim($pc);
                            }
                        }
                    }
                    $i = 0;
                    foreach ($rec->{'metadata'}->{'rfc1807'}->{'author'} as $autor) {
                        $autor_array = explode(";", (string)$autor);
                        $autor_nome_array = explode(",", (string)$autor_array[0]);
                            $query["doc"]["autores"][$i]["nomeCompletoDoAutor"] = $autor_nome_array[1].' '.ucwords(strtolower($autor_nome_array[0]));
                            $query["doc"]["autores"][$i]["nomeParaCitacao"] = (string)$autor_array[0];
                            if(isset($autor_array[1])) {
                                $query["doc"]["autores"][$i]["afiliacao"] = (string)$autor_array[1];
                            }
                            $i++;
                    }
                    $query["doc"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"] = (string)$identify->Identify->repositoryName;
    //                $query["doc"]["artigoPublicado"]["nomeDaEditora"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'publisher'}->{'publisher-name'};
    //                $query["doc"]["artigoPublicado"]["issn"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'};
    //                $query["doc"]["artigoPublicado"]["volume"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'};
    //                $query["doc"]["artigoPublicado"]["fasciculo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue'};
    //                $query["doc"]["artigoPublicado"]["paginaInicial"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'};
    //                $query["doc"]["artigoPublicado"]["serie"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'};
                    $query["doc"]["url_principal"] = (string)$rec->{'metadata'}->{'rfc1807'}->{'id'};
                    $query["doc"]["relation"][]=(string)$rec->{'metadata'}->{'rfc1807'}->{'id'};
                    $query["doc_as_upsert"] = true;
                    $resultado = Elasticsearch::update($sha256, $query, $index_source);
                    print_r($resultado);
                    unset($query);
                    flush();
            }
        }
    }      

    }



} else {
    echo "URL não informada";
}
?>
