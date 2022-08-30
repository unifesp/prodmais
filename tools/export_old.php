<?php

    if ($_GET["format"] == "table") {

        $file="export_bdpi.tsv";
        header('Content-type: text/tab-separated-values; charset=utf-8');
        header("Content-Disposition: attachment; filename=$file");

        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');

        if (!empty($_GET)) {
            $result_get = get::analisa_get($_GET);
            $query = $result_get['query'];  
            $limit = $result_get['limit'];
            $page = $result_get['page'];
            $skip = $result_get['skip'];

            if (isset($_GET["sort"])) {
                $query['sort'] = [
                    ['name.keyword' => ['order' => 'asc']],
                ];
            } else {
                $query['sort'] = [
                    ['datePublished.keyword' => ['order' => 'desc']],
                ];
            }

            $params = [];
            $params["index"] = $index;
            $params["type"] = $type;
            $params["size"] = 10000;
            $params["from"] = $skip;
            $params["body"] = $query; 

            $cursor = $client->search($params);
            $total = $cursor["hits"]["total"];

            $content[] = "Sysno\tDOI\tTítulo\tAutores\tFonte da publicação\tPaginação\tAno de publicação\tISSN\tLocal de publicação\tEditora\tNome do evento\tTipo de Material\tAutores USP\tNúmero funcional\tUnidades USP\tDepartamentos\tQualis 2013/2016\tJCR - Journal Impact Factor - 2016\tCitescore - 2016";
            
            foreach ($cursor["hits"]["hits"] as $r){

                $fields[] = $r['_id'];
            
                if (!empty($r["_source"]['doi'])) {
                    $fields[] = $r["_source"]['doi'];
                } else {
                    $fields[] = "";
                }
            
                $fields[] = $r["_source"]['name'];
                

                foreach ($r["_source"]['author'] as $authors) {
                    $authors_array[]= $authors["person"]["name"];                
                }
                $fields[] = implode(";",$authors_array);
                unset($authors_array);

                if (!empty($r["_source"]['isPartOf']["name"])) {
                    $fields[] = $r["_source"]['isPartOf']["name"];
                } else {
                    $fields[] = "";
                } 

                if (!empty($r["_source"]['isPartOf']['USP']['dados_do_periodico'])) {
                    $fields[] = $r["_source"]['isPartOf']['USP']['dados_do_periodico'];
                } else {
                    $fields[] = "";
                } 

                if (!empty($r["_source"]['datePublished'])) {
                    $fields[] = $r["_source"]['datePublished'];
                } else {
                    $fields[] = "";
                } 
                
                if (!empty($r["_source"]['isPartOf']['issn'])) {
                    foreach ($r["_source"]['isPartOf']['issn'] as $issn) {
                        $issn_array[]= $issn;                
                    }
                    $fields[] = implode(";",$issn_array);
                    unset($issn_array);
                } else {
                    $fields[] = "";
                } 
                
                if (!empty($r["_source"]['publisher']['organization']['location'])) {
                    $fields[] = $r["_source"]['publisher']['organization']['location'];
                } else {
                    $fields[] = "";
                }
                
                if (!empty($r["_source"]['publisher']['organization']['name'])) {
                    $fields[] = $r["_source"]['publisher']['organization']['name'];
                } else {
                    $fields[] = "";
                } 
                
                if (!empty($r["_source"]['releasedEvent'])) {
                    $fields[] = $r["_source"]['releasedEvent'];
                } else {
                    $fields[] = "";
                }  
                
                if (!empty($r["_source"]['type'])) {
                    $fields[] = $r["_source"]['type'];
                } else {
                    $fields[] = "";
                } 
                
                if (!empty($r["_source"]['authorUSP'])) {

                    foreach ($r["_source"]['authorUSP'] as $authorsUSP) {
                        $authorsUSP_array[]= $authorsUSP["name"];                
                    }
                    $fields[] = implode(";",$authorsUSP_array);
                    unset($authorsUSP_array);

                    foreach ($r["_source"]['authorUSP'] as $numUSP) {
                        if (!empty($numUSP["numfuncional"])) {
                            $numUSP_array[]= $numUSP["numfuncional"]; 
                        }               
                    }
                    if (!empty($numUSP_array)) {
                        $fields[] = implode(";",$numUSP_array);
                        unset($numUSP_array);
                    }

                    foreach ($r["_source"]['authorUSP'] as $unidadesUSP_aut) {
                        $unidadesUSP_array[]= $unidadesUSP_aut["unidade"];          
                    }
                    $fields[] = implode(";",$unidadesUSP_array);
                    unset($unidadesUSP_array);

                    foreach ($r["_source"]['authorUSP'] as $departament_aut) {
                        if (!empty($departament_aut["departament"])) {
                            $departament_array[]= $departament_aut["departament"];
                        }                
                    }
                    if (!empty($departament_array)) {
                        $fields[] = implode(";",$departament_array);
                        unset($departament_array);
                    }

                }
                
                if (!empty($r["_source"]['USP']['serial_metrics']['qualis']['2016'])) {
                    foreach ($r["_source"]['USP']['serial_metrics']['qualis']['2016'] as $qualis) {
                        $qualis_array[]= $qualis["area_nota"];                
                    }
                    $fields[] = implode(";",$qualis_array);
                    unset($qualis_array);
                } 
                
                if (!empty($r["_source"]['USP']['JCR']['JCR']['2016'][0]['Journal_Impact_Factor'])) {
                    $fields[] = $r["_source"]['USP']['JCR']['JCR']['2016'][0]['Journal_Impact_Factor'];
                } else {
                    $fields[] = "";
                } 
                
                if (!empty($r["_source"]['USP']['citescore']['citescore']['2016'][0]['citescore'])) {
                    $fields[] = $r["_source"]['USP']['citescore']['citescore']['2016'][0]['citescore'];
                } else {
                    $fields[] = "";
                }              

                
                $content[] = implode("\t",$fields);
                unset($fields);

            
            }
            echo implode("\n",$content);            

        }
    
    } elseif ($_GET["format"] == "csvThesis") {

        $file="export_bdpi.tsv";
        header('Content-type: text/tab-separated-values; charset=utf-8');
        header("Content-Disposition: attachment; filename=$file");
    
        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');
    
        if (!empty($_GET)) {
            $result_get = get::analisa_get($_GET);
            $query = $result_get['query'];  
            $limit = $result_get['limit'];
            $page = $result_get['page'];
            $skip = $result_get['skip'];
    
            if (isset($_GET["sort"])) {
                $query['sort'] = [
                    ['name.keyword' => ['order' => 'asc']],
                ];
            } else {
                $query['sort'] = [
                    ['datePublished.keyword' => ['order' => 'desc']],
                ];
            }
    
            $params = [];
            $params["index"] = $index;
            $params["type"] = $type;
            $params["size"] = 4000;
            $params["from"] = $skip;
            $params["body"] = $query; 
    
            $cursor = $client->search($params);
            $total = $cursor["hits"]["total"];
           
    
            echo "Sysno\tNúmero de chamada completo\tNúmero funcional\tNome Citação (946a)\tNome Citação (100a)\tNome Orientador (700a)\tNúm USP Orientador (946o)\tÁrea de concentração\tPrograma Grau\tIdioma\tTítulo\tResumo português\tAssuntos português\tTítulo inglês\tResumo inglês\tAno de impressão\tLocal de impressão\tData defesa\tURL\n";

            foreach ($cursor["hits"]["hits"] as $r) {
    
                $fields[] = $r['_id'];
                $fields[] = "Não foi possível coletar";

                foreach ($r["_source"]['authorUSP'] as $numUSP_aut) {
                    if (isset($numUSP_aut["numfuncional"])) {
                        $fields[] = $numUSP_aut["numfuncional"];
                    } else {
                        $fields[] = "Não preenchido corretamente";
                    }
                    
                    $fields[] = $numUSP_aut["name"];
                }
                
                
                foreach ($r["_source"]['author'] as $authors) {
                    if (empty($authors["person"]["potentialAction"])) {
                        $fields[] = $authors["person"]["name"];
                    } else {
                        $orientadores_array[] = $authors["person"]["name"]; 
                    }
                }
                if (isset($orientadores_array)) {
                    $array_orientadores = implode("; ", $orientadores_array);
                    unset($orientadores_array);
                    $fields[] = $array_orientadores;       
                } else {
                    $fields[] = "Não preenchido";
                }
               
                if (isset($r["_source"]['USP']['numfuncionalOrientador'])) {
                    foreach ($r["_source"]['USP']['numfuncionalOrientador'] as $numfuncionalOrientador) {
                        $array_numfuncionalOrientador[] = $numfuncionalOrientador;
                    }
                }    
                if (isset($array_numfuncionalOrientador)) {
                    $array_numfuncionalOrientadores = implode("; ", $array_numfuncionalOrientador);
                    unset($array_numfuncionalOrientador);
                    $fields[] = $array_numfuncionalOrientadores;       
                } else {
                    $fields[] = "Não preenchido";
                }
                


                if (isset($r["_source"]['USP']['areaconcentracao'])) {
                    $fields[] = $r["_source"]['USP']['areaconcentracao'];
                } else {
                    $fields[] = "Não preenchido";
                }
                
                $fields[] = $r["_source"]['inSupportOf'];
                $fields[] = $r["_source"]['language'][0];
                $fields[] = $r["_source"]['name'];

                if (isset($r["_source"]['description'][0])) {
                    $fields[] = $r["_source"]['description'][0];
                } else {
                    $fields[] = "Não preenchido";
                }    
                
                foreach ($r["_source"]['about'] as $subject) {
                    $subject_array[]=$subject;
                } 
                $array_subject = implode("; ", $subject_array);
                unset($subject_array);
                $fields[] = $array_subject;
                
                if (isset($r["_source"]['alternateName'])) {
                    $fields[] = $r["_source"]['alternateName'];
                } else {
                    $fields[] = "Não preenchido";
                }

                if (isset($r["_source"]['descriptionEn'])) {
                    foreach ($r["_source"]['descriptionEn'] as $descriptionEn) {
                        $descriptionEn_array[] = $descriptionEn;   
                    }
                    $array_descriptionEn = implode(" ", $descriptionEn_array);
                    unset($descriptionEn_array);
                    $fields[] = $array_descriptionEn;
                } else {
                    $fields[] = "Não preenchido";
                }
                
                $fields[] = $r["_source"]['datePublished'];

                $fields[] = $r["_source"]['publisher']['organization']['location'];

                $fields[] = $r["_source"]['dateCreated'];

                if (isset($r["_source"]['url'])) {
                    foreach ($r["_source"]['url'] as $url) {
                        $url_array[] = $url;
                    }
                    $array_url = implode("| ", $url_array);
                    unset($url_array);
                    $fields[] = $array_url;
                }    
                
                
                // $content[] = implode("\t", $fields);
                
                echo implode("\t", $fields)."\n";
                flush();

                unset($fields);
            
            }
            // echo implode("\n", $content);
    
        }
} elseif ($_GET["format"] == "capesprint") {

    function createTableDSpace($r)
    {
        unset($fields);
        $fields[] = $r['_id'];
        $fields[] = implode(',', $r['_source']['tag']);
        $fields[] = "collection";
        if (!empty($r["_source"]["tipo"])) {
            $fields[] = $r["_source"]["tipo"];
        } else {
            $fields[] = "Sem tipo";
        }
        if (!empty($r["_source"]["datePublished"])) {
            $fields[] = $r["_source"]["datePublished"];
        } else {
            $fields[] = "Sem data";
        }
        if (!empty($r["_source"]["doi"])) {
            $fields[] = $r["_source"]["doi"];
        } else {
            $fields[] = "Sem DOI";
        }
        if (!empty($r["_source"]["language"])) {
            $fields[] = $r["_source"]["language"];
        } else {
            $fields[] = "Sem idioma";
        }
        if (!empty($r["_source"]["name"])) {
            $fields[] = str_replace('"', '', $r["_source"]["name"]);
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["alternateName"])) {
            $fields[] = $r["_source"]["alternateName"];
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["about"])) {
            foreach ($r["_source"]["about"] as $subject) {
                $subject_array[] = str_replace(array("\r", "\n"), '', trim(str_replace('"', '', $subject)));
            }
            $fields[] = implode("||", $subject_array);
            unset($subject_array);
        } else {
            $fields[] = "Sem assunto cadastrado";
        }
        if (!empty($r["_source"]["author"])) {
            foreach ($r["_source"]["author"] as $authors) {
                $authors_array[] = trim($authors["person"]["name"]);
            }
            $fields[] = implode("||", $authors_array);
            unset($authors_array);
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["institutions"])) {
            $fields[] = str_replace(array("\r", "\n"), '', implode("||", $r["_source"]["institutions"]));
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["publisher"]["organization"]["name"])) {
            $fields[] = $r["_source"]["publisher"]["organization"]["name"];
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["isPartOf"]["name"])) {
            $fields[] = $r["_source"]["isPartOf"]["name"];
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["isPartOf"]["volume"])) {
            $fields[] = $r["_source"]["isPartOf"]["volume"];
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["isPartOf"]["fasciculo"])) {
            $fields[] = $r["_source"]["isPartOf"]["fasciculo"];
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["isPartOf"]["issn"])) {
            $fields[] = $r["_source"]["isPartOf"]["issn"];
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["pageStart"]) && !empty($r["_source"]["pageEnd"])) {
            $fields[] = '' . $r["_source"]["pageStart"] . '-' . $r["_source"]["pageEnd"] . '';
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["funder"])) {
            foreach ($r["_source"]["funder"] as $funders) {
                $funders_array[] = str_replace(array("\r", "\n"), '', trim($funders["name"]));
            }
            $fields[] = implode("||", $funders_array);
            unset($funders_array);
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["funder"])) {
            foreach ($r["_source"]["funder"] as $fundersID) {
                if (isset($fundersID["projectNumber"])) {
                    $fundersID_array[] = '' . str_replace(array("\r", "\n"), '', trim($fundersID["name"])) . ':' . $fundersID["projectNumber"] . '';
                }
            }
            if (isset($fundersID_array)) {
                $fields[] = implode("||", $fundersID_array);
                unset($fundersID_array);
            } else {
                $fields[] = "N/D";
            }
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["description"])) {
            $fields[] = str_replace(array("\r", "\n"), '', trim($r["_source"]["description"]));
        } else {
            $fields[] = "N/D";
        }
        if (!empty($r["_source"]["unidade"])) {
            $fields[] = $r["_source"]["unidade"];
        } else {
            $fields[] = "N/D";
        }
        $fields[] = strip_tags(Exporters::citation($r, "ABNT"));

        $content = implode("\t", $fields);
        unset($fields);
        return $content;
    }

    $file = "exportDspace.tsv";
    header('Content-type: text/tab-separated-values; charset=utf-8');
    header("Content-Disposition: attachment; filename=$file");

    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    include('inc/config.php');
    include('inc/functions.php');

    if (!empty($_GET)) {
        $result_get = Requests::getParser($_GET);
        $query = $result_get['query'];
        $limit = $result_get['limit'];
        $page = $result_get['page'];
        $skip = $result_get['skip'];

        if (isset($_GET["sort"])) {
            $query['sort'] = [
                ['name.keyword' => ['order' => 'asc']],
            ];
        } else {
            $query['sort'] = [
                ['datePublished.keyword' => ['order' => 'desc']],
            ];
        }

        $params = [];
        if (isset($_GET["alternativeIndex"])) {
            $params["index"] = $_GET["alternativeIndex"];
        } else {
            $params["index"] = $index;
        }
        $params["size"] = 50;
        $params["scroll"] = "30s";
        $params["body"] = $query;

        $cursor = $client->search($params);
        $total = $cursor["hits"]["total"];

        $content[] = "id\ttag\tcollection\tdc.type\tdc.date.issued\tdc.identifier.doi\tdc.language.iso\tdc.title\tdc.title.alternative\tdc.subject\tdc.contributor.author\tdc.description.affiliation\tdc.publisher\tdc.relation.ispartof\tdc.citation.volume\tdc.citation.issue\tdc.identifier.issn\tdc.format.extent\tdc.description.sponsorship\tdc.description.sponsorshipID\tdc.description.abstract\tABNT";

        foreach ($cursor["hits"]["hits"] as $r) {
            $content[] = createTableDSpace($r);
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
                $content[] = createTableDSpace($r);
            }
        }
        echo implode("\n", $content);
    }

} elseif ($_GET["format"] == "dspace") {

        function createTableDSpace($r) {
            unset($fields);
            $fields[] = $r['_id'];
            $fields[] = "collection";
            if(!empty($r["_source"]["tipo"])) {
                $fields[] = $r["_source"]["tipo"];
            } else {
                $fields[] = "Sem tipo";
            }
            if(!empty($r["_source"]["datePublished"])) {
                $fields[] =$r["_source"]["datePublished"];
            } else {
                $fields[] = "Sem data";
            }
            if(!empty($r["_source"]["doi"])) {
                $fields[] = $r["_source"]["doi"];
            } else {
                $fields[] = "Sem DOI";
            }
            if(!empty($r["_source"]["language"])) {
                $fields[] = $r["_source"]["language"];
            } else {
                $fields[] = "Sem idioma";
            }
            if (!empty($r["_source"]["name"])) {
                $fields[] = str_replace('"','', $r["_source"]["name"]);
            } else {
                $fields[] = "N/D";
            }
            if (!empty($r["_source"]["alternateName"])) {
                $fields[] = $r["_source"]["alternateName"];
            } else {
                $fields[] = "N/D";
            }
            if (!empty($r["_source"]["about"])) {
                foreach ($r["_source"]["about"] as $subject) {
                    $subject_array[]= str_replace(array("\r", "\n"), '', trim(str_replace('"','',$subject)));
                }
                $fields[] = implode("||", $subject_array);
                unset($subject_array);
            } else {
                $fields[] = "Sem assunto cadastrado";
            }
            if (!empty($r["_source"]["author"])) {
                foreach ($r["_source"]["author"] as $authors) {
                    $authors_array[]= trim($authors["person"]["name"]);
                }
                $fields[] = implode("||",$authors_array);
                unset($authors_array);
            } else {
                $fields[] = "N/D";
            }
            if(!empty($r["_source"]["institutions"])) {
                $fields[] = str_replace(array("\r", "\n"), '', implode("||", $r["_source"]["institutions"]));
            } else {
                $fields[] = "N/D";
            }            
            if(!empty($r["_source"]["publisher"]["organization"]["name"])) {
                $fields[] = $r["_source"]["publisher"]["organization"]["name"];
            } else {
                $fields[] = "N/D";
            }
            if(!empty($r["_source"]["isPartOf"]["name"])) {
                $fields[] = $r["_source"]["isPartOf"]["name"];
            } else {
                $fields[] = "N/D";
            }
            if(!empty($r["_source"]["isPartOf"]["volume"])) {
                $fields[] = $r["_source"]["isPartOf"]["volume"];
            } else {
                $fields[] = "N/D";
            }
            if(!empty($r["_source"]["isPartOf"]["fasciculo"])) {
                $fields[] = $r["_source"]["isPartOf"]["fasciculo"];
            } else {
                $fields[] = "N/D";
            }
            if(!empty($r["_source"]["isPartOf"]["issn"])) {
                $fields[] = $r["_source"]["isPartOf"]["issn"];
            } else {
                $fields[] = "N/D";
            }
            if(!empty($r["_source"]["pageStart"])&&!empty($r["_source"]["pageEnd"])) {
                $fields[] = ''.$r["_source"]["pageStart"].'-'.$r["_source"]["pageEnd"].'';
            } else {
                $fields[] = "N/D";
            }
            if (!empty($r["_source"]["funder"])) {
                foreach ($r["_source"]["funder"] as $funders) {
                    $funders_array[]= str_replace(array("\r", "\n"), '', trim($funders["name"]));
                }
                $fields[] = implode("||",$funders_array);
                unset($funders_array);
            } else {
                $fields[] = "N/D";
            }
            if (!empty($r["_source"]["funder"])) {
                foreach ($r["_source"]["funder"] as $fundersID) {
                    if (isset($fundersID["projectNumber"])){
                        $fundersID_array[]= ''.str_replace(array("\r", "\n"), '', trim($fundersID["name"])).':'.$fundersID["projectNumber"].'';
                    }
                }
                if (isset($fundersID_array)) {
                    $fields[] = implode("||",$fundersID_array);
                    unset($fundersID_array);
                } else {
                    $fields[] = "N/D";
                }
            } else {
                $fields[] = "N/D";
            }            
            if(!empty($r["_source"]["description"])) {
                $fields[] = str_replace(array("\r", "\n"), '', trim($r["_source"]["description"]));
            } else {
                $fields[] = "N/D";
            }            

            $content = implode("\t", $fields);
            unset($fields);
            return $content;
        }

        $file="exportDspace.tsv";
        header('Content-type: text/tab-separated-values; charset=utf-8');
        header("Content-Disposition: attachment; filename=$file");
    
        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');

        if (!empty($_GET)) {
            $result_get = Requests::getParser($_GET);
            $query = $result_get['query'];
            $limit = $result_get['limit'];
            $page = $result_get['page'];
            $skip = $result_get['skip'];

            if (isset($_GET["sort"])) {
                $query['sort'] = [
                    ['name.keyword' => ['order' => 'asc']],
                ];
            } else {
                $query['sort'] = [
                    ['datePublished.keyword' => ['order' => 'desc']],
                ];
            }

            $params = [];
            if (isset($_GET["alternativeIndex"])) {
                $params["index"] = $_GET["alternativeIndex"];
            } else {
                $params["index"] = $index;
            }
            $params["size"] = 50;
            $params["scroll"] = "30s";
            $params["body"] = $query;

            $cursor = $client->search($params);
            $total = $cursor["hits"]["total"];

            $content[] = "id\tcollection\tdc.type\tdc.date.issued\tdc.identifier.doi\tdc.language.iso\tdc.title\tdc.title.alternative\tdc.subject\tdc.contributor.author\tdc.description.affiliation\tdc.publisher\tdc.relation.ispartof\tdc.citation.volume\tdc.citation.issue\tdc.identifier.issn\tdc.format.extent\tdc.description.sponsorship\tdc.description.sponsorshipID\tdc.description.abstract";

            foreach ($cursor["hits"]["hits"] as $r) {
                $content[] = createTableDSpace($r);
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
                    $content[] = createTableDSpace($r);
                }
            }
            echo implode("\n", $content);

        }

    } elseif ($_GET["format"] == "authorNetwork") {


        function createNetwork($r) {
            unset($contentAuthor);
            foreach ($r["_source"]['author'] as $author) {
                unset($fields);
                $fields[] = hash('crc32', $r['_id']);
                $fields[] = $author['person']['name'];
                $fields[] = '['.$r['_source']['datePublished'].','.$r['_source']['datePublished'].']';
                $contentAuthor[] = implode("\t", $fields);
                unset($fields);
            }
            
            $content = implode("\n", $contentAuthor);
            unset($contentAuthor);
            return $content;
        }

        $file="exportAuthorNetwork.tsv";
        header('Content-type: text/tab-separated-values; charset=utf-8');
        header("Content-Disposition: attachment; filename=$file");
    
        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');

        if (!empty($_GET)) {
            $result_get = Requests::getParser($_GET);
            $query = $result_get['query'];
            $limit = $result_get['limit'];
            $page = $result_get['page'];
            $skip = $result_get['skip'];

            $params = [];
            $params["index"] = $index;
            $params["size"] = 10;
            $params["scroll"] = "30s";
            $params["body"] = $query;

            $cursor = $client->search($params);
            $total = $cursor["hits"]["total"];

            $content[] = "Source\tTarget\tYear";

            foreach ($cursor["hits"]["hits"] as $r) {
                $content[] = createNetwork($r);
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
                    $content[] = createNetwork($r);
                }
            }
            echo implode("\n", $content);

        }

    } elseif ($_GET["format"] == "authorNetworkWithoutPapers") {


        function createNetwork($r) {
            foreach ($r["_source"]['author'] as $author) {
                //print_r($author);
                unset($r["_source"]['author'][0]);
                //print_r($r["_source"]['author']);
                foreach ($r["_source"]['author'] as $authorArray) {
                    //print_r($authorArray);
                    if ($author['person']['name'] != $authorArray['person']['name']){
                        $fields[] = $author['person']['name'];
                        $fields[] = $authorArray['person']['name'];
                        $fields[] = '['.$r['_source']['datePublished'].','.$r['_source']['datePublished'].']';
                    }
                    if (isset($fields)) {
                        //print_r($fields);
                        $contentAuthor[] = implode("\t", $fields);
                        unset($fields);
                    }
                }
            }
            if (isset($contentAuthor)) {
                $content = implode("\n", $contentAuthor);
                unset($contentAuthor);
            }
            if (isset($content)) {
                return $content;
            } else {
                return "";
            }
        }

        $file="exportAuthorNetworkWithoutPapers.tsv";
        header('Content-type: text/tab-separated-values; charset=utf-8');
        header("Content-Disposition: attachment; filename=$file");
    
        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');

        if (!empty($_GET)) {
            $result_get = Requests::getParser($_GET);
            $query = $result_get['query'];
            $limit = $result_get['limit'];
            $page = $result_get['page'];
            $skip = $result_get['skip'];

            $params = [];
            $params["index"] = $index;
            $params["size"] = 10;
            $params["scroll"] = "30s";
            $params["body"] = $query;

            $cursor = $client->search($params);
            $total = $cursor["hits"]["total"];

            $content[] = "Source\tTarget\tYear";

            foreach ($cursor["hits"]["hits"] as $r) {
                $content[] = createNetwork($r);
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
                    $content[] = createNetwork($r);
                }
            }
            echo implode("\n", $content);

        }        

    } elseif ($_GET["format"] == "ppgNetworkWithoutPapers") {


        function createNetwork($r) {
            foreach ($r["_source"]['vinculo'] as $vinculo) {
                unset($r["_source"]['vinculo'][0]);
                foreach ($r["_source"]['vinculo'] as $vinculoArray) {
                    if ($vinculo['ppg_nome'] != $vinculoArray['ppg_nome']){
                        $fields[] = $vinculo['ppg_nome'][0];
                        $fields[] = $vinculoArray['ppg_nome'][0];
                        $fields[] = '['.$r['_source']['datePublished'].','.$r['_source']['datePublished'].']';
                    }
                    if (isset($fields)) {
                        $contentVinculo[] = implode("\t", $fields);
                        unset($fields);
                    }
                }
            }
            if (isset($contentVinculo)) {
                $content = implode("\n", $contentVinculo);
                unset($contentVinculo);
            }
            if (isset($content)) {
                return $content;
            } else {
                return "";
            }
        }

        $file="exportPPGSNetworkWithoutPapers.tsv";
        header('Content-type: text/tab-separated-values; charset=utf-8');
        header("Content-Disposition: attachment; filename=$file");
    
        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');

        if (!empty($_GET)) {
            $result_get = Requests::getParser($_GET);
            $query = $result_get['query'];
            $limit = $result_get['limit'];
            $page = $result_get['page'];
            $skip = $result_get['skip'];

            $params = [];
            $params["index"] = $index;
            $params["size"] = 10;
            $params["scroll"] = "30s";
            $params["body"] = $query;

            $cursor = $client->search($params);
            $total = $cursor["hits"]["total"];

            $content[] = "Source\tTarget\tYear";

            foreach ($cursor["hits"]["hits"] as $r) {
                $content[] = createNetwork($r);
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
                    $content[] = createNetwork($r);
                }
            }
            echo implode("\n", $content);
        }

    } elseif($_GET["format"] == "ris") {

        $file="export_bdpi.ris";
        header('Content-type: application/x-research-info-systems');
        header("Content-Disposition: attachment; filename=$file");

        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');


        $result_get = Requests::getParser($_GET);
        $query = $result_get['query'];  
        $limit = $result_get['limit'];
        $page = $result_get['page'];
        $skip = $result_get['skip'];

        if (isset($_GET["sort"])) {
            $query['sort'] = [
                ['name.keyword' => ['order' => 'asc']],
            ];
        } else {
            $query['sort'] = [
                ['datePublished.keyword' => ['order' => 'desc']],
            ];
        }

        $params = [];
        $params["index"] = $index;
        $params["size"] = 10000;
        $params["from"] = $skip;
        $params["body"] = $query; 

        $cursor = $client->search($params); 

        foreach ($cursor["hits"]["hits"] as $r) { 
            /* Exportador RIS */
            $record_blob[] = Exporters::RIS($r);
        }
        foreach ($record_blob as $record) {
            $record_array = explode('\n',$record);
            echo implode("\n",$record_array);
        }
    
    } elseif ($_GET["format"] == "alephseq") {

        $file="export_bdpi.seq";
        header("Content-Disposition: attachment; filename=$file");

        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include 'inc/config.php'; 
        include 'inc/functions.php';

        if (strpos($_GET["search"][0], '_id') !== false) {
            $query["query"]["terms"]["_id"][] = str_replace("_id:", "", $_GET["search"][0]);
            $result_get['page'] = 1;
            $result_get['limit'] = 20;
            $result_get['skip'] = 0;
        } else {
            $result_get = get::analisa_get($_GET);
            $query = $result_get['query'];
        }

        $limit = $result_get['limit'];
        $page = $result_get['page'];
        $skip = $result_get['skip'];

        if (isset($_GET["sort"])) {
            $query['sort'] = [
                ['name.keyword' => ['order' => 'asc']],
            ];
        } else {
            // $query['sort'] = [
            //     ['datePublished.keyword' => ['order' => 'desc']],
            // ];
        }

        $params = [];
        $params["index"] = $index;
        $params["size"] = 10000;
        $params["from"] = $skip;
        $params["body"] = $query; 

        $cursor = $client->search($params);

        foreach ($cursor["hits"]["hits"] as $r) { 
            /* Exportador RIS */
            $record_blob[] = Exporters::alephseq($r);
        }
        foreach ($record_blob as $record) {
            $record_array = explode('\n', $record);
            echo implode("\n", $record_array);
        }   

    } elseif ($_GET["format"] == "bibtex") {

        $file="export_bdpi.bib";
        header('Content-type: text/plain');
        header("Content-Disposition: attachment; filename=$file");



        // Set directory to ROOT
        chdir('../');
        // Include essencial files
        include('inc/config.php'); 
        include('inc/functions.php');


        $result_get = Requests::getParser($_GET);
        $query = $result_get['query'];
        $limit = $result_get['limit'];
        $page = $result_get['page'];
        $skip = $result_get['skip'];

        if (isset($_GET["sort"])) {
            $query['sort'] = [
                ['name.keyword' => ['order' => 'asc']],
            ];
        } else {
            $query['sort'] = [
                ['datePublished.keyword' => ['order' => 'desc']],
            ];
        }

        $params = [];
        $params["index"] = $index;
        $params["size"] = 50;
        $params["scroll"] = "30s";
        $params["body"] = $query;

        $cursor = $client->search($params);
        foreach ($cursor["hits"]["hits"] as $r) {
            /* Exportador RIS */
            $record_blob[] = Exporters::bibtex($r);
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
                /* Exportador RIS */
                $record_blob[] = Exporters::bibtex($r);
            }
        }

        foreach ($record_blob as $record) {
            $record_array = explode('\n', $record);
            echo "\n";
            echo "\n";
            echo implode("\n", $record_array);
        }

    } else {
        echo "Não foi informado nenhum formato";
    }

?>