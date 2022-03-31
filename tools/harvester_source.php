<?php

chdir('../');
require 'inc/config.php';
require 'inc/functions.php';

function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
 
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
 }


if (isset($_GET["oai"])) {


    if (!isset($_GET["useTematres"])) {
        $_GET["useTematres"] = false;
    }

    $oaiUrl = $_GET["oai"];
    $client_harvester = new \Phpoaipmh\Client(''.$oaiUrl.'');
    $myEndpoint = new \Phpoaipmh\Endpoint($client_harvester);


    // Result will be a SimpleXMLElement object
    $identify = $myEndpoint->identify();
    echo '<pre>';

    // Store repository data - Início

    $repositoryName = (string)$identify->Identify->repositoryName;
    $repositoryName = str_replace(":", " ", $repositoryName);

    $body_repository["doc"]["name"] = $repositoryName;

    if (!empty($_GET["repositoryName"])) {
        $body_repository["doc"]["repositoryName"] = $_GET["repositoryName"];
    }

    $body_repository["doc"]["metadataFormat"] = $_GET["metadataFormat"];
    if (isset($_GET["qualis2015"])) {
        $body_repository["doc"]["qualis2015"] = $_GET["qualis2015"];
    }
    if (isset($_GET["area"])) {
        $body_repository["doc"]["area"] = $_GET["area"];
    }
    if (isset($_GET["areaChild"])) {
        $body_repository["doc"]["areaChild"] = $_GET["areaChild"];
    }
    if (isset($_GET["corrente"])) {
        $body_repository["doc"]["corrente"] = $_GET["corrente"];
    }
    if (isset($_GET["typeOfContent"])) {
        $body_repository["doc"]["typeOfContent"] = $_GET["typeOfContent"];
    }    
    $body_repository["doc"]["date"] = (string)$identify->responseDate;
    $body_repository["doc"]["url"] = (string)$identify->request;
    $body_repository["doc"]["type"] = "journal";
    $body_repository["doc_as_upsert"] = true;

    $insert_repository_result = Elasticsearch::update(clean($body_repository["doc"]["url"]), $body_repository, $index_source);
    
    // Store repository data - Fim

    // Results will be iterator of SimpleXMLElement objects
    $results = $myEndpoint->listMetadataFormats();
    $metadata_formats = [];
    foreach ($results as $item) {
        $metadata_formats[] = $item->{"metadataPrefix"};
    }

    if ($_GET["metadataFormat"] == "nlm") {

        if (isset($_GET["set"])) {
            $recs = $myEndpoint->listRecords('nlm', null, null, $_GET["set"]);
        } else {
            $recs = $myEndpoint->listRecords('nlm');
        }


        foreach ($recs as $rec) {

            //print_r($rec);

            if ($rec->{'header'}->attributes()->{'status'} != "deleted") {

                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');


                if (!empty($_GET["repositoryName"])) {
                    $query["doc"]["source"] = $_GET["repositoryName"];
                } else {
                    $query["doc"]["source"] = $repositoryName;
                }
                
                $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};
                $query["doc"]["originalType"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-categories'}->{'subj-group'}->{'subject'};
                $query["doc"]["name"] = str_replace('"', '', (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'title-group'}->{'article-title'});
                $query["doc"]["datePublished"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'pub-date'}[1]->{'year'};
                $query["doc"]["doi"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1];
                $query["doc"]["description"] = str_replace('"', '', (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'abstract'}->{'p'});

                // Palavras-chave
                if (isset($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'})) {
                    foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'} as $palavra_chave) {
                        $palavra_chave_corrigida = str_replace(",", ".", (string)$palavra_chave);
                        $palavra_chave_corrigida = str_replace(";", ".", (string)$palavra_chave);
                        $palavraschave_array = explode(".", $palavra_chave_corrigida);
                        foreach ($palavraschave_array  as $pc) {
                            $query["doc"]["about"][] = trim($pc);
                        }
                    }
                }


                $i = 0;
                foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'contrib-group'}->{'contrib'} as $autores) {

                    if ($autores->attributes()->{'contrib-type'} == "author") {
                        $string_author = (string)$autores->{'name'}->{'given-names'}.' '.$autores->{'name'}->{'surname'};
                        if ($string_author != "O Editor" || $string_author != "Os Editores") {
                            $query["doc"]["author"][$i]["person"]["completeName"] = (string)$autores->{'name'}->{'given-names'}.' '.$autores->{'name'}->{'surname'};
                        }
                        $query["doc"]["author"][$i]["person"]["name"] = (string)$autores->{'name'}->{'surname'}.', '.$autores->{'name'}->{'given-names'};

                        if (isset($autores->{'aff'})) {
                            if ($_GET["useTematres"] == true) {
                                $resultTematres = Authorities::tematresQuery(trim(strip_tags((string)$autores->{'aff'})), $tematres_url); 
                                if ($resultTematres['foundTerm'] != "ND") {
                                    $query["doc"]["author"][$i]["organization"]["name"] = $resultTematres['foundTerm'];
                                    $query["doc"]["author"][$i]["organization"]["tematres"] = true;
                                } else {
                                    $query["doc"]["author"][$i]["organization"]["name"] = trim(strip_tags((string)$autores->{'aff'}));
                                }
                            } else {
                                $query["doc"]["author"][$i]["organization"]["name"] = trim(strip_tags((string)$autores->{'aff'}));
                            }
                            
                        }

                        if (isset($autores->{'uri'})) {
                            $query["doc"]["author"][$i]["nroIdCnpq"] = (string)$autores->{'uri'};
                        }
                        $i++;
                    }
                }
                $query["doc"]["numAutores"] = $i;

                $query["doc"]["isPartOf"]["name"] = $repositoryName;
                $query["doc"]["isPartOf"]["publisher"]["organization"]["name"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'publisher'}->{'publisher-name'};
                $query["doc"]["isPartOf"]["ISSN"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'};
                $query["doc"]["isPartOf"]["volume"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'};
                $query["doc"]["isPartOf"]["issue"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue'};
                $query["doc"]["isPartOf"]["initialPage"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'};
                $query["doc"]["isPartOf"]["serie"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'};
                $query["doc"]["url"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'self-uri'}->attributes('http://www.w3.org/1999/xlink');

                $query["doc"]["origin"] = "OAI-PHM";

                if (isset($_GET["typeOfContent"])) {
                    $query["doc"]["type"] = $_GET["typeOfContent"];
                } else {
                    $query["doc"]["type"] = "Work";
                }
                
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

    } elseif ($_GET["metadataFormat"] == "oai_dc") {

        if (isset($_GET["set"])) {
            $recs = $myEndpoint->listRecords('oai_dc', null, null, $_GET["set"]);
        } else {
            $recs = $myEndpoint->listRecords('oai_dc');
        }
        foreach ($recs as $rec) {
            $data = $rec->metadata->children('http://www.openarchives.org/OAI/2.0/oai_dc/');
            $rows = $data->children('http://purl.org/dc/elements/1.1/');

            //var_dump ($rows);

            if (isset($rows->publisher)) {
                $query["doc"]["isPartOf"]["publisher"]["organization"]["name"] = (string)$rows->publisher;
            }

            if (isset($rows->title)) {
                $query["doc"]["name"] = (string)$rows->title[0];
                if (isset($rows->title[1])) {
                    $query["doc"]["alternateName"] = (string)$rows->title[1];
                }
            }

            if (isset($rows->identifier)) {
                $identifierString = (string)$rows->identifier[1];
                if (substr($identifierString, 0, 2) == "10"){
                    $query["doc"]["doi"] = (string)$rows->identifier[1];
                }                
            }

            if (isset($rows->identifier)) {
                if (substr((string)$rows->identifier, 0, 4) === "http"){
                    $query["doc"]["url"] = (string)$rows->identifier;
                }
            }

            if (isset($rows->identifier)) {
                if (substr((string)$rows->identifier, 0, 4) === "http"){
                    $query["doc"]["relation"][] = (string)$rows->identifier;
                }
            }

            if (isset($rows->description)) {
                if (!isset($query["doc"]["description"])) {
                    $query["doc"]["description"] = (string)$rows->description[0];
                }
            }            

            if (isset($rows->source)) {
                $query["doc"]["isPartOf"]["name"] = $repositoryName;
            }

            if (isset($rows->subject)) {
                $subjectString = (string)$rows->subject;
                $subjectArray = explode(";", $subjectString);
                if (is_array($subjectArray)) {
                    $query["doc"]["about"] = $subjectArray;
                } else {
                    $query["doc"]["about"][] = $subjectArray;
                }

                if (isset($rows->subject[1])) {
                    $subjectString = (string)$rows->subject[1];
                    $subjectArray = explode(";", $subjectString);
                    if (is_array($subjectArray)) {
                        $query["doc"]["about"] = array_merge($query["doc"]["about"], $subjectArray);
                    } else {
                        $query["doc"]["about"][] = $subjectArray;
                    }
                }
                
                if (isset($rows->subject[2])) {
                    $subjectString = (string)$rows->subject[2];
                    $subjectArray = explode(";", $subjectString);
                    if (is_array($subjectArray)) {
                        $query["doc"]["about"] = array_merge($query["doc"]["about"], $subjectArray);
                    } else {
                        $query["doc"]["about"][] = $subjectArray;
                    }
                }                   
                
            }            


            if (isset($rows->creator)) {
                $i = 0;
                foreach ($rows->creator as $author) {
                    $authorArray = explode(";", (string)$author);
                    $query["doc"]["author"][$i]["person"]["name"] = $authorArray[0];
                    if (!empty($authorArray[1])) {
                        if ($_GET["useTematres"] == true) {
                            $resultTematres = Authorities::tematresQuery(trim(strip_tags($authorArray[1])), $tematres_url);
                            if ($resultTematres['foundTerm'] != "ND") {
                                $query["doc"]["author"][$i]["organization"]["name"] = $resultTematres['foundTerm'];
                                $query["doc"]["author"][$i]["organization"]["tematres"] = true;
                            } else {
                                $query["doc"]["author"][$i]["organization"]["name"] = trim(strip_tags($authorArray[1]));
                            }
                        } else {
                            $query["doc"]["author"][$i]["organization"]["name"] = trim(strip_tags($authorArray[1]));
                        }                        
                    }
                    $i++;
                }
            }
            $query["doc"]["numAutores"] = $i;

            if (isset($rows->date)) {
                $query["doc"]["datePublished"] = substr((string)$rows->date[2], 0, 4);
            }

            if (isset($rows->relation)) {
                $query["doc"]["relation"][] = (string)$rows->relation;
            }
            
            if (isset($rows->relation)) {
                $relationString = (string)$rows->relation;
                if (substr($relationString, 0, 2) == "10"){
                    $query["doc"]["doi"] = (string)$rows->relation;
                }                
            }

            $id = (string)$rec->header->identifier;

            if (!empty($_GET["repositoryName"])) {
                $query["doc"]["source"] = $_GET["repositoryName"];
            } else {
                $query["doc"]["source"] = $repositoryName;
            }
            $query["doc"]["origin"] = "OAI-PHM";
            if (isset($_GET["typeOfContent"])) {
                $query["doc"]["type"] = $_GET["typeOfContent"];
            } else {
                $query["doc"]["type"] = "Work";
            }
            $query["doc_as_upsert"] = true;
            unset($author);
            //print_r($query);
            $resultado = Elasticsearch::update(clean($id), $query, $index_source);
            //print_r($resultado);
            //print_r($query);
            unset($query);

        }

    } elseif ($_GET["metadataFormat"] == "rfc1807") {

        $recs = $myEndpoint->listRecords('rfc1807');
        foreach ($recs as $rec) {
            if ($rec->{'header'}->attributes()->{'status'} != "deleted") {

                //var_dump($rec);

                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');

                if (!empty($_GET["repositoryName"])) {
                    $query["doc"]["source"] = $_GET["repositoryName"];
                } else {
                    $query["doc"]["source"] = $repositoryName;
                }

                $query["doc"]["set"] = (string)$rec->{'header'}->{'setSpec'};
                $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};
                $query["doc"]["originalType"] = (string)$rec->{'metadata'}->{'rfc1807'}->{'type'}[0];
                $query["doc"]["name"] = str_replace('"', '', (string)$rec->{'metadata'}->{'rfc1807'}->{'title'});
                $query["doc"]["datePublished"] = substr((string)$rec->{'metadata'}->{'rfc1807'}->{'date'}, 0, 4);
                //$query["doc"]["doi"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1];
                $query["doc"]["description"] = str_replace('"', '', (string)$rec->{'metadata'}->{'rfc1807'}->{'abstract'});

                // Palavras-chave
                if (isset($rec->{'metadata'}->{'rfc1807'}->{'keyword'})) {
                    foreach ($rec->{'metadata'}->{'rfc1807'}->{'keyword'} as $palavra_chave) {
                        $pc_array = [];
                        $pc_array = explode(";", (string)$palavra_chave);
                        foreach ($pc_array as $pc) {
                            $query["doc"]["about"][] = trim($pc);
                        }
                    }
                }

                $i = 0;
                foreach ($rec->{'metadata'}->{'rfc1807'}->{'author'} as $autor) {
                    $autor_array = explode(";", (string)$autor);
                    $autor_nome_array = explode(",", (string)$autor_array[0]);
                    $query["doc"]["author"][$i]["person"]["completeName"] = $autor_nome_array[1].' '.ucwords(strtolower($autor_nome_array[0]));
                    $query["doc"]["author"][$i]["person"]["name"] = (string)$autor_array[0];
                    if (isset($autor_array[1])) {
                        if ($_GET["useTematres"] == true) {
                            $resultTematres = Authorities::tematresQuery(trim(strip_tags((string)$autor_array[1])), $tematres_url);
                            if ($resultTematres['foundTerm'] != "ND") {
                                $query["doc"]["author"][$i]["organization"]["name"] = $resultTematres['foundTerm'];
                                $query["doc"]["author"][$i]["organization"]["tematres"] = true;
                            } else {
                                $query["doc"]["author"][$i]["organization"]["name"] = trim(strip_tags((string)$autor_array[1]));
                            }
                        } else {
                            $query["doc"]["author"][$i]["organization"]["name"] = trim(strip_tags((string)$autor_array[1]));
                        }
                    }
                    $i++;
                }
                $query["doc"]["numAutores"] = $i;

                $query["doc"]["isPartOf"]["name"] = $repositoryName;
                //$query["doc"]["artigoPublicado"]["nomeDaEditora"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'publisher'}->{'publisher-name'};
                //$query["doc"]["artigoPublicado"]["issn"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'};
                //$query["doc"]["artigoPublicado"]["volume"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'};
                //$query["doc"]["artigoPublicado"]["fasciculo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue'};
                //$query["doc"]["artigoPublicado"]["paginaInicial"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'};
                //$query["doc"]["artigoPublicado"]["serie"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'};
                $query["doc"]["url"] = (string)$rec->{'metadata'}->{'rfc1807'}->{'id'};


                $query["doc"]["relation"][]=(string)$rec->{'metadata'}->{'rfc1807'}->{'id'};

                $query["doc"]["origin"] = "OAI-PHM";
                if (isset($_GET["typeOfContent"])) {
                    $query["doc"]["type"] = $_GET["typeOfContent"];
                } else {
                    $query["doc"]["type"] = "Work";
                }
                $query["doc_as_upsert"] = true;

                $resultado = Elasticsearch::update($sha256, $query, $index_source);
                print_r($resultado);

                unset($query);
                flush();

            }
        }

    } elseif ($_GET["metadataFormat"] == "dim") {
        if (isset($_GET["set"])) {
            $recs = $myEndpoint->listRecords('dim', null, null, $_GET["set"]);
        } else {
            $recs = $myEndpoint->listRecords('dim');
        }
        foreach ($recs as $rec) {
            //$data = $rec->metadata->children('http://www.dspace.org/xmlns/dspace/dim');
            //$rows = $data->children('http://www.dspace.org/xmlns/dspace/dim');
            foreach ($rec->metadata->children('http://www.dspace.org/xmlns/dspace/dim') as $record) {
                $i = 0;
                foreach ($record->field as $field) {
                    if ($field->attributes()->element == "title" && empty($field->attributes()->qualifier)) {
                        $query["doc"]["name"] = (string)$field;
                    }
                    if ($field->attributes()->element == "title" && $field->attributes()->qualifier == "alternative") {
                        $query["doc"]["alternateName"] = (string)$field;
                    }
                    if ($field->attributes()->element == "subject") {
                        $query["doc"]["about"][] = (string)$field;
                    }
                    if ($field->attributes()->element == "date" && $field->attributes()->qualifier == "issued") {
                        $query["doc"]["datePublished"] = substr((string)$field, 0, 4);
                    }
                    if ($field->attributes()->element == "identifier" && $field->attributes()->qualifier == "doi") {
                        $query["doc"]["doi"] = (string)$field;
                    }
                    if ($field->attributes()->element == "relation" && $field->attributes()->qualifier == "ispartof") {
                        $query["doc"]["isPartOf"]["name"] = (string)$field;
                    }
                    if ($field->attributes()->element == "jtitle") {
                        $query["doc"]["isPartOf"]["name"] = (string)$field;
                    }
                    if ($field->attributes()->element == "identifier" && $field->attributes()->qualifier == "issn") {
                        $query["doc"]["isPartOf"]["issn"] = (string)$field;
                    }
                    if ($field->attributes()->element == "identifier" && $field->attributes()->qualifier == "uri") {
                        $query["doc"]["url"] = (string)$field;
                    }
                    if ($field->attributes()->element == "identifier" && $field->attributes()->qualifier == "citation") {
                        $query["doc"]["citation"] = (string)$field;
                    }
                    // if ($field->attributes()->element == "description" && $field->attributes()->qualifier == "abstract") {
                    //     $query["doc"]["description"][] = (string)$field;
                    // }
                    if ($field->attributes()->element == "description" && $field->attributes()->qualifier == "source") {
                        $query["doc"]["source"] = (string)$field;
                    }
                    if ($field->attributes()->element == "contributor" && $field->attributes()->qualifier == "author") {
                        $author[$i]["person"]["name"] = (string)$field;
                    }
                    if ($field->attributes()->element == "contributor" && $field->attributes()->qualifier == "institution") {
                        $query["doc"]["instituicao"]["contributor"]["institution"][] = (string)$field;
                    }
                    if ($field->attributes()->element == "description" && $field->attributes()->qualifier == "affiliation") {
                        $query["doc"]["instituicao"]["description"]["affiliation"][] = (string)$field;
                    }
                    if ($field->attributes()->element == "description" && $field->attributes()->qualifier == "affiliationUnifesp") {
                        $query["doc"]["instituicao"]["description"]["affiliationUnifesp"][] = (string)$field;
                    }
                    if ($field->attributes()->element == "format" && $field->attributes()->qualifier == "extend") {
                        $pagesArray = explode("-", (string)$field);
                        $query["doc"]["pageStart"] = $pagesArray[0];
                        $query["doc"]["pageEnd"] = $pagesArray[1];
                    }
                    if ($field->attributes()->element == "rights") {
                        $query["doc"]["conditionsOfAccess"] = (string)$field;
                    }
                    if ($field->attributes()->element == "type") {
                        $query["doc"]["type"] = (string)$field;
                    }
                    if ($field->attributes()->element == "type") {
                        $query["doc"]["tipo"] = (string)$field;
                    }
                    if ($field->attributes()->element == "publisher" && empty($field->attributes()->qualifier)) {
                        $query["doc"]["publisher"]["organization"]["name"] = (string)$field;
                    }
                    if ($field->attributes()->element == "publisher" && $field->attributes()->qualifier == "place") {
                        $query["doc"]["publisher"]["organization"]["location"] = (string)$field;
                    }
                    if ($field->attributes()->element == "language" && $field->attributes()->qualifier == "iso") {
                        $query["doc"]["language"][] = (string)$field;
                    }
                    if ($field->attributes()->element == "creator" && $field->attributes()->qualifier == "affilliation") {
                        $query["doc"]["institutions"][] = (string)$field;
                    }
                    $i++;
                }
            }
            $id = (string)$rec->header->identifier;
            $query["doc"]["origin"] = "OAI-PHM";
            $query["doc"]["type"] = "Work";
            $query["doc"]["unidade"] = (array)$rec->header->setSpec;
            $query["doc"]["identifier"] = (string)$rec->header->identifier;
            if (isset($author)) {
                $query["doc"]["author"] = $author;
                unset($author);
            }
            $query["doc_as_upsert"] = true;
            $resultado = Elasticsearch::update(clean($id), $query, $index_source);
            //print_r($resultado);
            //print_r($query);
            unset($query);
            unset($record);
            flush();
            //break;
        }        

    } else {
        echo "Formato de metadados não definido"; 
    }
} elseif (isset($_GET["delete"])) {
    echo $_GET["delete"];
    echo '<br/>';
    echo $_GET["delete_name"];

    $delete_repository = Elasticsearch::delete($_GET["delete"], $type, $index_source);
    print_r($delete_repository);
    echo '<br/>';
    $query["query"]["query_string"]["query"] = 'source.keyword:"'.$_GET["delete_name"].'"';
    print_r($query);
    echo '<br/><br/>';
    $delete_records = Elasticsearch::elastic_delete_by_query("journals", $query, $index_source);
    print_r($delete_records);


} else {
    echo "URL não informada";
}

?>
