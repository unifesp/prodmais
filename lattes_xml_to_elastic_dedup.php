<?php

require 'inc/config.php';
require 'inc/functions.php';

function get_curriculum($identificador)
{
    try {
        global $index_cv;
        global $client;

        $params = [
            'index' => $index_cv,
            'id' => $identificador
        ];

        // Get doc at /my_index/_doc/my_id
        $response = $client->get($params);

        return $response;
    } catch (\Exception $e) {
        //echo $e->getMessage();
    }
}

function comparaprod_doi($doi)
{
    global $index;
    global $type;
    global $client;
    $query['query']['query_string']['query'] = "doi:\"$doi\"";
    $params = [];
    $params['index'] = $index;
    $params['type'] = $type;
    $params['size'] = 100;
    $params['body'] = $query;
    $cursor = $client->search($params);
    $total = $cursor['hits']['total']['value'];

    if ($total >= 1) {
        foreach ($cursor['hits']['hits'] as $r) {
        }
        return $r;
    } else {
        return 'Não encontrado';
    }
}

function comparaprod_title($doc)
{
    global $index;
    global $client;

    $query['query']['bool']['filter'][]["term"]["tipo.keyword"] = $doc["doc"]["tipo"];
    $query['query']['bool']['filter'][]["term"]["datePublished.keyword"] = $doc["doc"]["datePublished"];
    if (isset($doc["doc"]['author'][0]['person']['name'])) {
        if (!is_null($doc["doc"]['author'][0]['person']['name'])) {
            $query["query"]["bool"]["must"]["query_string"]["query"] = '(name:"' . $doc["doc"]["name"] . '"^5) AND (author:' . $doc["doc"]['author'][0]['person']['name'] . ')';
        } else {
            $query["query"]["bool"]["must"]["query_string"]["query"] = '(name:"' . $doc["doc"]["name"] . '"^5)';
        }
    } else {
        $query["query"]["bool"]["must"]["query_string"]["query"] = '(name:"' . $doc["doc"]["name"] . '"^5)';
    }

    if (!empty($doc['doc']['isPartOf']['name'])) {
        $query['query']['bool']['filter'][]["term"]["isPartOf.name.keyword"] = $doc['doc']['isPartOf']['name'];
    }
    if (!empty($doc['doc']['publisher']['organization']['name'])) {
        $query['query']['bool']['filter'][]["term"]["publisher.organization.name.keyword"] = $doc['doc']['publisher']['organization']['name'];
    }
    if (!empty($doc['doc']['isbn'])) {
        $query['query']['bool']['filter'][]["term"]["isbn.keyword"] = $doc['doc']['isbn'];
    }

    $params = [];
    $params['index'] = $index;
    $params['size'] = 10;
    $params['body'] = $query;
    $cursor = $client->search($params);
    $total = $cursor['hits']['total']['value'];

    foreach ($cursor['hits']['hits'] as $r) {
    }

    if ($total >= 1) {
        return $r;
    } else {
        return 'Não encontrado';
    }
}

function processaAutoresLattes($autores_array)
{
    $i = 0;
    if (is_array($autores_array)) {
        foreach ($autores_array as $autor) {
            $autor = get_object_vars($autor);
            $array_result['doc']['author'][$i]['person']['name'] = $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'];
            $array_result['doc']['author'][$i]['nomeParaCitacao'] = $autor['@attributes']['NOME-PARA-CITACAO'];
            $array_result['doc']['author'][$i]['ordemDeAutoria'] = $autor['@attributes']['ORDEM-DE-AUTORIA'];
            if (isset($autor['@attributes']['NRO-ID-CNPQ'])) {
                $array_result['doc']['author'][$i]['nroIdCnpq'] = $autor['@attributes']['NRO-ID-CNPQ'];
            }

            $i++;
        }
    } else {
        $autor = get_object_vars($autores_array);
        $array_result['doc']['author'][$i]['person']['name'] = $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'];
        $array_result['doc']['author'][$i]['nomeParaCitacao'] = $autor['@attributes']['NOME-PARA-CITACAO'];
        $array_result['doc']['author'][$i]['ordemDeAutoria'] = $autor['@attributes']['ORDEM-DE-AUTORIA'];
        if (isset($autor['@attributes']['NRO-ID-CNPQ'])) {
            $array_result['doc']['author'][$i]['nroIdCnpq'] = $autor['@attributes']['NRO-ID-CNPQ'];
        }
    }

    if (!empty($array_result)) {
        return $array_result;
    } else {
        $array_empty = [];
        return $array_empty;
    }
    unset($array_result);
}

function processaPalavrasChaveLattes($palavras_chave)
{
    $palavras_chave = get_object_vars($palavras_chave);
    foreach (range(1, 6) as $number) {
        if (!empty($palavras_chave['@attributes']["PALAVRA-CHAVE-$number"])) {
            $array_result['doc']['about'][] = $palavras_chave['@attributes']["PALAVRA-CHAVE-$number"];
        }
    }
    if (isset($array_result)) {
        return $array_result;
    }
    unset($array_result);
}

function processaPalavrasChaveFormacaoLattes($palavras_chave)
{
    $palavras_chave = get_object_vars($palavras_chave);
    foreach (range(1, 6) as $number) {
        if (!empty($palavras_chave['@attributes']["PALAVRA-CHAVE-$number"])) {
            $array_result["palavras_chave"][] = $palavras_chave["@attributes"]["PALAVRA-CHAVE-$number"];
        }
    }
    if (isset($array_result)) {
        return $array_result;
    }
    unset($array_result);
}

function processaAreaDoConhecimentoLattes($areas_do_conhecimento)
{
    $i = 0;
    foreach ($areas_do_conhecimento as $ac) {
        $ac = get_object_vars($ac);
        foreach ($ac as $ac_record) {
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeGrandeAreaDoConhecimento"] = $ac_record["NOME-GRANDE-AREA-DO-CONHECIMENTO"];
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeDaAreaDoConhecimento"] = $ac_record["NOME-DA-AREA-DO-CONHECIMENTO"];
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeDaSubAreaDoConhecimento"] = $ac_record["NOME-DA-SUB-AREA-DO-CONHECIMENTO"];
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeDaEspecialidade"] = $ac_record["NOME-DA-ESPECIALIDADE"];
        }
        $i++;
    }
    if (!empty($array_result)) {
        return $array_result;
    } else {
        $array_empty = [];
        return $array_empty;
    }
    unset($array_result);
}

function processaAreaDoConhecimentoFormacaoLattes($areas_do_conhecimento)
{
    $i = 0;
    foreach ($areas_do_conhecimento as $ac) {
        $ac = get_object_vars($ac);
        foreach ($ac as $ac_record) {
            $array_result["area_do_conhecimento"][$i]["nomeGrandeAreaDoConhecimento"] = $ac_record["NOME-GRANDE-AREA-DO-CONHECIMENTO"];
            $array_result["area_do_conhecimento"][$i]["nomeDaAreaDoConhecimento"] = $ac_record["NOME-DA-AREA-DO-CONHECIMENTO"];
            $array_result["area_do_conhecimento"][$i]["nomeDaSubAreaDoConhecimento"] = $ac_record["NOME-DA-SUB-AREA-DO-CONHECIMENTO"];
            $array_result["area_do_conhecimento"][$i]["nomeDaEspecialidade"] = $ac_record["NOME-DA-ESPECIALIDADE"];
        }
        $i++;
    }
    return $array_result;
    unset($array_result);
}

