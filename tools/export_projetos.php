<?php

    // $file="export_projetos_bdpi.tsv";
    // header('Content-type: text/tab-separated-values; charset=utf-8');
    // header("Content-Disposition: attachment; filename=$file");

    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    include('inc/config.php'); 
    include('inc/functions.php');

    $params = [];
    $query["query"]["query_string"]["query"] = "*";
    $params["index"] = $index_cv;
    $params["size"] = 2;
    $params["_source"] = ["atuacoes_profissionais"];
    //$params["fields"] = ['atuacoes_profissionais'];
    $params["scroll"] = "30s";
    $params["body"] = $query;

    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    $content[] = "id\ttítulo";

    foreach ($cursor["hits"]["hits"] as $r) {
        foreach ($r['_source']['atuacoes_profissionais'] as $key => $atuacoes_profissionais) {
            foreach ($atuacoes_profissionais as $key => $atuacao_profissional) {
                $dataJson = str_replace('@', '', json_encode($atuacao_profissional));
                $atuacao_profissional = json_decode($dataJson, true);
                //echo "<pre>".print_r($atuacao_profissional, true)."</pre>";
                $projetoArray = [];
                if (isset($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'])) {
                    if (isset($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA'])) {
                        //echo "<pre>".print_r($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA'], true)."</pre>";
                        $projetoArray[0] = $atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA']['attributes']['NOME-DO-PROJETO'];
                        $content[] = $r['_id']."\t".implode("\t", $projetoArray);
                        unset($projetoArray);
                    } else {
                        foreach ($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'] as $key => $participacao_em_projeto) {
                            if (isset($participacao_em_projeto['PROJETO-DE-PESQUISA']['attributes'])) {
                                $projetoArray[0] = $participacao_em_projeto['PROJETO-DE-PESQUISA']['attributes']['NOME-DO-PROJETO'];
                            } else {
                                if (isset($participacao_em_projeto['PROJETO-DE-PESQUISA'])) {
                                    foreach ($participacao_em_projeto['PROJETO-DE-PESQUISA'] as $key => $projeto_de_pesquisa) {
                                        $projetoArray[0] = $projeto_de_pesquisa['attributes']['NOME-DO-PROJETO'];
                                        $content[] = $r['_id']."\t".implode("\t", $projetoArray);
                                        unset($projetoArray);
                                    }
                                } else {
                                    
                                }

                                //echo "<pre>".print_r($participacao_em_projeto['PROJETO-DE-PESQUISA'], true)."</pre><br/><br/><br/><br/>";
                            }
                            if (isset($projetoArray)) {
                                $content[] = $r['_id']."\t".implode("\t", $projetoArray);
                                unset($projetoArray);
                            }
                        }
                    }

        //             $projectArray = [];
        //             foreach ($atuacao_profissional_1['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'] as $key => $participacao_em_projeto) {
        //                 foreach ($participacao_em_projeto['PROJETO-DE-PESQUISA'] as $key => $projeto_de_pesquisa) {
        //                     if (!empty($projeto_de_pesquisa['@attributes'])) {
        //                         //echo "<pre>".print_r($projeto_de_pesquisa, true)."</pre>";
        //                         $projectArray[] = $projeto_de_pesquisa['@attributes']['NOME-DO-PROJETO'];
        //                     } else {
        //                         //$projectArray[] = $projeto_de_pesquisa['NOME-DO-PROJETO'];
        //                         echo "<pre>".print_r($key, true)."</pre>";
        //                         echo "<pre>".print_r($projeto_de_pesquisa, true)."</pre>";
        //                     }
        //                 }
        //                 //echo "<pre>".print_r($projeto, true)."</pre>";
        //                 //print_r($projeto['PROJETO-DE-PESQUISA']['@attributes']['NOME-DO-PROJETO']);
        //             }
        //             $content[] = $r['_id']."\t".implode(" | ", $projectArray);
        //             unset($projectArray);

                }

            }

        }
    }




    // while (isset($cursor['hits']['hits']) && count($cursor['hits']['hits']) > 0) {
    //     $scroll_id = $cursor['_scroll_id'];
    //     $cursor = $client->scroll(
    //         [
    //             "scroll_id" => $scroll_id,
    //             "scroll" => "30s"
    //         ]
    //     );

    //     foreach ($cursor["hits"]["hits"] as $r) {
    //         $content[] = createTableDSpace($r);
    //     }
    // }
    echo implode("\n<br/>", $content);