<?php

    // $file="export_projetos_bdpi.tsv";
    // header('Content-type: text/tab-separated-values; charset=utf-8');
    // header("Content-Disposition: attachment; filename=$file");


    function processProject(Array $project) {
        //echo "<pre>".print_r($project, true)."</pre>";
        $projectArray = [];
        if (isset($project['attributes'])) {
            $projectArray[0] = $project['attributes']['NOME-DO-PROJETO'];
            $projectArray[1] = $project['attributes']['ANO-INICIO'];
            $projectArray[2] = $project['attributes']['ANO-FIM'];
            $projectArray[3] = $project['attributes']['SITUACAO'];
            $projectArray[4] = $project['attributes']['DESCRICAO-DO-PROJETO'];
        } else {
            foreach ($project as $key => $projeto_de_pesquisa) {
                $projectArray[0] = $projeto_de_pesquisa['attributes']['NOME-DO-PROJETO'];
                $projectArray[1] = $projeto_de_pesquisa['attributes']['ANO-INICIO'];
                $projectArray[2] = $projeto_de_pesquisa['attributes']['ANO-FIM'];
                $projectArray[3] = $projeto_de_pesquisa['attributes']['SITUACAO'];
                $projectArray[4] = $projeto_de_pesquisa['attributes']['DESCRICAO-DO-PROJETO'];
            }
        }
        return $projectArray;
    }


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

    $content[] = "id\ttítulo\tanoinicio\tanofim\tsituacao\tdescricaodoprojeto";

    foreach ($cursor["hits"]["hits"] as $r) {
        foreach ($r['_source']['atuacoes_profissionais'] as $key => $atuacoes_profissionais) {
            foreach ($atuacoes_profissionais as $key => $atuacao_profissional) {
                $dataJson = str_replace('@', '', json_encode($atuacao_profissional));
                $atuacao_profissional = json_decode($dataJson, true);
                $projetoArray = [];
                if (isset($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'])) {
                    if (isset($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA'])) {
                        $projetoArray = processProject($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA']);
                        $content[] = $r['_id']."\t".implode("\t", $projetoArray);
                        unset($projetoArray);
                    } else {
                        foreach ($atuacao_profissional['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'] as $key => $participacao_em_projeto) {
                            
                            if (isset($participacao_em_projeto['PROJETO-DE-PESQUISA']['attributes'])) {
                                $projetoArray = processProject($participacao_em_projeto['PROJETO-DE-PESQUISA']);
                            } else {
                                if (isset($participacao_em_projeto['PROJETO-DE-PESQUISA'])) {
                                    foreach ($participacao_em_projeto['PROJETO-DE-PESQUISA'] as $key => $projeto_de_pesquisa) {
                                        $projetoArray = processProject($projeto_de_pesquisa);
                                        $content[] = $r['_id']."\t".implode("\t", $projetoArray);
                                        unset($projetoArray);
                                    }
                                } else {}
                            }
                            if (isset($projetoArray)) {
                                $content[] = $r['_id']."\t".implode("\t", $projetoArray);
                                unset($projetoArray);
                            }
                        }
                    }
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