function construct_vinculo($request, $curriculo)
{
    // Vinculo
    if (isset($doc["doc"]["vinculo"])) {
        $i_vinculo = count($doc["doc"]["vinculo"]);
        $i_vinculo++;
    } else {
        $i_vinculo = 0;
    }
    $doc["doc"]["vinculo"][$i_vinculo]["nome"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-COMPLETO'};
    $doc["doc"]["vinculo"][$i_vinculo]["lattes_id"] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
    if (isset($request['instituicao'])) {
        $doc["doc"]["vinculo"][$i_vinculo]["instituicao"] = explode("|", rtrim($request['instituicao']));
    }
    if (isset($request['unidade'])) {
        $doc["doc"]["vinculo"][$i_vinculo]["unidade"] = explode("|", rtrim($request['unidade']));
    }
    if (isset($request['departamento'])) {
        $doc["doc"]["vinculo"][$i_vinculo]["departamento"] = explode("|", $request['departamento']);
    }
    if (isset($request['numfuncional'])) {
        $doc["doc"]["vinculo"][$i_vinculo]["numfuncional"] = $request['numfuncional'];
    }
    if (isset($request['tipvin'])) {
        $doc["doc"]["vinculo"][$i_vinculo]["tipvin"] = explode("|", $request['tipvin']);
    }
    if (isset($request['divisao'])) {
        $doc["doc"]["vinculo"][$i_vinculo]["divisao"] = explode("|", $request['divisao']);
    }
    if (isset($request['secao'])) {
        $doc['doc']["vinculo"][$i_vinculo]['secao'] = explode("|", $request['secao']);
    }
    if (isset($request['ppg_nome'])) {
        $doc['doc']["vinculo"][$i_vinculo]['ppg_nome'] = explode("|", rtrim($request['ppg_nome']));
    }
    if (isset($request['area_concentracao'])) {
        $doc['doc']["vinculo"][$i_vinculo]['area_concentracao'] = explode("|", rtrim($request['area_concentracao']));
    }
    if (isset($request['ppg_capes'])) {
        $doc['doc']["vinculo"][$i_vinculo]['ppg_capes'] = explode("|", $request['ppg_capes']);
    }
    if (isset($request['genero'])) {
        $doc['doc']["vinculo"][$i_vinculo]['genero'] = $request['genero'];
    }
    if (isset($request['etnia'])) {
        $doc['doc']["vinculo"][$i_vinculo]['etnia'] = $request['etnia'];
    }
    if (isset($request['ano_ingresso'])) {
        $doc['doc']["vinculo"][$i_vinculo]['ano_ingresso'] = substr($request['ano_ingresso'], -4);
    }
    if (isset($request['desc_nivel'])) {
        $doc['doc']["vinculo"][$i_vinculo]['desc_nivel'] = explode("|", $request['desc_nivel']);
    }
    if (isset($request['desc_curso'])) {
        $doc['doc']["vinculo"][$i_vinculo]['desc_curso'] = explode("|", $request['desc_curso']);
    }
    if (isset($request['campus'])) {
        $doc['doc']["vinculo"][$i_vinculo]['campus'] = explode("|", $request['campus']);
    }
    if (isset($request['desc_gestora'])) {
        $doc['doc']["vinculo"][$i_vinculo]['desc_gestora'] = explode("|", $request['desc_gestora']);
    }

    return $doc['doc']["vinculo"];
}

function my_array_unique($array, $keep_key_assoc = false)
{
    $duplicate_keys = array();
    $tmp = array();

    foreach ($array as $key => $val) {
        // convert objects to arrays, in_array() does not support objects
        if (is_object($val))
            $val = (array) $val;

        if (!in_array($val, $tmp))
            $tmp[] = $val;
        else
            $duplicate_keys[] = $key;
    }

    foreach ($duplicate_keys as $key)
        unset($array[$key]);

    return $keep_key_assoc ? $array : array_values($array);
}

function upsert($doc, $sha256)
{
    // Comparador
    if (!empty($doc['doc']['doi'])) {
        $result_comparaprod = comparaprod_doi($doc['doc']['doi']);
        if (is_array($result_comparaprod)) {
            $result_comparaprod['_source']['vinculo'] = array_merge($result_comparaprod['_source']['vinculo'], $doc['doc']["vinculo"]);
            $result_comparaprod['_source']['vinculo'] = my_array_unique($result_comparaprod['_source']['vinculo']);
            $doc_existing['doc'] = $result_comparaprod['_source'];
            $doc_existing["doc"]["concluido"] = "Não";
            $doc_existing["doc_as_upsert"] = true;
            $resultado = Elasticsearch::update($result_comparaprod['_id'], $doc_existing);
        } else {
            if (isset($doc['doc']['instituicao']['ano_ingresso'])) {
                if (intval($doc["doc"]["datePublished"]) >= intval($doc['doc']["instituicao"]['ano_ingresso'])) {
                    $resultado = Elasticsearch::update($sha256, $doc);
                }
            } else {
                $resultado = Elasticsearch::update($sha256, $doc);
            }
        }
    } else {
        $result_comparaprod = comparaprod_title($doc);
        if (is_array($result_comparaprod)) {
            $result_comparaprod['_source']['vinculo'] = array_merge($result_comparaprod['_source']['vinculo'], $doc['doc']["vinculo"]);
            $result_comparaprod['_source']['vinculo'] = my_array_unique($result_comparaprod['_source']['vinculo']);
            $doc_existing['doc'] = $result_comparaprod['_source'];
            $doc_existing["doc"]["concluido"] = "Não";
            $doc_existing["doc_as_upsert"] = true;
            $resultado = Elasticsearch::update($result_comparaprod['_id'], $doc_existing);
        } else {
            if (isset($doc['doc']['instituicao']['ano_ingresso'])) {
                if (intval($doc["doc"]["datePublished"]) >= intval($doc['doc']["instituicao"]['ano_ingresso'])) {
                    $resultado = Elasticsearch::update($sha256, $doc);
                }
            } else {
                $resultado = Elasticsearch::update($sha256, $doc);
            }
        }
    }
    return $resultado;
}

if (!isset($_POST['numfuncional'])) {
    $_POST['numfuncional'] = null;
}
if (!isset($_GET['unidade'])) {
    $_POST['unidade'] = null;
}
if (!isset($_GET['tag'])) {
    $_POST['tag'] = null;
}

// Verifica se um arquivo foi enviado pelo formulário
if (isset($_FILES['file'])) {
    if ($_FILES['file']['name'] != "") {
        // Recebe o arquivo enviado pelo formulário
        $arquivo = $_FILES['file'];
        // Verifica se o arquivo é válido
        if ($arquivo['error'] == 0) {
            // Obtém o nome e a extensão do arquivo
            $nome = $arquivo['name'];
            $extensao = pathinfo($nome, PATHINFO_EXTENSION);

            // Verifica se o arquivo é zip ou xml
            if ($extensao == 'zip') {
                // Cria um objeto ZipArchive
                $zip = new ZipArchive();

                // Abre o arquivo zip
                if ($zip->open($arquivo['tmp_name']) === true) {
                    // Extrai o conteúdo do zip para um diretório temporário
                    $temp_dir = 'tmp/zip_' . uniqid();
                    $zip->extractTo($temp_dir);
                    $zip->close();

                    // Procura pelo arquivo curriculo.xml no diretório temporário
                    $xml_file = $temp_dir . '/curriculo.xml';
                    if (file_exists($xml_file)) {
                        echo "arquivo existe";
                        // Lê o conteúdo do arquivo xml
                        $file_xml_lattes = file_get_contents($xml_file);


                        // Remove o diretório temporário e seus arquivos
                        array_map('unlink', glob($temp_dir . '/*'));
                        rmdir($temp_dir);
                    } else {
                        // Não encontrou o arquivo curriculo.xml no zip
                        echo 'O arquivo zip não contém o arquivo curriculo.xml';
                    }
                } else {
                    // Não conseguiu abrir o arquivo zip
                    echo 'O arquivo zip é inválido ou está corrompido';
                }
            } elseif ($extensao == 'xml') {
                // Lê o conteúdo do arquivo xmlquery
                $file_xml_lattes = file_get_contents($arquivo['tmp_name']);
            } else {
                // O arquivo não é zip nem xml
                echo 'O arquivo não é zip nem xml';
            }
        } else {
            // O arquivo não foi enviado corretamente
            echo 'Ocorreu um erro ao enviar o arquivo';
        }
        $curriculo = simplexml_load_string($file_xml_lattes);
    } elseif (isset($_REQUEST['lattes_id'])) {
        $lattes_id = $_REQUEST['lattes_id'];
        if (file_exists('data/' . $lattes_id . '.xml')) {
            $xml_file = 'data/' . $lattes_id . '.xml';
            $curriculo = simplexml_load_string(file_get_contents($xml_file));
        } elseif (file_exists('data/' . $lattes_id . '.zip')) {
            $zip = new ZipArchive();
            $zip->open('data/' . $lattes_id . '.zip');
            $temp_dir = 'tmp/zip_' . uniqid();
            $zip->extractTo($temp_dir);
            $zip->close();
            // Procura pelo arquivo curriculo.xml no diretório temporário
            $xml_file = $temp_dir . '/curriculo.xml';
            $curriculo = simplexml_load_string(file_get_contents($xml_file));
            // Remove o diretório temporário e seus arquivos
            unlink($xml_file);
            rmdir($temp_dir);
        } else {
            echo "Arquivo não encontrado";
        }
    }
} elseif (isset($_REQUEST['lattes_id'])) {
    $lattes_id = $_REQUEST['lattes_id'];
    if (file_exists('data/' . $lattes_id . '.xml')) {
        $xml_file = 'data/' . $lattes_id . '.xml';
        $curriculo = simplexml_load_string(file_get_contents($xml_file));
    } elseif (file_exists('data/' . $lattes_id . '.zip')) {
        $zip = new ZipArchive();
        $zip->open('data/' . $lattes_id . '.zip');
        $temp_dir = 'tmp/zip_' . uniqid();
        $zip->extractTo($temp_dir);
        $zip->close();
        // Procura pelo arquivo curriculo.xml no diretório temporário
        $xml_file = $temp_dir . '/curriculo.xml';
        $curriculo = simplexml_load_string(file_get_contents($xml_file));
        // Remove o diretório temporário e seus arquivos
        unlink($xml_file);
        rmdir($temp_dir);
    } else {
        echo "Arquivo não encontrado";
    }
} else {
    // Nenhum arquivo foi enviado pelo formulário
    echo 'Nenhum arquivo foi enviado';
    if (isset($_REQUEST['instituicao'])) {
        $query["doc"]["instituicao"] = explode("|", rtrim($_REQUEST['instituicao']));
    }
    if (isset($_REQUEST['area_concentracao'])) {
        $query['doc']['area_concentracao'] = explode("|", rtrim($_REQUEST['area_concentracao']));
    }
    if (isset($_REQUEST['unidade'])) {
        $query["doc"]["unidade"] = explode("|", $_REQUEST['unidade']);
    }
    if (isset($_REQUEST['departamento'])) {
        $query["doc"]["departamento"] = explode("|", $_REQUEST['departamento']);
    }
    if (isset($_REQUEST['tipvin'])) {
        $query["doc"]["tipvin"] = explode("|", $_REQUEST['tipvin']);
    }
    if (isset($_REQUEST['divisao'])) {
        $query["doc"]["divisao"] = explode("|", $_REQUEST['divisao']);
    }
    if (isset($_REQUEST['secao'])) {
        $query['doc']['secao'] = explode("|", $_REQUEST['secao']);
    }
    if (isset($_REQUEST['ppg_nome'])) {
        $query['doc']['ppg_nome'] = explode("|", rtrim($_REQUEST['ppg_nome']));
    }
    if (isset($_REQUEST['ppg_capes'])) {
        $query['doc']['ppg_capes'] = explode("|", $_REQUEST['ppg_nome']);
    }
    if (isset($_REQUEST['genero'])) {
        $query['doc']['genero'] = $_REQUEST['genero'];
    }
    if (isset($_REQUEST['etnia'])) {
        $query['doc']['etnia'] = $_REQUEST['etnia'];
    }
    if (isset($_REQUEST['ano_ingresso'])) {
        $query['doc']['ano_ingresso'] = $_REQUEST['ano_ingresso'];
    }
    if (isset($_REQUEST['desc_nivel'])) {
        $query['doc']['desc_nivel'] = explode("|", $_REQUEST['desc_nivel']);
    }
    if (isset($_REQUEST['desc_curso'])) {
        $query['doc']['desc_curso'] = explode("|", $_REQUEST['desc_curso']);
    }
    if (isset($_REQUEST['campus'])) {
        $query['doc']['campus'] = explode("|", $_REQUEST['campus']);
    }
    if (isset($_REQUEST['desc_gestora'])) {
        $query['doc']['desc_gestora'] = explode("|", $_REQUEST['desc_gestora']);
    }
    //$query["doc"]["lattesID"] = "Lattes ID não encontrado";
    if (isset($_REQUEST['nome_completo'])) {
        $query["doc"]["nome_completo"] = $_REQUEST['nome_completo'];
    }
    if (isset($_REQUEST['uuid'])) {
        $id = $_REQUEST['uuid'];
    } else {
        $id = uniqid(rand(), true);
    }
    $query["doc_as_upsert"] = true;

    $resultado_curriculo = Elasticsearch::update($id, $query, $index_cv);
    //print_r($resultado_curriculo);

    unset($query);

    exit();
}

// Inicio Currículo

$identificador = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};

$result_get_curriculo = get_curriculum($identificador);

$doc_curriculo_array = [];

if (isset($result_get_curriculo["found"])) {
    if ($result_get_curriculo["found"] == true) {
        $ppg_array = $result_get_curriculo["_source"]["ppg_nome"];
        if (isset($_REQUEST['ppg_nome'])) {
            $ppg_array[] = rtrim($_REQUEST['ppg_nome']);
        }
        $doc_curriculo_array['doc']['ppg_nome'] = array_unique($ppg_array);

        if (isset($result_get_curriculo["_source"]["instituicao"])) {
            $instituicao_array[] = $result_get_curriculo["_source"]["instituicao"];
        } else {
            $instituicao_array = [];
        }
        if (isset($_REQUEST['instituicao'])) {
            $instituicao_array[] = rtrim($_REQUEST['instituicao']);
        }
        $doc_curriculo_array['doc']['instituicao'] = array_unique($instituicao_array);

        if (isset($result_get_curriculo["_source"]["area_concentracao"])) {
            $area_concentracao_array = $result_get_curriculo["_source"]["area_concentracao"];
            if (isset($_REQUEST['area_concentracao'])) {
                $area_concentracao_array[] = rtrim($_REQUEST['area_concentracao']);
            }
            if (!is_null($area_concentracao_array)) {
                $doc_curriculo_array['doc']['area_concentracao'] = array_unique($area_concentracao_array);
            }
        }
    }
} else {
    if (isset($_REQUEST['ppg_nome'])) {
        $doc_curriculo_array['doc']['ppg_nome'] = explode("|", rtrim($_REQUEST['ppg_nome']));
    }
    if (isset($_REQUEST['instituicao'])) {
        $doc_curriculo_array['doc']['instituicao'] = explode("|", rtrim($_REQUEST['instituicao']));
    }
    if (isset($_REQUEST['area_concentracao'])) {
        $doc_curriculo_array['doc']['area_concentracao'] = explode("|", rtrim($_REQUEST['area_concentracao']));
    }
};




$doc_curriculo_array["doc"]["source"] = "Base Lattes";
$doc_curriculo_array["doc"]["type"] = "Curriculum";
if (isset($_REQUEST['tag'])) {
    $doc_curriculo_array["doc"]["tag"] = $_REQUEST['tag'];
}
if (isset($_REQUEST['unidade'])) {
    $doc_curriculo_array["doc"]["unidade"] = explode("|", $_REQUEST['unidade']);
}
if (isset($_REQUEST['departamento'])) {
    $doc_curriculo_array["doc"]["departamento"] = explode("|", $_REQUEST['departamento']);
}
if (isset($_REQUEST['numfuncional'])) {
    $doc_curriculo_array["doc"]["numfuncional"] = $_REQUEST['numfuncional'];
}
if (isset($_REQUEST['tipvin'])) {
    $doc_curriculo_array["doc"]["tipvin"] = explode("|", $_REQUEST['tipvin']);
}
if (isset($_REQUEST['divisao'])) {
    $doc_curriculo_array["doc"]["divisao"] = explode("|", $_REQUEST['divisao']);
}
if (isset($_REQUEST['secao'])) {
    $doc_curriculo_array['doc']['secao'] = explode("|", $_REQUEST['secao']);
}
if (isset($_REQUEST['ppg_capes'])) {
    $doc_curriculo_array['doc']['ppg_capes'] = explode("|", $_REQUEST['ppg_capes']);
}
if (isset($_REQUEST['genero'])) {
    $doc_curriculo_array['doc']['genero'] = $_REQUEST['genero'];
}
if (isset($_REQUEST['etnia'])) {
    $doc_curriculo_array['doc']['etnia'] = $_REQUEST['etnia'];
}
if (isset($_REQUEST['ano_ingresso'])) {
    $doc_curriculo_array['doc']['ano_ingresso'] = $_REQUEST['ano_ingresso'];
}
if (isset($_REQUEST['desc_nivel'])) {
    $doc_curriculo_array['doc']['desc_nivel'] = explode("|", $_REQUEST['desc_nivel']);
}
if (isset($_REQUEST['desc_curso'])) {
    $doc_curriculo_array['doc']['desc_curso'] = explode("|", $_REQUEST['desc_curso']);
}
if (isset($_REQUEST['campus'])) {
    $doc_curriculo_array['doc']['campus'] = explode("|", $_REQUEST['campus']);
}
if (isset($_REQUEST['desc_gestora'])) {
    $doc_curriculo_array['doc']['desc_gestora'] = explode("|", $_REQUEST['desc_gestora']);
}
if (isset($_REQUEST['email'])) {
    $doc_curriculo_array['doc']['email'] = $_REQUEST['email'];
}
if (isset($_REQUEST['google_citation'])) {
    $doc_curriculo_array['doc']['google_citation'] = $_REQUEST['google_citation'];
}
if (isset($_REQUEST['researcherid'])) {
    $doc_curriculo_array['doc']['researcherid'] = $_REQUEST['researcherid'];
}
if (isset($_REQUEST['lattes10'])) {
    $doc_curriculo_array['doc']['lattes10'] = $_REQUEST['lattes10'];
}

$doc_curriculo_array["doc"]["data_atualizacao"] = substr((string) $curriculo->attributes()->{'DATA-ATUALIZACAO'}, 4, 4) . "-" . substr((string) $curriculo->attributes()->{'DATA-ATUALIZACAO'}, 2, 2);
$doc_curriculo_array["doc"]["nome_completo"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-COMPLETO'};
$doc_curriculo_array["doc"]["nome_em_citacoes_bibliograficas"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-EM-CITACOES-BIBLIOGRAFICAS'};
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'NACIONALIDADE'})) {
    $doc_curriculo_array["doc"]["nacionalidade"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'NACIONALIDADE'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NASCIMENTO'})) {
    $doc_curriculo_array["doc"]["pais_de_nascimento"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NASCIMENTO'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'SIGLA-PAIS-NACIONALIDADE'})) {
    $doc_curriculo_array["doc"]["sigla_pais_nacionalidade"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'SIGLA-PAIS-NACIONALIDADE'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NACIONALIDADE'})) {
    $doc_curriculo_array["doc"]["pais_de_nacionalidade"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NACIONALIDADE'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'UF-NASCIMENTO'})) {
    $doc_curriculo_array["doc"]["uf_nascimento"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'UF-NASCIMENTO'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'CIDADE-NASCIMENTO'})) {
    $doc_curriculo_array["doc"]["cidade_nascimento"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'CIDADE-NASCIMENTO'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'DATA-FALECIMENTO'})) {
    $doc_curriculo_array["doc"]["data_falecimento"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'DATA-FALECIMENTO'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'ORCID-ID'})) {
    $doc_curriculo_array["doc"]["orcid_id"] = (string) $curriculo->{'DADOS-GERAIS'}->attributes()->{'ORCID-ID'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'})) {
    $doc_curriculo_array["doc"]["resumo_cv"]["texto_resumo_cv_rh"] = str_replace('"', '\"', (string) $curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'}->attributes()->{'TEXTO-RESUMO-CV-RH'});
    if (isset($cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRhEn"])) {
        $doc_curriculo_array["doc"]["resumo_cv"]["texto_resumo_cv_rh_en"] = str_replace('"', '\"', (string) $curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'}->attributes()->{'TEXTO-RESUMO-CV-RH-EN'});
    }
}

// // if (isset($cursor["docs"][0]["linksPesquisador"])){
// //     foreach ($cursor["docs"][0]["linksPesquisador"] as $links_pesquisador) {
// //         //print_r($links_pesquisador);
// //         if ($links_pesquisador["origemLink"] == "orcid") {
// //             $doc_curriculo_array["doc"]["orcid"] = $links_pesquisador["link"]["path"];
// //         }
// //     }
// // }

// Endereço profissional atual
if (isset($curriculo->{'DADOS-GERAIS'}->{'ENDERECO'})) {
    $doc_curriculo_array["doc"]["endereco"]["flagDePreferencia"] = (string) $curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->attributes()->{'FLAG-DE-PREFERENCIA'};
    if (isset($curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'})) {
        $enderecoProfissionalArray = get_object_vars($curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'});
        foreach (["CODIGO-INSTITUICAO-EMPRESA", "NOME-INSTITUICAO-EMPRESA", "CODIGO-ORGAO", "NOME-ORGAO", "CODIGO-UNIDADE", "NOME-UNIDADE", "LOGRADOURO-COMPLEMENTO", "PAIS", "UF", "CEP", "CIDADE", "BAIRRO", "HOME-PAGE"] as $endprof_campos) {
            if (!empty($enderecoProfissionalArray["@attributes"][$endprof_campos])) {
                $endprof_campos_corrigido = pregReplaceVariableName(strtolower($endprof_campos));
                $doc_curriculo_array["doc"]["endereco"]["endereco_profissional"][$endprof_campos_corrigido] = $enderecoProfissionalArray["@attributes"][$endprof_campos];
            }
        }
    }
}

// // // Quadro de citações
// // if (isset($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"])) {
// //     $i = 0;
// //     foreach ($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"] as $citacoes) {
// //         foreach (["nomeBase","codigoBase","sequencialIndicador","numeroCitacoes","dataCitacao","textoArgumento","indiceH","numeroTrabalhos","uriPesquisadorBase","uriLogoBase"] as $citacoes_campos) {
// //             if (isset ($citacoes[$citacoes_campos])) {
// //                 $doc_curriculo_array["doc"]["citacoes"][$citacoes["nomeBase"]][$citacoes_campos] = $citacoes[$citacoes_campos];
// //             }
// //         }
// //         foreach (["uriPesquisadorBase"] as $identificador_pesquisador) {
// //             if (!empty($citacoes[$identificador_pesquisador])) {
// //                 $doc_curriculo_array["doc"]["uri_pesquisador"][] = $citacoes[$identificador_pesquisador];
// //             }
// //         }
// //         $i++;
// //     }
// // }

// Formação Acadêmica Titulação
if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'})) {
    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'GRADUACAO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'GRADUACAO'} as $graduacao) {
            $graduacao = get_object_vars($graduacao);
            $formacao_array["sequenciaFormacao"] = $graduacao['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $graduacao['@attributes']["NIVEL"];
            $formacao_array["tituloDoTrabalhoDeConclusaoDeCurso"] = $graduacao['@attributes']["TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO"];
            $formacao_array["nomeDoOrientador"] = $graduacao['@attributes']["NOME-DO-ORIENTADOR"];
            $formacao_array["codigoInstituicao"] = $graduacao['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $graduacao['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"] = $graduacao['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"] = $graduacao['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"] = $graduacao['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"] = $graduacao['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"] = $graduacao['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"] = $graduacao['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"] = $graduacao['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"] = $graduacao['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"] = $graduacao['@attributes']["NOME-AGENCIA"];
            if (isset($graduacao['@attributes']["FORMACAO-ACADEMICA-TITULACAO"])) {
                $formacao_array["formacaoAcademicaTitulacao"] = $graduacao['@attributes']["FORMACAO-ACADEMICA-TITULACAO"];
            }

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'ESPECIALIZACAO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'ESPECIALIZACAO'} as $especializacao) {
            $especializacao = get_object_vars($especializacao);
            $formacao_array["sequenciaFormacao"] = $especializacao['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $especializacao['@attributes']["NIVEL"];
            $formacao_array["tituloDaMonografia"] = $especializacao['@attributes']["TITULO-DA-MONOGRAFIA"];
            $formacao_array["nomeDoOrientador"] = $especializacao['@attributes']["NOME-DO-ORIENTADOR"];
            $formacao_array["codigoInstituicao"] = $especializacao['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $especializacao['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"] = $especializacao['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"] = $especializacao['@attributes']["NOME-CURSO"];
            $formacao_array["statusDoCurso"] = $especializacao['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"] = $especializacao['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"] = $especializacao['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"] = $especializacao['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"] = $especializacao['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"] = $especializacao['@attributes']["NOME-AGENCIA"];
            $formacao_array["cargaHoraria"] = $especializacao['@attributes']["CARGA-HORARIA"];

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_especializacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO'} as $mestrado) {
            $mestrado = get_object_vars($mestrado);
            $formacao_array["sequenciaFormacao"] = $mestrado['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $mestrado['@attributes']["NIVEL"];
            $formacao_array["tituloDaDissertacaoTese"] = $mestrado['@attributes']["TITULO-DA-DISSERTACAO-TESE"];
            $formacao_array["nomeDoOrientador"] = $mestrado['@attributes']["NOME-COMPLETO-DO-ORIENTADOR"];
            $formacao_array["nomeDoCoOrientador"] = $mestrado['@attributes']["NOME-DO-CO-ORIENTADOR"];
            $formacao_array["codigoInstituicao"] = $mestrado['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $mestrado['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"] = $mestrado['@attributes']["CODIGO-CURSO"];
            $formacao_array["codigoCursoCapes"] = $mestrado['@attributes']["CODIGO-CURSO-CAPES"];
            $formacao_array["nomeCurso"] = $mestrado['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"] = $mestrado['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"] = $mestrado['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"] = $mestrado['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"] = $mestrado['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"] = $mestrado['@attributes']["FLAG-BOLSA"];
            $formacao_array["tipoMestrado"] = $mestrado['@attributes']["TIPO-MESTRADO"];
            $formacao_array["codigoAgenciaFinanciadora"] = $mestrado['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"] = $mestrado['@attributes']["NOME-AGENCIA"];
            $formacao_array["anoDeObtencaoDoTitulo"] = $mestrado['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];

            if (isset($mestrado["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($mestrado["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }
            }

            if (isset($mestrado["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($mestrado["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($mestrado["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }
            }

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_mestrado"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO-PROFISSIONALIZANTE'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO-PROFISSIONALIZANTE'} as $mestradoProf) {
            $mestradoProf = get_object_vars($mestradoProf);
            $formacao_array["sequenciaFormacao"] = $mestradoProf['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $mestradoProf['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"] = $mestradoProf['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $mestradoProf['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"] = $mestradoProf['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"] = $mestradoProf['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"] = $mestradoProf['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"] = $mestradoProf['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"] = $mestradoProf['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"] = $mestradoProf['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"] = $mestradoProf['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"] = $mestradoProf['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"] = $mestradoProf['@attributes']["NOME-AGENCIA"];
            $formacao_array["anoDeObtencaoDoTitulo"] = $mestradoProf['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["tituloDaDissertacaoTese"] = $mestradoProf['@attributes']["TITULO-DA-DISSERTACAO-TESE"];
            $formacao_array["nomeDoOrientador"] = $mestradoProf['@attributes']["NOME-COMPLETO-DO-ORIENTADOR"];
            $formacao_array["nomeDoCoOrientador"] = $mestradoProf['@attributes']["NOME-DO-CO-ORIENTADOR"];

            if (isset($mestradoProf["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($mestradoProf["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }
            }

            if (isset($mestradoProf["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($mestradoProf["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($mestradoProf["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }
            }

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_mestradoProfissionalizante"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'DOUTORADO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'DOUTORADO'} as $doutorado) {
            $doutorado = get_object_vars($doutorado);
            $formacao_array["sequenciaFormacao"] = $doutorado['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $doutorado['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"] = $doutorado['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $doutorado['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"] = $doutorado['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"] = $doutorado['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"] = $doutorado['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"] = $doutorado['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"] = $doutorado['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"] = $doutorado['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"] = $doutorado['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"] = $doutorado['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"] = $doutorado['@attributes']["NOME-AGENCIA"];
            $formacao_array["anoDeObtencaoDoTitulo"] = $doutorado['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["tituloDaDissertacaoTese"] = $doutorado['@attributes']["TITULO-DA-DISSERTACAO-TESE"];
            $formacao_array["nomeDoOrientador"] = $doutorado['@attributes']["NOME-COMPLETO-DO-ORIENTADOR"];
            $formacao_array["tipoDoutorado"] = $doutorado['@attributes']["TIPO-DOUTORADO"];
            $formacao_array["numeroIDOrientador"] = $doutorado['@attributes']["NUMERO-ID-ORIENTADOR"];
            $formacao_array["codigoCursoCapes"] = $doutorado['@attributes']["CODIGO-CURSO-CAPES"];
            $formacao_array["nomeDoCoOrientador"] = $doutorado['@attributes']["NOME-DO-CO-ORIENTADOR"];
            $formacao_array["codigoInstituicaoCoTutela"] = $doutorado['@attributes']["CODIGO-INSTITUICAO-CO-TUTELA"];
            $formacao_array["codigoInstituicaoSanduiche"] = $doutorado['@attributes']["CODIGO-INSTITUICAO-SANDUICHE"];

            if (isset($doutorado["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($doutorado["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }
            }

            if (isset($doutorado["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($doutorado["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($doutorado["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }
            }

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_doutorado"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'POS-DOUTORADO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'POS-DOUTORADO'} as $posDoutorado) {
            $posDoutorado = get_object_vars($posDoutorado);
            $formacao_array["sequenciaFormacao"] = $posDoutorado['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $posDoutorado['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"] = $posDoutorado['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $posDoutorado['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["anoDeInicio"] = $posDoutorado['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"] = $posDoutorado['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["anoDeObtencaoDoTitulo"] = $posDoutorado['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["flagBolsa"] = $posDoutorado['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"] = $posDoutorado['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"] = $posDoutorado['@attributes']["NOME-AGENCIA"];
            $formacao_array["statusDoCurso"] = $posDoutorado['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["numeroIDOrientador"] = $posDoutorado['@attributes']["NUMERO-ID-ORIENTADOR"];
            $formacao_array["tituloDoTrabalho"] = $posDoutorado['@attributes']["TITULO-DO-TRABALHO"];

            if (isset($posDoutorado["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($posDoutorado["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }
            }

            if (isset($posDoutorado["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($posDoutorado["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($posDoutorado["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }
            }

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_pos_doutorado"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'LIVRE-DOCENCIA'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'LIVRE-DOCENCIA'} as $livreDocencia) {
            $livreDocencia = get_object_vars($livreDocencia);
            $formacao_array["sequenciaFormacao"] = $livreDocencia['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"] = $livreDocencia['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"] = $livreDocencia['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"] = $livreDocencia['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["anoDeObtencaoDoTitulo"] = $livreDocencia['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["tituloDoTrabalho"] = $livreDocencia['@attributes']["TITULO-DO-TRABALHO"];

            if (isset($livreDocencia["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($livreDocencia["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }
            }

            if (isset($livreDocencia["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($livreDocencia["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($livreDocencia["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }
            }

            $doc_curriculo_array["doc"]["formacao_academica_titulacao_livreDocencia"][] = $formacao_array;
            unset($formacao_array);
        }
    }
}



// Formação máxima
if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'LIVRE-DOCENCIA'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Livre Docência";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'POS-DOUTORADO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Pós Doutorado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'DOUTORADO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Doutorado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO-PROFISSIONALIZANTE'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Mestrado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Mestrado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'ESPECIALIZACAO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Especialização";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'GRADUACAO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Graduação";
} else {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Sem formação informada";
}

// Projetos de pesquisa
if (isset($curriculo->{'DADOS-GERAIS'}->{'ATUACOES-PROFISSIONAIS'})) {
    $doc_curriculo_array["doc"]["atuacoes_profissionais"] = $curriculo->{'DADOS-GERAIS'}->{'ATUACOES-PROFISSIONAIS'};
    $atuacoes_profissionais = json_decode(json_encode($curriculo->{'DADOS-GERAIS'}->{'ATUACOES-PROFISSIONAIS'}), true);
    foreach ($atuacoes_profissionais as $atuacao_profissional) {
        foreach ($atuacao_profissional as $key => $value) {
            if (isset($value['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO'])) {
                if (isset($value['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'])) {
                    foreach ($value['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'] as $participacao_projeto) {
                        if (isset($participacao_projeto['PROJETO-DE-PESQUISA'])) {
                            $doc_projetos['doc']['NOME-INSTITUICAO'] = $value['@attributes']['NOME-INSTITUICAO'];
                            $doc_projetos['doc']['DADOS-DO-PROJETO'] = $participacao_projeto['PROJETO-DE-PESQUISA'];
                            $doc_projetos["doc_as_upsert"] = true;
                            if (isset($doc_projetos['doc']['DADOS-DO-PROJETO']['@attributes']['NOME-DO-PROJETO'])) {
                                $sha_projeto_array[] = $doc_projetos['doc']['DADOS-DO-PROJETO']['@attributes']['NOME-DO-PROJETO'];
                            }
                            if (isset($doc_projetos['doc']['DADOS-DO-PROJETO']['@attributes']['ANO-FIM'])) {
                                $sha_projeto_array[] = $doc_projetos['doc']['DADOS-DO-PROJETO']['@attributes']['ANO-FIM'];
                            }
                            if (isset($doc_projetos['doc']['DADOS-DO-PROJETO']['@attributes']['DESCRICAO-DO-PROJETO'])) {
                                $sha_projeto_array[] = $doc_projetos['doc']['DADOS-DO-PROJETO']['@attributes']['DESCRICAO-DO-PROJETO'];
                            }
                            if (isset($sha_projeto_array)) {
                                $sha256_projeto = hash('sha256', '' . implode("", $sha_projeto_array) . '');
                                $resultado_projeto = Elasticsearch::update($sha256_projeto, $doc_projetos, $index_projetos);
                            }
                        }
                    }
                }
            }
        }
    }
}


// Idiomas

if (isset($curriculo->{'DADOS-GERAIS'}->{'IDIOMAS'})) {
    foreach ($curriculo->{'DADOS-GERAIS'}->{'IDIOMAS'}->{'IDIOMA'} as $idioma) {
        $idioma = get_object_vars($idioma);
        $idioma_array["idioma"] = $idioma['@attributes']["IDIOMA"];
        $idioma_array["descricaoDoIdioma"] = $idioma['@attributes']["DESCRICAO-DO-IDIOMA"];
        $idioma_array["proficienciaDeLeitura"] = $idioma['@attributes']["PROFICIENCIA-DE-LEITURA"];
        $idioma_array["proficienciaDeFala"] = $idioma['@attributes']["PROFICIENCIA-DE-FALA"];
        $idioma_array["proficienciaDeFala"] = $idioma['@attributes']["PROFICIENCIA-DE-FALA"];
        $idioma_array["proficienciaDeEscrita"] = $idioma['@attributes']["PROFICIENCIA-DE-ESCRITA"];
        $idioma_array["proficienciaDeCompreensao"] = $idioma['@attributes']["PROFICIENCIA-DE-COMPREENSAO"];
        $doc_curriculo_array["doc"]["idiomas"][] = $idioma_array;
        unset($idioma_array);
    }
}

// Premios - Títulos

if (isset($curriculo->{'DADOS-GERAIS'}->{'PREMIOS-TITULOS'})) {
    $i_premio = 0;
    foreach ($curriculo->{'DADOS-GERAIS'}->{'PREMIOS-TITULOS'}->{'PREMIO-TITULO'} as $premioTitulo) {
        $premioTitulo = get_object_vars($premioTitulo);
        $doc_curriculo_array["doc"]["premios_titulos"][$i_premio]["nomeDoPremioOuTitulo"] = $premioTitulo['@attributes']["NOME-DO-PREMIO-OU-TITULO"];
        $doc_curriculo_array["doc"]["premios_titulos"][$i_premio]["nomeDaEntidadePromotora"] = $premioTitulo['@attributes']["NOME-DA-ENTIDADE-PROMOTORA"];
        $doc_curriculo_array["doc"]["premios_titulos"][$i_premio]["anoDaPremiacao"] = $premioTitulo['@attributes']["ANO-DA-PREMIACAO"];
        $doc_curriculo_array["doc"]["premios_titulos"][$i_premio]["nomeDoPremioOuTituloIngles"] = $premioTitulo['@attributes']["NOME-DO-PREMIO-OU-TITULO-INGLES"];
        $i_premio++;
    }
}

// Licença Maternidade

if (isset($curriculo->{'DADOS-GERAIS'}->{'LICENCAS'})) {
    $i_licenca = 0;
    foreach ($curriculo->{'DADOS-GERAIS'}->{'LICENCAS'}->{'LICENCA'} as $licenca) {
        $licenca = get_object_vars($licenca);
        $licenca_array[$i_licenca]["tipoLicenca"] = $licenca['@attributes']["TIPO-LICENCA"];
        $licenca_array[$i_licenca]["dataInicioLicenca"] = $licenca['@attributes']["DATA-INICIO-LICENCA"];
        $licenca_array[$i_licenca]["dataFimLicenca"] = $licenca['@attributes']["DATA-FIM-LICENCA"];
        $doc_curriculo_array["doc"]["licencas"][] = $licenca_array;
        $i_licenca++;
        unset($licenca_array);
    }
}

// Orientações em andamento

if (isset($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'})) {
    // Mestrado
    $i_orientacao = 0;

    if (isset($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'}->{'ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO'})) {
        foreach ($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'}->{'ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO'} as $orientacao) {
            $orientacao = get_object_vars($orientacao);
            $dadosBasicosDaOrientacao = get_object_vars($orientacao["DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO"]);
            $detalhamentoDaOrientacao = get_object_vars($orientacao["DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO"]);
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["natureza"] = $dadosBasicosDaOrientacao['@attributes']["NATUREZA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["titulo"] = $dadosBasicosDaOrientacao['@attributes']["TITULO-DO-TRABALHO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["ano"] = $dadosBasicosDaOrientacao['@attributes']["ANO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["tipoDeOrientacao"] = $detalhamentoDaOrientacao['@attributes']["TIPO-DE-ORIENTACAO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDoOrientando"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-ORIENTANDO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDaInstituicao"] = $detalhamentoDaOrientacao['@attributes']["NOME-INSTITUICAO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDoCurso"] = $detalhamentoDaOrientacao['@attributes']["NOME-CURSO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["flagBolsa"] = $detalhamentoDaOrientacao['@attributes']["FLAG-BOLSA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDaAgencia"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-AGENCIA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["numeroIDOrientado"] = $detalhamentoDaOrientacao['@attributes']["NUMERO-ID-ORIENTADO"];
            //$doc_curriculo_array["doc"]["orientacoes"][] = $orientacao_array;
            $i_orientacao++;
        }
    }
    // Doutorado
    if (isset($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'}->{'ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO'})) {
        foreach ($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'}->{'ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO'} as $orientacao) {
            $orientacao = get_object_vars($orientacao);
            $dadosBasicosDaOrientacao = get_object_vars($orientacao["DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO"]);
            $detalhamentoDaOrientacao = get_object_vars($orientacao["DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO"]);
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["natureza"] = $dadosBasicosDaOrientacao['@attributes']["NATUREZA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["titulo"] = $dadosBasicosDaOrientacao['@attributes']["TITULO-DO-TRABALHO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["ano"] = $dadosBasicosDaOrientacao['@attributes']["ANO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["tipoDeOrientacao"] = $detalhamentoDaOrientacao['@attributes']["TIPO-DE-ORIENTACAO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDoOrientando"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-ORIENTANDO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDaInstituicao"] = $detalhamentoDaOrientacao['@attributes']["NOME-INSTITUICAO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDoCurso"] = $detalhamentoDaOrientacao['@attributes']["NOME-CURSO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["flagBolsa"] = $detalhamentoDaOrientacao['@attributes']["FLAG-BOLSA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDaAgencia"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-AGENCIA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["numeroIDOrientado"] = $detalhamentoDaOrientacao['@attributes']["NUMERO-ID-ORIENTANDO"];
            //$doc_curriculo_array["doc"]["orientacoes"][] = $orientacao_array;
            $i_orientacao++;
        }
    }

    // Pós-doutorado
    if (isset($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'}->{'ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO'})) {
        foreach ($curriculo->{'DADOS-COMPLEMENTARES'}->{'ORIENTACOES-EM-ANDAMENTO'}->{'ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO'} as $orientacao) {
            $orientacao = get_object_vars($orientacao);
            $dadosBasicosDaOrientacao = get_object_vars($orientacao["DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO"]);
            $detalhamentoDaOrientacao = get_object_vars($orientacao["DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO"]);
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["natureza"] = $dadosBasicosDaOrientacao['@attributes']["NATUREZA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["titulo"] = $dadosBasicosDaOrientacao['@attributes']["TITULO-DO-TRABALHO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["ano"] = $dadosBasicosDaOrientacao['@attributes']["ANO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["tipoDeOrientacao"] = $detalhamentoDaOrientacao['@attributes']["TIPO-DE-ORIENTACAO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDoOrientando"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-ORIENTANDO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDaInstituicao"] = $detalhamentoDaOrientacao['@attributes']["NOME-INSTITUICAO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDoCurso"] = $detalhamentoDaOrientacao['@attributes']["NOME-CURSO"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["flagBolsa"] = $detalhamentoDaOrientacao['@attributes']["FLAG-BOLSA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["nomeDaAgencia"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-AGENCIA"];
            $doc_curriculo_array["doc"]["orientacoes"][$i_orientacao]["numeroIDOrientado"] = $detalhamentoDaOrientacao['@attributes']["NUMERO-ID-ORIENTANDO"];
            //$doc_curriculo_array["doc"]["orientacoes"][] = $orientacao_array;
            $i_orientacao++;
        }
    }
}

// Orientações concluídas

if (isset($curriculo->{'OUTRA-PRODUCAO'}->{'ORIENTACOES-CONCLUIDAS'})) {
    //var_dump($curriculo->{'OUTRA-PRODUCAO'}->{'ORIENTACOES-CONCLUIDAS'});
    $i_orientacao_concluida = 0;
    if (isset($curriculo->{'OUTRA-PRODUCAO'}->{'ORIENTACOES-CONCLUIDAS'}->{'ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'})) {
        foreach ($curriculo->{'OUTRA-PRODUCAO'}->{'ORIENTACOES-CONCLUIDAS'}->{'ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'} as $orientacao_concluida) {
            $orientacao_concluida = get_object_vars($orientacao_concluida);
            $dadosBasicosDaOrientacao = get_object_vars($orientacao_concluida["DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO"]);
            $detalhamentoDaOrientacao = get_object_vars($orientacao_concluida["DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO"]);
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["natureza"] = $dadosBasicosDaOrientacao['@attributes']["NATUREZA"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["titulo"] = $dadosBasicosDaOrientacao['@attributes']["TITULO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["ano"] = $dadosBasicosDaOrientacao['@attributes']["ANO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["tipoDeOrientacao"] = $detalhamentoDaOrientacao['@attributes']["TIPO-DE-ORIENTACAO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDoOrientando"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-ORIENTADO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDaInstituicao"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-INSTITUICAO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDoCurso"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-CURSO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["flagBolsa"] = $detalhamentoDaOrientacao['@attributes']["FLAG-BOLSA"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDaAgencia"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-AGENCIA"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["numeroIDOrientado"] = $detalhamentoDaOrientacao['@attributes']["NUMERO-ID-ORIENTADO"];
            $i_orientacao_concluida++;
        }
    }
    if (isset($curriculo->{'OUTRA-PRODUCAO'}->{'ORIENTACOES-CONCLUIDAS'}->{'ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO'})) {
        foreach ($curriculo->{'OUTRA-PRODUCAO'}->{'ORIENTACOES-CONCLUIDAS'}->{'ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO'} as $orientacao_concluida) {
            $orientacao_concluida = get_object_vars($orientacao_concluida);
            $dadosBasicosDaOrientacao = get_object_vars($orientacao_concluida["DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO"]);
            $detalhamentoDaOrientacao = get_object_vars($orientacao_concluida["DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO"]);
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["natureza"] = $dadosBasicosDaOrientacao['@attributes']["NATUREZA"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["titulo"] = $dadosBasicosDaOrientacao['@attributes']["TITULO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["ano"] = $dadosBasicosDaOrientacao['@attributes']["ANO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["tipoDeOrientacao"] = $detalhamentoDaOrientacao['@attributes']["TIPO-DE-ORIENTACAO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDoOrientando"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-ORIENTADO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDaInstituicao"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-INSTITUICAO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDoCurso"] = $detalhamentoDaOrientacao['@attributes']["NOME-DO-CURSO"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["flagBolsa"] = $detalhamentoDaOrientacao['@attributes']["FLAG-BOLSA"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["nomeDaAgencia"] = $detalhamentoDaOrientacao['@attributes']["NOME-DA-AGENCIA"];
            $doc_curriculo_array["doc"]["orientacoesconcluidas"][$i_orientacao_concluida]["numeroIDOrientado"] = $detalhamentoDaOrientacao['@attributes']["NUMERO-ID-ORIENTADO"];
            $i_orientacao_concluida++;
        }
    }
}


$doc_curriculo_array["doc"]["lattesID"] = $identificador;
$doc_curriculo_array["doc"]["dataDeColeta"] = date('Y-m-d');
$doc_curriculo_array["doc_as_upsert"] = true;

$resultado_curriculo = Elasticsearch::update($identificador, $doc_curriculo_array, $index_cv);

//Parser de Trabalhos-em-Eventos

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'})) {

    $trabalhosEmEventosArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'}->{'TRABALHO-EM-EVENTOS'};
    foreach ($trabalhosEmEventosArray as $obra) {
        $obra = get_object_vars($obra);
        $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-TRABALHO"]);
        $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-TRABALHO"]);
        $doc["doc"]["type"] = "Work";
        $doc["doc"]["tipo"] = "Trabalhos em eventos";
        $doc["doc"]["source"] = "Base Lattes";
        $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
        if (isset($_REQUEST['tag'])) {
            $doc["doc"]["tag"][] = $_REQUEST['tag'];
        } else {
            $doc["doc"]["tag"][] = "Lattes";
        }
        $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-TRABALHO"];
        $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-TRABALHO"];
        $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
        $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DO-EVENTO"];
        $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
        $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
        $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
        $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
        $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
        if ($testDOI === 1) {
            $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
        }
        $doc["doc"]["lattes"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];

        $doc["doc"]["detalhamentoDoTrabalho"]["classificacaoDoEvento"] = $detalhamentoDoTrabalho['@attributes']["CLASSIFICACAO-DO-EVENTO"];
        $doc["doc"]["EducationEvent"]["name"] = $detalhamentoDoTrabalho['@attributes']["NOME-DO-EVENTO"];
        $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DO-EVENTO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["anoDeRealizacao"] = $detalhamentoDoTrabalho['@attributes']["ANO-DE-REALIZACAO"];
        $doc["doc"]["isPartOf"]["name"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DOS-ANAIS-OU-PROCEEDINGS"];
        $doc["doc"]["pageStart"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-INICIAL"];
        $doc["doc"]["pageEnd"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-FINAL"];
        $doc["doc"]["isPartOf"]["isbn"] = $detalhamentoDoTrabalho['@attributes']["ISBN"];
        $doc["doc"]["publisher"]["organization"]["name"] = $detalhamentoDoTrabalho['@attributes']["NOME-DA-EDITORA"];
        $doc["doc"]["detalhamentoDoTrabalho"]["cidadeDaEditora"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DA-EDITORA"];
        $doc["doc"]["detalhamentoDoTrabalho"]["volumeDosAnais"] = $detalhamentoDoTrabalho['@attributes']["VOLUME"];
        $doc["doc"]["detalhamentoDoTrabalho"]["fasciculoDosAnais"] = $detalhamentoDoTrabalho['@attributes']["FASCICULO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["serieDosAnais"] = $detalhamentoDoTrabalho['@attributes']["SERIE"];

        if (!empty($obra["AUTORES"])) {
            $array_result = processaAutoresLattes($obra["AUTORES"]);
            $doc = array_merge_recursive($doc, $array_result);
        }

        if (isset($obra["PALAVRAS-CHAVE"])) {
            $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
            if (isset($array_result_pc)) {
                $doc = array_merge_recursive($doc, $array_result_pc);
            }
            unset($array_result_pc);
        }

        if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
            $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
            if (isset($array_result_ac)) {
                $doc = array_merge_recursive($doc, $array_result_ac);
            }
            unset($array_result_ac);
        }

        // Vinculo
        $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

        // Constroi sha256
        if (!empty($doc['doc']['doi'])) {
            $sha256 = hash('sha256', '' . $doc['doc']['doi'] . '');
        } else {
            $sha_array[] = $doc["doc"]["lattes_ids"][0];
            $sha_array[] = $doc["doc"]["tipo"];
            $sha_array[] = $doc["doc"]["name"];
            $sha_array[] = $doc["doc"]["datePublished"];
            $sha_array[] = $doc["doc"]["country"];
            $sha_array[] = $doc["doc"]["EducationEvent"]["name"];
            $sha_array[] = $doc["doc"]["pageStart"];
            $sha_array[] = $doc["doc"]["pageEnd"];
            $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
        }


        //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
        $doc["doc"]["concluido"] = "Não";
        $doc["doc_as_upsert"] = true;

        // Comparador
        $resultado = upsert($doc, $sha256);

        unset($dadosBasicosDoTrabalho);
        unset($detalhamentoDoTrabalho);
        unset($obra);
        unset($doc);
        unset($sha_array);
        unset($sha256);
        flush();
    }
}

//Parser de Artigos-Publicados

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'})) {

    $artigoPublicadoArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'}->{'ARTIGO-PUBLICADO'};
    foreach ($artigoPublicadoArray as $obra) {
        $obra = get_object_vars($obra);
        $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-ARTIGO"]);
        $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-ARTIGO"]);

        $doc["doc"]["type"] = "Work";
        $doc["doc"]["tipo"] = "Artigo publicado";
        $doc["doc"]["source"] = "Base Lattes";
        $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
        if (isset($_REQUEST['tag'])) {
            $doc["doc"]["tag"][] = $_REQUEST['tag'];
        } else {
            $doc["doc"]["tag"][] = "Lattes";
        }
        $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-ARTIGO"];
        $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-ARTIGO"];
        $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
        $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
        $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
        $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
        $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
        $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
        $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
        if ($testDOI === 1) {
            $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
        }
        $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-ARTIGO-INGLES"];
        $doc["doc"]["lattes"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];

        $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["LOCAL-DE-PUBLICACAO"];
        $doc["doc"]["isPartOf"]["name"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DO-PERIODICO-OU-REVISTA"];
        $doc["doc"]["pageStart"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-INICIAL"];
        $doc["doc"]["pageEnd"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-FINAL"];
        $doc["doc"]["isPartOf"]["issn"] = $detalhamentoDoTrabalho['@attributes']["ISSN"];
        $doc["doc"]["isPartOf"]["volume"] = $detalhamentoDoTrabalho['@attributes']["VOLUME"];
        $doc["doc"]["isPartOf"]["fasciculo"] = $detalhamentoDoTrabalho['@attributes']["FASCICULO"];
        $doc["doc"]["isPartOf"]["serie"] = $detalhamentoDoTrabalho['@attributes']["SERIE"];

        if (!empty($obra["AUTORES"])) {
            $array_result = processaAutoresLattes($obra["AUTORES"]);
            $doc = array_merge_recursive($doc, $array_result);
        }

        if (isset($obra["PALAVRAS-CHAVE"])) {
            $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
            if (isset($array_result_pc)) {
                $doc = array_merge_recursive($doc, $array_result_pc);
            }
            unset($array_result_pc);
        }

        if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
            $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
            if (isset($array_result_ac)) {
                $doc = array_merge_recursive($doc, $array_result_ac);
            }
            unset($array_result_ac);
        }

        // Vinculo
        $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

        // Constroi sha256


        if (!empty($doc["doc"]["doi"])) {
            $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
        } else {
            $sha_array[] = $doc["doc"]["lattes_ids"][0];
            $sha_array[] = $doc["doc"]["tipo"];
            $sha_array[] = $doc["doc"]["name"];
            $sha_array[] = $doc["doc"]["datePublished"];
            $sha_array[] = $doc["doc"]["isPartOf"]["name"];
            $sha_array[] = $doc["doc"]["pageStart"];
            $sha_array[] = $doc["doc"]["url"];
            $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
        }


        //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
        $doc["doc"]["concluido"] = "Não";
        $doc["doc_as_upsert"] = true;

        // Comparador
        $resultado = upsert($doc, $sha256);
        unset($dadosBasicosDoTrabalho);
        unset($detalhamentoDoTrabalho);
        unset($obra);
        unset($doc);
        unset($sha_array);
        unset($sha256);
        flush();
    }
}

//Parser de Livros-Publicados

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'})) {

    if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'LIVROS-PUBLICADOS-OU-ORGANIZADOS'})) {

        $livrosPublicadoArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'LIVROS-PUBLICADOS-OU-ORGANIZADOS'}->{'LIVRO-PUBLICADO-OU-ORGANIZADO'};
        foreach ($livrosPublicadoArray as $obra) {
            $obra = get_object_vars($obra);
            $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-LIVRO"]);
            $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-LIVRO"]);

            $doc["doc"]["type"] = "Work";
            $doc["doc"]["tipo"] = "Livro publicado ou organizado";
            $doc["doc"]["source"] = "Base Lattes";
            $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
            if (isset($_REQUEST['tag'])) {
                $doc["doc"]["tag"][] = $_REQUEST['tag'];
            } else {
                $doc["doc"]["tag"][] = "Lattes";
            }
            $doc["doc"]["lattes"]["tipo"] = $dadosBasicosDoTrabalho['@attributes']["TIPO"];
            $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO"];
            $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-LIVRO"];
            $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
            $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
            $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
            $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
            $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
            $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
            $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
            if ($testDOI === 1) {
                $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
            }
            $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-LIVRO-INGLES"];
            $doc["doc"]["lattes"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];

            $doc["doc"]["detalhamentoDoLivro"]["numeroDeVolumes"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DE-VOLUMES"];
            $doc["doc"]["numberOfPages"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DE-PAGINAS"];
            $doc["doc"]["isbn"] = $detalhamentoDoTrabalho['@attributes']["ISBN"];
            $doc["doc"]["bookEdition"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DA-EDICAO-REVISAO"];
            $doc["doc"]["detalhamentoDoLivro"]["numeroDaSerie"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DA-SERIE"];
            $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DA-EDITORA"];
            $doc["doc"]["publisher"]["organization"]["name"] = $detalhamentoDoTrabalho['@attributes']["NOME-DA-EDITORA"];

            if (!empty($obra["AUTORES"])) {
                $array_result = processaAutoresLattes($obra["AUTORES"]);
                $doc = array_merge_recursive($doc, $array_result);
            }

            if (isset($obra["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $doc = array_merge_recursive($doc, $array_result_pc);
                }
                unset($array_result_pc);
            }

            if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                if (isset($array_result_ac)) {
                    $doc = array_merge_recursive($doc, $array_result_ac);
                }
                unset($array_result_ac);
            }

            // Vinculo
            $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

            // Constroi sha256
            if (!empty($doc["doc"]["doi"])) {
                $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
            } elseif (!empty($doc["doc"]["isbn"])) {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["isbn"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            } else {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["tipo"];
                $sha_array[] = $doc["doc"]["name"];
                $sha_array[] = $doc["doc"]["datePublished"];
                $sha_array[] = $doc["doc"]["bookEdition"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            }


            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
            $doc["doc"]["concluido"] = "Não";
            $doc["doc_as_upsert"] = true;

            // Comparador
            $resultado = upsert($doc, $sha256);

            unset($dadosBasicosDoTrabalho);
            unset($detalhamentoDoTrabalho);
            unset($obra);
            unset($doc);
            unset($sha_array);
            unset($sha256);
            flush();
        }
    }

    if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'CAPITULOS-DE-LIVROS-PUBLICADOS'})) {

        $capitulosPublicadoArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'LIVROS-E-CAPITULOS'}->{'CAPITULOS-DE-LIVROS-PUBLICADOS'}->{'CAPITULO-DE-LIVRO-PUBLICADO'};
        foreach ($capitulosPublicadoArray as $obra) {
            $obra = get_object_vars($obra);
            $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-CAPITULO"]);
            $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-CAPITULO"]);

            $doc["doc"]["type"] = "Work";
            $doc["doc"]["tipo"] = "Capítulo de livro publicado";
            $doc["doc"]["source"] = "Base Lattes";
            $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
            if (isset($_REQUEST['tag'])) {
                $doc["doc"]["tag"][] = $_REQUEST['tag'];
            } else {
                $doc["doc"]["tag"][] = "Lattes";
            }
            $doc["doc"]["lattes"]["tipo"] = $dadosBasicosDoTrabalho['@attributes']["TIPO"];
            $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-CAPITULO-DO-LIVRO"];
            $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO"];
            $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
            $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
            $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
            $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
            $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
            $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
            if ($testDOI === 1) {
                $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
            }
            $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-CAPITULO-DO-LIVRO-INGLES"];
            if (isset($dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"])) {
                $doc["doc"]["lattes"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];
            }
            $doc["doc"]["isPartOf"]["name"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DO-LIVRO"];
            $doc["doc"]["pageStart"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-INICIAL"];
            $doc["doc"]["pageEnd"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-FINAL"];
            $doc["doc"]["isPartOf"]["isbn"] = $detalhamentoDoTrabalho['@attributes']["ISBN"];
            $doc["doc"]["isPartOf"]["contributor"] = $detalhamentoDoTrabalho['@attributes']["ORGANIZADORES"];
            $doc["doc"]["bookEdition"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DA-EDICAO-REVISAO"];
            $doc["doc"]["serie"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DA-SERIE"];
            $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DA-EDITORA"];
            $doc["doc"]["publisher"]["organization"]["name"] = $detalhamentoDoTrabalho['@attributes']["NOME-DA-EDITORA"];

            if (!empty($obra["AUTORES"])) {
                $array_result = processaAutoresLattes($obra["AUTORES"]);
                $doc = array_merge_recursive($doc, $array_result);
            }

            if (isset($obra["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $doc = array_merge_recursive($doc, $array_result_pc);
                }
                unset($array_result_pc);
            }

            if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                if (isset($array_result_ac)) {
                    $doc = array_merge_recursive($doc, $array_result_ac);
                }
                unset($array_result_ac);
            }

            // Vinculo
            $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

            // Constroi sha256
            if (!empty($doc["doc"]["doi"])) {
                $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
            } else {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["tipo"];
                $sha_array[] = $doc["doc"]["name"];
                $sha_array[] = $doc["doc"]["datePublished"];
                $sha_array[] = $doc["doc"]["isPartOf"]["name"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            }

            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
            $doc["doc"]["concluido"] = "Não";
            $doc["doc_as_upsert"] = true;

            // Comparador
            $resultado = upsert($doc, $sha256);

            unset($dadosBasicosDoTrabalho);
            unset($detalhamentoDoTrabalho);
            unset($obra);
            unset($doc);
            unset($sha_array);
            unset($sha256);
            flush();
        }
    }
}

//Parser de Textos em Jornais e Revistas

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TEXTOS-EM-JORNAIS-OU-REVISTAS'})) {

    $textosEmJornaisPublicadoArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TEXTOS-EM-JORNAIS-OU-REVISTAS'}->{'TEXTO-EM-JORNAL-OU-REVISTA'};
    foreach ($textosEmJornaisPublicadoArray as $obra) {
        $obra = get_object_vars($obra);
        $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-TEXTO"]);
        $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-TEXTO"]);

        $doc["doc"]["type"] = "Work";
        $doc["doc"]["tipo"] = "Textos em jornais de notícias/revistas";
        $doc["doc"]["source"] = "Base Lattes";
        $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
        if (isset($_REQUEST['tag'])) {
            $doc["doc"]["tag"][] = $_REQUEST['tag'];
        } else {
            $doc["doc"]["tag"][] = "Lattes";
        }

        $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
        $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-TEXTO"];
        $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-TEXTO"];
        $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
        $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
        $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
        $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
        $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
        $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
        if ($testDOI === 1) {
            $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
        }
        $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-TEXTO-INGLES"];
        $doc["doc"]["lattes"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];

        $doc["doc"]["isPartOf"]["name"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DO-JORNAL-OU-REVISTA"];
        $doc["doc"]["isPartOf"]["issn"] = $detalhamentoDoTrabalho['@attributes']["ISSN"];
        $doc["doc"]["isPartOf"]["datePublished"] = $detalhamentoDoTrabalho['@attributes']["DATA-DE-PUBLICACAO"];
        $doc["doc"]["volume"] = $detalhamentoDoTrabalho['@attributes']["VOLUME"];
        $doc["doc"]["pageStart"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-INICIAL"];
        $doc["doc"]["pageEnd"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-FINAL"];
        $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["LOCAL-DE-PUBLICACAO"];

        if (!empty($obra["AUTORES"])) {
            $array_result = processaAutoresLattes($obra["AUTORES"]);
            $doc = array_merge_recursive($doc, $array_result);
        }

        if (isset($obra["PALAVRAS-CHAVE"])) {
            $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
            if (isset($array_result_pc)) {
                $doc = array_merge_recursive($doc, $array_result_pc);
            }
            unset($array_result_pc);
        }

        if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
            $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
            if (isset($array_result_ac)) {
                $doc = array_merge_recursive($doc, $array_result_ac);
            }
            unset($array_result_ac);
        }

        // Vinculo
        $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

        // Constroi sha256


        if (!empty($doc["doc"]["doi"])) {
            $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
        } else {
            $sha_array[] = $doc["doc"]["lattes_ids"][0];
            $sha_array[] = $doc["doc"]["tipo"];
            $sha_array[] = $doc["doc"]["name"];
            $sha_array[] = $doc["doc"]["datePublished"];
            $sha_array[] = $doc["doc"]["isPartOf"]["name"];
            $sha_array[] = $doc["doc"]["pageStart"];
            $sha_array[] = $doc["doc"]["url"];
            $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
        }


        //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
        $doc["doc"]["concluido"] = "Não";
        $doc["doc_as_upsert"] = true;

        // Comparador
        $resultado = upsert($doc, $sha256);

        unset($dadosBasicosDoTrabalho);
        unset($detalhamentoDoTrabalho);
        unset($obra);
        unset($doc);
        unset($sha_array);
        unset($sha256);
        flush();
    }
}


//Parser de Demais tipos de Produção Bibliográfica

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'})) {

    if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'}->{'PARTITURA-MUSICAL'})) {

        $partituraArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'}->{'PARTITURA-MUSICAL'};
        foreach ($partituraArray as $obra) {
            $obra = get_object_vars($obra);
            $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DA-PARTITURA"]);
            $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DA-PARTITURA"]);

            $doc["doc"]["type"] = "Work";
            $doc["doc"]["tipo"] = "Partitura musical";
            $doc["doc"]["source"] = "Base Lattes";
            $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
            if (isset($_REQUEST['tag'])) {
                $doc["doc"]["tag"][] = $_REQUEST['tag'];
            } else {
                $doc["doc"]["tag"][] = "Lattes";
            }
            $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
            $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO"];
            $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO"];
            $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
            $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
            $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
            $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
            $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
            $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
            if ($testDOI === 1) {
                $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
            }
            $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-INGLES"];

            $doc["doc"]["formacaoInstrumental"] = $detalhamentoDoTrabalho['@attributes']["FORMACAO-INSTRUMENTAL"];
            $doc["doc"]["publisher"]["organization"]["name"] = $detalhamentoDoTrabalho['@attributes']["EDITORA"];
            $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DA-EDITORA"];
            $doc["doc"]["numberOfPages"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DE-PAGINAS"];
            $doc["doc"]["lattes"]["numeroDoCatalogo"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DO-CATALOGO"];


            if (!empty($obra["AUTORES"])) {
                $array_result = processaAutoresLattes($obra["AUTORES"]);
                $doc = array_merge_recursive($doc, $array_result);
            }

            if (isset($obra["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $doc = array_merge_recursive($doc, $array_result_pc);
                }
                unset($array_result_pc);
            }

            if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                if (isset($array_result_ac)) {
                    $doc = array_merge_recursive($doc, $array_result_ac);
                }
                unset($array_result_ac);
            }

            // Vinculo
            $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

            // Constroi sha256

            if (!empty($doc["doc"]["doi"])) {
                $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
            } else {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["tipo"];
                $sha_array[] = $doc["doc"]["name"];
                $sha_array[] = $doc["doc"]["datePublished"];
                $sha_array[] = $doc["doc"]["url"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            }


            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
            $doc["doc"]["concluido"] = "Não";
            $doc["doc_as_upsert"] = true;

            // Comparador
            $resultado = upsert($doc, $sha256);

            unset($dadosBasicosDoTrabalho);
            unset($detalhamentoDoTrabalho);
            unset($obra);
            unset($doc);
            unset($sha_array);
            unset($sha256);
            flush();
        }
    }

    if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'}->{'TRADUCAO'})) {

        $traducaoArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'}->{'TRADUCAO'};
        foreach ($traducaoArray as $obra) {
            $obra = get_object_vars($obra);
            $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DA-TRADUCAO"]);
            $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DA-TRADUCAO"]);

            $doc["doc"]["type"] = "Work";
            $doc["doc"]["tipo"] = "Tradução";
            $doc["doc"]["source"] = "Base Lattes";
            $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
            if (isset($_REQUEST['tag'])) {
                $doc["doc"]["tag"][] = $_REQUEST['tag'];
            } else {
                $doc["doc"]["tag"][] = "Lattes";
            }
            $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
            $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO"];
            $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO"];
            $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
            $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
            $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
            $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
            $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
            $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
            if ($testDOI === 1) {
                $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
            }
            $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-INGLES"];

            $doc["doc"]["originalName"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DA-OBRA-ORIGINAL"];
            $doc["doc"]["issnIsbn"] = $detalhamentoDoTrabalho['@attributes']["ISSN-ISBN"];
            $doc["doc"]["originalLanguage"] = $detalhamentoDoTrabalho['@attributes']["IDIOMA-DA-OBRA-ORIGINAL"];
            $doc["doc"]["publisher"]["organization"]["name"] = $detalhamentoDoTrabalho['@attributes']["EDITORA-DA-TRADUCAO"];
            $doc["doc"]["publisher"]["organization"]["location"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DA-EDITORA"];
            $doc["doc"]["numberOfPages"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DE-PAGINAS"];
            $doc["doc"]["bookEdition"] = $detalhamentoDoTrabalho['@attributes']["NUMERO-DA-EDICAO-REVISAO"];
            $doc["doc"]["volume"] = $detalhamentoDoTrabalho['@attributes']["VOLUME"];
            $doc["doc"]["fasciculo"] = $detalhamentoDoTrabalho['@attributes']["FASCICULO"];
            $doc["doc"]["serie"] = $detalhamentoDoTrabalho['@attributes']["FASCICULO"];


            if (!empty($obra["AUTORES"])) {
                $array_result = processaAutoresLattes($obra["AUTORES"]);
                $doc = array_merge_recursive($doc, $array_result);
            }

            if (isset($obra["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $doc = array_merge_recursive($doc, $array_result_pc);
                }
                unset($array_result_pc);
            }

            if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                if (isset($array_result_ac)) {
                    $doc = array_merge_recursive($doc, $array_result_ac);
                }
                unset($array_result_ac);
            }

            // Vinculo
            $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

            // Constroi sha256
            if (!empty($doc["doc"]["doi"])) {
                $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
            } else {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["tipo"];
                $sha_array[] = $doc["doc"]["name"];
                $sha_array[] = $doc["doc"]["datePublished"];
                $sha_array[] = $doc["doc"]["url"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            }


            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
            $doc["doc"]["concluido"] = "Não";
            $doc["doc_as_upsert"] = true;

            // Comparador
            $resultado = upsert($doc, $sha256);

            unset($dadosBasicosDoTrabalho);
            unset($detalhamentoDoTrabalho);
            unset($obra);
            unset($doc);
            unset($sha_array);
            unset($sha256);
            flush();
        }
    }
}

//Parser de Produção Técnica

if (isset($curriculo->{'PRODUCAO-TECNICA'})) {

    if (isset($curriculo->{'PRODUCAO-TECNICA'}->{'SOFTWARE'})) {

        $softwareArray = $curriculo->{'PRODUCAO-TECNICA'}->{'SOFTWARE'};
        foreach ($softwareArray as $obra) {
            $obra = get_object_vars($obra);
            $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-SOFTWARE"]);
            $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-SOFTWARE"]);

            $doc["doc"]["type"] = "Work";
            $doc["doc"]["tipo"] = "Software";
            $doc["doc"]["source"] = "Base Lattes";
            $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
            if (isset($_REQUEST['tag'])) {
                $doc["doc"]["tag"][] = $_REQUEST['tag'];
            } else {
                $doc["doc"]["tag"][] = "Lattes";
            }
            $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
            $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-SOFTWARE"];
            $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO"];
            $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS"];
            $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
            $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
            $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
            $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
            $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
            if ($testDOI === 1) {
                $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
            }
            $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-SOFTWARE-INGLES"];
            $doc["doc"]["lattes"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];
            $doc["doc"]["lattes"]["flagPotencialInovacao"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-POTENCIAL-INOVACAO"];

            $doc["doc"]["lattes"]["finalidade"] = $detalhamentoDoTrabalho['@attributes']["FINALIDADE"];
            $doc["doc"]["lattes"]["plataforma"] = $detalhamentoDoTrabalho['@attributes']["PLATAFORMA"];
            $doc["doc"]["lattes"]["ambiente"] = $detalhamentoDoTrabalho['@attributes']["AMBIENTE"];
            $doc["doc"]["lattes"]["disponibilidade"] = $detalhamentoDoTrabalho['@attributes']["DISPONIBILIDADE"];
            $doc["doc"]["lattes"]["instituicaoFinanciadora"] = $detalhamentoDoTrabalho['@attributes']["INSTITUICAO-FINANCIADORA"];

            if (!empty($obra["AUTORES"])) {
                $array_result = processaAutoresLattes($obra["AUTORES"]);
                $doc = array_merge_recursive($doc, $array_result);
            }

            if (isset($obra["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $doc = array_merge_recursive($doc, $array_result_pc);
                }
                unset($array_result_pc);
            }

            if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                if (isset($array_result_ac)) {
                    $doc = array_merge_recursive($doc, $array_result_ac);
                }
                unset($array_result_ac);
            }

            // Vinculo
            $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

            // Constroi sha256


            if (!empty($doc["doc"]["doi"])) {
                $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
            } else {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["tipo"];
                $sha_array[] = $doc["doc"]["name"];
                $sha_array[] = $doc["doc"]["datePublished"];
                $sha_array[] = $doc["doc"]["url"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            }


            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
            $doc["doc"]["concluido"] = "Não";
            $doc["doc_as_upsert"] = true;

            // Comparador
            $resultado = upsert($doc, $sha256);

            unset($dadosBasicosDoTrabalho);
            unset($detalhamentoDoTrabalho);
            unset($obra);
            unset($doc);
            unset($sha_array);
            unset($sha256);
            flush();
        }
    }

    if (isset($curriculo->{'PRODUCAO-TECNICA'}->{'PATENTE'})) {

        $patenteArray = $curriculo->{'PRODUCAO-TECNICA'}->{'PATENTE'};
        foreach ($patenteArray as $obra) {
            $obra = get_object_vars($obra);
            $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DA-PATENTE"]);
            $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DA-PATENTE"]);

            $doc["doc"]["type"] = "Work";
            $doc["doc"]["tipo"] = "Patente";
            $doc["doc"]["source"] = "Base Lattes";
            $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
            if (isset($_REQUEST['tag'])) {
                $doc["doc"]["tag"][] = $_REQUEST['tag'];
            } else {
                $doc["doc"]["tag"][] = "Lattes";
            }
            $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO"];
            $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DESENVOLVIMENTO"];
            $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS"];
            $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
            $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE"];
            $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
            $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-INGLES"];
            $doc["doc"]["lattes"]["flagPotencialInovacao"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-POTENCIAL-INOVACAO"];

            $doc["doc"]["lattes"]["finalidade"] = $detalhamentoDoTrabalho['@attributes']["FINALIDADE"];
            $doc["doc"]["lattes"]["instituicaoFinanciadora"] = $detalhamentoDoTrabalho['@attributes']["INSTITUICAO-FINANCIADORA"];
            $doc["doc"]["lattes"]["categoria"] = $detalhamentoDoTrabalho['@attributes']["CATEGORIA"];


            if (!empty($obra["AUTORES"])) {
                $array_result = processaAutoresLattes($obra["AUTORES"]);
                $doc = array_merge_recursive($doc, $array_result);
            }

            if (isset($obra["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $doc = array_merge_recursive($doc, $array_result_pc);
                }
                unset($array_result_pc);
            }

            if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                if (isset($array_result_ac)) {
                    $doc = array_merge_recursive($doc, $array_result_ac);
                }
                unset($array_result_ac);
            }

            // Vinculo
            $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

            // Constroi sha256


            if (!empty($doc["doc"]["doi"])) {
                $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
            } else {
                $sha_array[] = $doc["doc"]["lattes_ids"][0];
                $sha_array[] = $doc["doc"]["tipo"];
                $sha_array[] = $doc["doc"]["name"];
                $sha_array[] = $doc["doc"]["datePublished"];
                $sha_array[] = $doc["doc"]["url"];
                $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
            }


            //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
            $doc["doc"]["concluido"] = "Não";
            $doc["doc_as_upsert"] = true;

            // Comparador
            $resultado = upsert($doc, $sha256);

            unset($dadosBasicosDoTrabalho);
            unset($detalhamentoDoTrabalho);
            unset($obra);
            unset($doc);
            unset($sha_array);
            unset($sha256);
            flush();
        }
    }
}

//Parser de Outra Produção

if (isset($curriculo->{'OUTRA-PRODUCAO'})) {

    if (isset($curriculo->{'OUTRA-PRODUCAO'}->{'PRODUCAO-ARTISTICA-CULTURAL'})) {

        if (isset($curriculo->{'OUTRA-PRODUCAO'}->{'PRODUCAO-ARTISTICA-CULTURAL'}->{'APRESENTACAO-DE-OBRA-ARTISTICA'})) {

            $obraArtisticaArray = $curriculo->{'OUTRA-PRODUCAO'}->{'PRODUCAO-ARTISTICA-CULTURAL'}->{'APRESENTACAO-DE-OBRA-ARTISTICA'};
            foreach ($obraArtisticaArray as $obra) {
                $obra = get_object_vars($obra);
                $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DA-APRESENTACAO-DE-OBRA-ARTISTICA"]);
                $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DA-APRESENTACAO-DE-OBRA-ARTISTICA"]);

                $doc["doc"]["type"] = "Work";
                $doc["doc"]["tipo"] = "Apresentação de obra artística";
                $doc["doc"]["source"] = "Base Lattes";
                $doc["doc"]["lattes_ids"][] = (string) $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
                if (isset($_REQUEST['tag'])) {
                    $doc["doc"]["tag"][] = $_REQUEST['tag'];
                } else {
                    $doc["doc"]["tag"][] = "Lattes";
                }
                $doc["doc"]["lattes"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
                $doc["doc"]["name"] = $dadosBasicosDoTrabalho['@attributes']["TITULO"];
                $doc["doc"]["datePublished"] = $dadosBasicosDoTrabalho['@attributes']["ANO"];
                $doc["doc"]["country"] = $dadosBasicosDoTrabalho['@attributes']["PAIS"];
                $doc["doc"]["language"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
                $doc["doc"]["lattes"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
                $doc["doc"]["url"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE"];
                $doc["doc"]["lattes"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
                $testDOI = Testadores::testDOI($dadosBasicosDoTrabalho['@attributes']["DOI"]);
                if ($testDOI === 1) {
                    $doc["doc"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
                }
                $doc["doc"]["alternateName"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-INGLES"];

                $doc["doc"]["lattes"]["tipoDeEvento"] = $detalhamentoDoTrabalho['@attributes']["TIPO-DE-EVENTO"];
                $doc["doc"]["lattes"]["atividadeDosAutores"] = $detalhamentoDoTrabalho['@attributes']["ATIVIDADE-DOS-AUTORES"];
                $doc["doc"]["lattes"]["flagIneditismoDaObra"] = $detalhamentoDoTrabalho['@attributes']["FLAG-INEDITISMO-DA-OBRA"];
                $doc["doc"]["lattes"]["premiacao"] = $detalhamentoDoTrabalho['@attributes']["PREMIACAO"];
                $doc["doc"]["lattes"]["obraDeReferencia"] = $detalhamentoDoTrabalho['@attributes']["OBRA-DE-REFERENCIA"];
                $doc["doc"]["lattes"]["autorDaObraDeReferencia"] = $detalhamentoDoTrabalho['@attributes']["AUTOR-DA-OBRA-DE-REFERENCIA"];
                $doc["doc"]["lattes"]["anoDaObraDeReferencia"] = $detalhamentoDoTrabalho['@attributes']["ANO-DA-OBRA-DE-REFERENCIA"];
                $doc["doc"]["lattes"]["duracaoEmMinutos"] = $detalhamentoDoTrabalho['@attributes']["DURACAO-EM-MINUTOS"];
                $doc["doc"]["lattes"]["instituicaoPromotoraDoEvento"] = $detalhamentoDoTrabalho['@attributes']["INSTITUICAO-PROMOTORA-DO-EVENTO"];
                $doc["doc"]["lattes"]["localDoEvento"] = $detalhamentoDoTrabalho['@attributes']["LOCAL-DO-EVENTO"];
                $doc["doc"]["lattes"]["cidade"] = $detalhamentoDoTrabalho['@attributes']["CIDADE"];


                if (!empty($obra["AUTORES"])) {
                    $array_result = processaAutoresLattes($obra["AUTORES"]);
                    $doc = array_merge_recursive($doc, $array_result);
                }

                if (isset($obra["PALAVRAS-CHAVE"])) {
                    $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
                    if (isset($array_result_pc)) {
                        $doc = array_merge_recursive($doc, $array_result_pc);
                    }
                    unset($array_result_pc);
                }

                if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $doc = array_merge_recursive($doc, $array_result_ac);
                    }
                    unset($array_result_ac);
                }

                // Vinculo
                $doc["doc"]["vinculo"] = construct_vinculo($_REQUEST, $curriculo);

                // Constroi sha256


                if (!empty($doc["doc"]["doi"])) {
                    $sha256 = hash('sha256', '' . $doc["doc"]["doi"] . '');
                } else {
                    $sha_array[] = $doc["doc"]["lattes_ids"][0];
                    $sha_array[] = $doc["doc"]["tipo"];
                    $sha_array[] = $doc["doc"]["name"];
                    $sha_array[] = $doc["doc"]["datePublished"];
                    $sha_array[] = $doc["doc"]["url"];
                    $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
                }


                //$doc["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc["doc"]["name"], $doc["doc"]["datePublished"]);
                $doc["doc"]["concluido"] = "Não";
                $doc["doc_as_upsert"] = true;

                // Comparador
                $resultado = upsert($doc, $sha256);

                unset($dadosBasicosDoTrabalho);
                unset($detalhamentoDoTrabalho);
                unset($obra);
                unset($doc);
                unset($sha_array);
                unset($sha256);
                flush();
            }
        }
    }
}

//sleep(5);

$url = 'result.php';
$data = [];
$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

header('Location: ' . $url);