<?php

/**
 * Arquivo de classes e funções do ColetaProd
 */
include('config.php');
include('components/Categories.php');

/* Connect to Elasticsearch - Index */
try {
  $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
  //print("<pre>".print_r($client,true)."</pre>");
  $indexParams['index'] = $index;
  $testIndex = $client->indices()->exists($indexParams);
} catch (Exception $e) {
  $error_connection_message = '<div class="alert alert-danger" role="alert">Elasticsearch não foi encontrado.</div>';
}

/* Create index if not exists */
if (isset($testIndex) && $testIndex == false) {
  Elasticsearch::createIndex($index, $client);
  Elasticsearch::mappingsIndex($index, $client);
}

/* Connect to Elasticsearch | Index CV */
try {
  $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
  //print("<pre>".print_r($client,true)."</pre>");
  $indexParams['index'] = $index_cv;
  $testIndexCV = $client->indices()->exists($indexParams);
} catch (Exception $e) {
  $error_connection_message = '<div class="alert alert-danger" role="alert">Índice de CV no Elasticsearch não foi encontrado.</div>';
}

/* Create index if not exists */
if (isset($testIndexCV) && $testIndexCV == false) {
  Elasticsearch::createIndex($index_cv, $client);
  Elasticsearch::mappingsIndexCV($index_cv, $client);
}

/* Connect to Elasticsearch | Index PPGs */
try {
  $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
  $indexParams['index'] = $index_ppg;
  $testIndexPPG = $client->indices()->exists($indexParams);
} catch (Exception $e) {
  $error_connection_message = '<div class="alert alert-danger" role="alert">Índice de PPG no Elasticsearch não foi encontrado.</div>';
}

/* Create index if not exists */
if (isset($testIndexPPG) && $testIndexPPG == false) {
  Elasticsearch::createIndex($index_ppg, $client);
}


/* Definição de idioma */

setlocale(LC_ALL, 'pt_BR');

// if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
//     if (empty($_SESSION['localeToUse'])) {
//         $_SESSION['localeToUse'] = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
//     }
// } else {
//     if (empty($_SESSION['localeToUse'])) {
//         $_SESSION['localeToUse'] = Locale::getDefault();
//     }
// }

// if (!empty($_GET['locale'])) {
//     $_SESSION['localeToUse'] = $_GET["locale"];
// }


// //use Gettext\Translator;

// //Create the translator instance
// //$t = new Translator();

// if ($_SESSION['localeToUse'] == 'pt_BR') {
//     //$t->loadTranslations(__DIR__.'/../Locale/pt_BR/LC_MESSAGES/pt_BR.php');
// } else {
//     //$t->loadTranslations(__DIR__.'/../Locale/en_US/LC_MESSAGES/en.php');
// }



function toPercent($number)
{
  return number_format((float) $number, 2, '.', '') . '%';
}


function pregReplaceVariableName($string)
{

  $arrayString = explode("-", $string);
  $arrayString = array_map('ucwords', $arrayString);
  $result = implode("", $arrayString);
  $result = lcfirst($result);
  return $result;
}

/**
 * Compara registros que estão entrando com os já existentes na base
 */
class bak_compararRegistros
{

  /**
   * Consulta registros por DOI
   * 
   * @param string $doi DOI
   * 
   */
  public static function doi($doi)
  {
    global $index;
    global $client;
    global $type;
    $body = '
            {
                "query":{
                    "match" : {
                        "doi": "' . $doi . '"
                    }
                }
            }
        ';
    $response = Elasticsearch::search(null, $size, $body);
    return $response;
  }

  /**
   * Consulta trabalhos em eventos já existentes
   * 
   * @param string $ano Ano
   * @param string $titulo Título do trabalho de evento
   * @param string $nome_do_evento Nome do evento
   * @param string $tipo Tipo do registro                                      
   * 
   */
  public static function lattesEventos($ano, $titulo, $nome_do_evento, $tipo)
  {
    global $index;
    global $client;
    $body = '
        {
            "min_score": 30,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "' . $tipo . '",
                                "type":       "cross_fields",
                                "fields":     [ "tipo" ],
                                "minimum_should_match": "100%" 
                             }
                        },		
                        {
                            "multi_match" : {
                                "query":      "' . $titulo . '",
                                "type":       "cross_fields",
                                "fields":     [ "titulo" ],
                                "minimum_should_match": "90%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "' . $nome_do_evento . '",
                                "type":       "cross_fields",
                                "fields":     [ "evento.nome_do_evento" ],
                                "minimum_should_match": "80%" 
                             }
                        },		    
                        {
                            "multi_match" : {
                                "query":      "' . $ano . '",
                                "type":       "best_fields",
                                "fields":     [ "ano" ],
                                "minimum_should_match": "75%" 
                            }
                        }
                    ],
                    "minimum_should_match" : 1               
                }
            }
        }
        ';
    $type = "trabalhos";
    $response = Elasticsearch::search(null, null, $body);
    return $response;
  }

  public static function lattesArtigos($ano, $titulo, $titulo_do_periodico, $doi, $tipo)
  {
    global $index;
    global $client;
    $body = '
            {
                "min_score": 10,
                "query":{
                    "bool": {
                        "should": [
                            {
                                "multi_match" : {
                                    "query":      "' . $tipo . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "tipo" ],
                                    "minimum_should_match": "100%" 
                                 }
                            },
                            {
                                "multi_match" : {
                                    "query":      "' . $doi . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "doi" ],
                                    "minimum_should_match": "100%" 
                                 }
                            },			    		
                            {
                                "multi_match" : {
                                    "query":      "' . $titulo . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "titulo" ],
                                    "minimum_should_match": "90%" 
                                 }
                            },
                            {
                                "multi_match" : {
                                    "query":      "' . $titulo_do_periodico . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "periodico.titulo_do_periodico" ],
                                    "minimum_should_match": "80%" 
                                 }
                            },		    
                            {
                                "multi_match" : {
                                    "query":      "' . $ano . '",
                                    "type":       "best_fields",
                                    "fields":     [ "ano" ],
                                    "minimum_should_match": "75%" 
                                }
                            }
                        ],
                        "minimum_should_match" : 3               
                    }
                }
            }
        ';

    $type = "trabalhos";
    $response = Elasticsearch::search(null, null, $body);
    return $response;
  }

  public static function lattesLivros($titulo, $isbn, $tipo)
  {
    global $index;
    global $client;
    $body = '
        {
            "min_score": 10,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "' . $tipo . '",
                                "type":       "cross_fields",
                                "fields":     [ "tipo" ],
                                "minimum_should_match": "100%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "' . $isbn . '",
                                "type":       "cross_fields",
                                "fields":     [ "isbn" ],
                                "minimum_should_match": "100%" 
                             }
                        },			    		
                        {
                            "multi_match" : {
                                "query":      "' . $titulo . '",
                                "type":       "cross_fields",
                                "fields":     [ "titulo" ],
                                "minimum_should_match": "90%" 
                             }
                        }
                    ],
                    "minimum_should_match" : 2               
                }
            }
        }
        ';
    $type = "trabalhos";
    $response = Elasticsearch::search(null, null, $body);
    return $response;
  }

  public static function lattesCapitulos($titulo, $titulo_do_livro, $tipo)
  {
    global $index;
    global $client;
    $body = '
            {
                "min_score": 2,
                "query":{
                    "bool": {
                        "should": [
                            {
                                "multi_match" : {
                                    "query":      "' . $tipo . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "tipo" ],
                                    "minimum_should_match": "100%" 
                                 }
                            },		    		
                            {
                                "multi_match" : {
                                    "query":      "' . $titulo . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "titulo" ],
                                    "minimum_should_match": "90%" 
                                 }
                            },
                            {
                                "multi_match" : {
                                    "query":      "' . $titulo_do_livro . '",
                                    "type":       "cross_fields",
                                    "fields":     [ "capitulo_do_livro.titulo_do_livro" ],
                                    "minimum_should_match": "90%" 
                                 }
                            }                    
                        ],
                        "minimum_should_match" : 3               
                    }
                }
            }
        ';
    $type = "trabalhos";
    $response = Elasticsearch::search(null, null, $body);
    return $response;
  }

  public static function lattesMidiaSocial($titulo, $url, $tipo)
  {
    global $index;
    global $client;
    $body = '
        {
            "min_score": 3,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "' . $tipo . '",
                                "type":       "cross_fields",
                                "fields":     [ "tipo" ],
                                "minimum_should_match": "100%" 
                             }
                        },		    		
                        {
                            "multi_match" : {
                                "query":      "' . $titulo . '",
                                "type":       "cross_fields",
                                "fields":     [ "titulo" ],
                                "minimum_should_match": "90%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "' . $url . '",
                                "type":       "cross_fields",
                                "fields":     [ "url" ],
                                "minimum_should_match": "100%" 
                             }
                        }                    
                    ],
                    "minimum_should_match" : 3               
                }
            }
        }
        ';
    $type = "trabalhos";
    $response = Elasticsearch::search(null, null, $body);
    return $response;
  }

  public static function match_id($_id, $nota)
  {
    $fields = ['titulo', 'tipo', 'ano'];
    $response = Elasticsearch::get($_id, $fields);

    echo '<div class="uk-alert uk-alert-danger">';
    echo '<h3>Registros similares no Coleta Produção USP</h3>';
    echo '<p><a href="result.php?&search[]=+_id:&quot;' . $_id . '&quot;">' . $response["_source"]["tipo"] . ' - ' . $response["_source"]["titulo"] . ' (' . $response["_source"]["ano"] . ') - Nota de proximidade: ' . $nota . '</a></p>';
    echo '</div>';
  }
}

/**
 * Funções executadas na página principal
 */
class paginaInicial
{

  static function contar_registros_indice($index)
  {
    global $client;
    $params = [];
    $params["index"] = $index;
    $response = $client->count($params);
    return number_format($response['count'], 0, ',', '.');
  }

  static function contar_tipo_de_registro($type, $index_cv = null)
  {
    $body = '
            {
                "query": {
                    "bool":{
                        "filter":{
                            "term": {
                                "type.keyword":"' . $type . '"
                            }
                        }
                    }
                }
            }        
        ';
    $size = 0;
    $response = Elasticsearch::search(null, $size, $body, $index_cv);
    return number_format($response['hits']['total']['value'], 0, ',', '.');
  }

  static function contar_registros_match($type)
  {
    $body = '
            {
                "query": {
                    "exists" : { "field" : "ids_match" }
                }
            }          
        ';
    $size = 0;
    $response = Elasticsearch::search(null, $size, $body);
    return number_format($response['hits']['total']['value'], 0, ',', '.');
  }

  static function fonte_inicio()
  {
    global $client;
    global $index;
    $query = '{
            "query": {
                "bool":{
                    "filter":{
                        "term": {
                            "type.keyword":"Work"
                        }
                    }
                }
            },
            "aggs": {
                "group_by_state": {
                    "terms": {
                        "field": "source.keyword",                    
                        "size" : 100
                    }
                }
            }
        }';

    $params = [
      'index' => $index,
      'size' => 0,
      'body' => $query
    ];

    $response = $client->search($params);
    Categories::list($response, 'source');
  }

  static function tipo_inicio()
  {
    global $client;
    global $index;
    $query = '{
            "query": {
                "bool":{
                    "filter":{
                        "term": {
                            "type.keyword":"Work"
                        }
                    }
                }
            },            
            "aggs": {
                "group_by_state": {
                    "terms": {
                        "field": "tipo.keyword",                    
                        "size" : 50
                    }
                }
            }
        }';

    $params = [
      'index' => $index,
      'size' => 0,
      'body' => $query
    ];

    $response = $client->search($params);
    Categories::list($response, 'tipo');
  }

  static function unidade_inicio($field)
  {
    global $client;
    global $index;
    $query = '{
            "query": {
                "bool":{
                    "filter":{
                        "term": {
                            "type.keyword":"Work"
                        }
                    }
                }
            },            
            "aggs": {
                "group_by_state": {
                    "terms": {
                        "field": "' . $field . '.keyword",                    
                        "size" : 200
                    }
                }
            }
        }';

    $params = [
      'index' => $index,
      'size' => 0,
      'body' => $query
    ];

    $response = $client->search($params);
    Categories::list($response, $field);
  }

  static function possui_lattes()
  {
    global $index_cv;
    global $client;
    $body["index"] = $index_cv;
    $cursor = $client->count($body);
    $total = $cursor["count"];

    $body["body"]["query"]["bool"]["must_not"]["exists"]["field"] = "lattesID";
    $cursorTotal = $client->count($body);
    $total_dont_have_lattes = $cursorTotal["count"];

    return number_format((float) ($total_dont_have_lattes / $total) * 100, 2, '.', '');
  }

  static function filter_select($field)
  {
    global $client;
    global $index;
    $query['aggs']['group_by_state']['terms']['field'] = "$field.keyword";
    $query['aggs']['group_by_state']['terms']['size'] = 200;
    $query["aggs"]['group_by_state']["terms"]["order"]['_term'] = "asc";
    $params = [
      'index' => $index,
      'size' => 0,
      'body' => $query
    ];
    $response = $client->search($params);
    echo '<select class="c-input" name="filter[]" aria-label="Filtro">
        <option value="" selected>Escolha o nome do programa de pós-graduação</option>';
    foreach ($response["aggregations"]["group_by_state"]["buckets"] as $facets) {
      echo '<option value="' . $field . ':' . $facets['key'] . '">' . $facets['key'] . '</option>';
    }
    echo '</select>';
  }
}

class DadosInternos
{

  static function queryProdmais($query_title, $query_year, $sha256)
  {

    global $client;
    global $index;

    $query_title = str_replace('"', '', $query_title);
    $query["min_score"] = 50;
    $query["query"]["bool"]["should"][0]["multi_match"]["query"] = $query_title;
    $query["query"]["bool"]["should"][0]["multi_match"]["type"] = "cross_fields";
    $query["query"]["bool"]["should"][0]["multi_match"]["fields"][] = "name";
    $query["query"]["bool"]["should"][0]["multi_match"]["minimum_should_match"] = "90%";
    $query["query"]["bool"]["should"][1]["multi_match"]["query"] = $query_year;
    $query["query"]["bool"]["should"][1]["multi_match"]["type"] = "best_fields";
    $query["query"]["bool"]["should"][1]["multi_match"]["fields"][] = "datePublished";
    $query["query"]["bool"]["should"][1]["multi_match"]["operator"] = "and";
    $query["query"]["bool"]["should"][1]["multi_match"]["minimum_should_match"] = "100%";
    $query["query"]["bool"]["minimum_should_match"] = 2;

    $params = [];

    $params["index"] = $index;
    //$params["_source"] = $fields;
    //$params["size"] = $size;
    $params["body"] = $query;

    $data = $client->search($params);

    if ($data["hits"]["total"]["value"] > 0) {

      foreach ($data["hits"]["hits"] as $match) {
        if ($sha256 != $match["_id"]) {

          echo '
          <details class="c-collapse" >
            <summary class=".c-collapse-summary">
            <b>Registros similares no Prod+</b></p>
            </summary>

            <div id="collapse' . $match["_id"] . '">';

          echo '<p class="t t-gray"> Proximidade: ' . toPercent($match["_score"]);

          echo '<p class="t t-gray"> <a class="t" href="http://localhost/coletaprod/item/' . $match["_id"] . '" target="_blank">' . $match["_source"]["tipo"] . ' - ' . $match["_source"]["name"] . ' (' . $match["_source"]["datePublished"] . ')</a></p>';

          echo '<p';
          foreach ($match["_source"]['author'] as $autores) {
            $autArray[] = $autores['person']['name'];
          }
          echo implode("; ", $autArray);
          echo '</p>';

          if (isset($match["_source"]["doi"])) {
            echo '<p class="t t-gray">DOI: <a href="https://doi.org/' . $match["_source"]["doi"] . '">' . $match["_source"]["doi"] . '</a></p>';
            $doc["doc"]["bdpi"]["doi_bdpi"] = $match["_source"]["doi"];
          }
          echo '</p>';

          echo '<p class="t t-gray"> Fonte: ' . $match["_source"]["source"] . '<p/>';

          unset($autArray);
          echo '</div>';
          echo '</details>';
        }
      }
    }
    return $data;
  }
}

/**
 * Classe que obtem dados de fontes externas
 */
class DadosExternos
{

  static function querySource($query_title, $query_year, $sha256)
  {

    global $client;
    global $index_source;

    $query_title = str_replace('"', '', $query_title);
    $query["min_score"] = 50;
    $query["query"]["bool"]["should"][0]["multi_match"]["query"] = $query_title;
    $query["query"]["bool"]["should"][0]["multi_match"]["type"] = "cross_fields";
    $query["query"]["bool"]["should"][0]["multi_match"]["fields"][] = "name";
    $query["query"]["bool"]["should"][0]["multi_match"]["minimum_should_match"] = "95%";
    $query["query"]["bool"]["should"][1]["multi_match"]["query"] = $query_year;
    $query["query"]["bool"]["should"][1]["multi_match"]["type"] = "best_fields";
    $query["query"]["bool"]["should"][1]["multi_match"]["fields"][] = "datePublished";
    $query["query"]["bool"]["should"][1]["multi_match"]["operator"] = "and";
    $query["query"]["bool"]["should"][1]["multi_match"]["minimum_should_match"] = "100%";
    $query["query"]["bool"]["minimum_should_match"] = 2;

    $params = [];

    $params["index"] = $index_source;
    //$params["_source"] = $fields;
    //$params["size"] = $size;
    $params["body"] = $query;

    $data = $client->search($params);

    if ($data["hits"]["total"]["value"] > 0) {
      echo '<div class="alert alert-warning" role="alert">';
      echo '<h5>Registros similares na FONTE</h5>';
      foreach ($data["hits"]["hits"] as $match) {
        echo '<p>Nota de proximidade: ' . $match["_score"] . ' - <a href="http://localhost/coletaprod/item/' . $match["_id"] . '" target="_blank">' . $match["_source"]["tipo"] . ' - ' . $match["_source"]["name"] . ' (' . $match["_source"]["datePublished"] . ')</a><br/> Autores: ';
        foreach ($match["_source"]['author'] as $autores) {
          $autArray[] = $autores['person']['name'];
        }
        echo implode("; ", $autArray);
        if (isset($match["_source"]["doi"])) {
          echo '<p>DOI: <a href="https://doi.org/' . $match["_source"]["doi"] . '">' . $match["_source"]["doi"] . '</a></p>';
          $doc["doc"]["bdpi"]["doi_bdpi"] = $match["_source"]["doi"];
        }
        echo '</p>';
        unset($autArray);
      }
      echo '</div>';

      $doc["doc"]["bdpi"]["existe"] = "Sim";
      $doc["doc_as_upsert"] = true;
      //print_r($doc);
      $result_elastic = Elasticsearch::update($sha256, $doc);
    } else {
      $doc["doc"]["bdpi"]["existe"] = "Não";
      $doc["doc_as_upsert"] = true;
      $result_elastic = Elasticsearch::update($sha256, $doc);
    }
    return $data;
  }

  static function query_bdpi($query_title, $query_year, $sha256)
  {

    global $client_bdpi;
    global $index_bdpi;

    $query_title = str_replace('"', '', $query_title);
    $query["min_score"] = 50;
    $query["query"]["bool"]["should"][0]["multi_match"]["query"] = $query_title;
    $query["query"]["bool"]["should"][0]["multi_match"]["type"] = "cross_fields";
    $query["query"]["bool"]["should"][0]["multi_match"]["fields"][] = "name";
    $query["query"]["bool"]["should"][0]["multi_match"]["minimum_should_match"] = "95%";
    $query["query"]["bool"]["should"][1]["multi_match"]["query"] = $query_year;
    $query["query"]["bool"]["should"][1]["multi_match"]["type"] = "best_fields";
    $query["query"]["bool"]["should"][1]["multi_match"]["fields"][] = "datePublished";
    $query["query"]["bool"]["should"][1]["multi_match"]["operator"] = "and";
    $query["query"]["bool"]["should"][1]["multi_match"]["minimum_should_match"] = "100%";
    $query["query"]["bool"]["minimum_should_match"] = 2;

    $params = [];

    $params["index"] = $index_bdpi;
    //$params["_source"] = $fields;
    //$params["size"] = $size;
    $params["body"] = $query;

    $data = $client_bdpi->search($params);

    if ($data["hits"]["total"]["value"] > 0) {
      echo '<div class="alert alert-info" role="alert">';
      echo '<h5>Registros similares na DEDALUS</h5>';
      foreach ($data["hits"]["hits"] as $match) {
        echo '<p>Nota de proximidade: ' . $match["_score"] . ' - <a href="http://localhost/ecafind/item/' . $match["_id"] . '" target="_blank">' . $match["_source"]["type"] . ' - ' . $match["_source"]["name"] . ' (' . $match["_source"]["datePublished"] . ')</a><br/> Autores: ';
        foreach ($match["_source"]['author'] as $autores) {
          $autArray[] = $autores['person']['name'];
        }
        echo implode("; ", $autArray);
        if (isset($match["_source"]["doi"])) {
          echo '<p>DOI: ' . $match["_source"]["doi"] . '</p>';
          $doc["doc"]["bdpi"]["doi_bdpi"] = $match["_source"]["doi"];
        }
        echo '</p>';
        unset($autArray);
      }
      echo '</div>';

      $doc["doc"]["bdpi"]["existe"] = "Sim";
      $doc["doc_as_upsert"] = true;
      //print_r($doc);
      $result_elastic = Elasticsearch::update($sha256, $doc);
    } else {
      $doc["doc"]["bdpi"]["existe"] = "Não";
      $doc["doc_as_upsert"] = true;
      $result_elastic = Elasticsearch::update($sha256, $doc);
    }
    return $data;
  }

  static function query_bdpi_index($query_title, $query_year)
  {

    global $client_bdpi;

    $query_title = str_replace('"', '', $query_title);
    $query["min_score"] = 40;
    $query["query"]["bool"]["should"][0]["multi_match"]["query"] = $query_title;
    $query["query"]["bool"]["should"][0]["multi_match"]["type"] = "cross_fields";
    $query["query"]["bool"]["should"][0]["multi_match"]["fields"][] = "name";
    $query["query"]["bool"]["should"][0]["multi_match"]["minimum_should_match"] = "95%";
    $query["query"]["bool"]["should"][1]["multi_match"]["query"] = $query_year;
    $query["query"]["bool"]["should"][1]["multi_match"]["type"] = "best_fields";
    $query["query"]["bool"]["should"][1]["multi_match"]["fields"][] = "datePublished";
    $query["query"]["bool"]["should"][1]["multi_match"]["operator"] = "and";
    $query["query"]["bool"]["should"][1]["multi_match"]["minimum_should_match"] = "100%";
    $query["query"]["bool"]["minimum_should_match"] = 2;

    $params = [];

    $params["index"] = "bdpi";
    $params["type"] = "producao";
    //$params["_source"] = $fields;
    //$params["size"] = $size;
    $params["body"] = $query;

    $data = $client_bdpi->search($params);

    $facet_bdpi = [];
    if ($data["hits"]["total"] > 0) {
      $facet_bdpi["existe"] = "Sim";
      foreach ($data["hits"]["hits"] as $match) {
        if (isset($match["_source"]["doi"])) {
          $facet_bdpi["doi_bdpi"] = $match["_source"]["doi"];
        }
      }
    } else {
      $facet_bdpi["existe"] = "Não";
    }
    return $facet_bdpi;
  }

  static function coleta_json_lattes($id_lattes)
  {

    $ch = curl_init();
    $method = "GET";
    $url = "http://buscacv.cnpq.br/buscacv/rest/espelhocurriculo/$id_lattes";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    $tentativas = 0;
    while ($tentativas < 3) {
      $result = curl_exec($ch);
      $info = curl_getinfo($ch);
      if ($info["http_code"] == 200) {
        var_dump($info);
        $data = json_decode($result, TRUE);
        curl_close($ch);
        return $data;
      } else {
        $tentativas++;
      }
    }


    echo '<br/><br/><br/><h2>Erro ' . $info["http_code"] . ' ao obter o arquivo da Base do Lattes, favor tentar novamente. <a href="index.php">Clique aqui para voltar a página inicial</a></h2>';
    //var_dump($info);
    curl_close($ch);
  }



  static function coleta_json_download_lattes($id_lattes)
  {

    $result = file_get_contents($id_lattes);
    $data = json_decode($result, TRUE);
    return $data;
  }

  static function query_openlibrary($isbn)
  {
    $isbn = trim($isbn);
    $url = "https://openlibrary.org/api/books?bibkeys=ISBN:$isbn&jscmd=details&format=json";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    return $data;
  }


  static function query_doi($doi, $tag)
  {
    global $client;
    global $index;
    $doi = trim($doi);
    $url = "https://api.crossref.org/v1/works/$doi";
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    $sha256 = hash('sha256', '' . $doi . '');

    print_r($data["message"]);

    $doc_obra_array["doc"]["type"] = "Work";
    $doc_obra_array["doc"]["source"] = "Base DOI - CrossRef";
    $doc_obra_array["doc"]["source_id"] = $doi;
    $doc_obra_array["doc"]["doi"] = $doi;
    $doc_obra_array["doc"]["tag"][] = $tag;

    if ($data["message"]["type"] == "journal-article") {
      $doc_obra_array["doc"]["tipo"] = "Artigo publicado";

      if (isset($data["message"]["container-title"][0])) {
        $doc_obra_array["doc"]["isPartOf"]["name"] = $data["message"]["container-title"][0];
      }
      if (isset($data["message"]["ISSN"][0])) {
        $doc_obra_array["doc"]["isPartOf"]["issn"] = $data["message"]["ISSN"][0];
      }
      if (isset($data["message"]["volume"])) {
        $doc_obra_array["doc"]["isPartOf"]["volume"] = $data["message"]["volume"];
      }
      if (isset($data["message"]["issue"])) {
        $doc_obra_array["doc"]["isPartOf"]["fasciculo"] = $data["message"]["issue"];
      }
      if (isset($data["message"]["page"])) {
        $doc_obra_array["doc"]["pageStart"] = $data["message"]["page"];
      }
      if (isset($data["message"]["publisher"])) {
        $doc_obra_array["doc"]["publisher"]["organization"]["name"] = $data["message"]["publisher"];
      }
      if (isset($data["message"]["cited-count"])) {
        $doc_obra_array["doc"]["citacoesRecebidas"] = $data["message"]["cited-count"];
      }
    } elseif ($data["message"]["type"] == "book") {
      $doc_obra_array["doc"]["tipo"] = "Livros publicados ou organizados";
      if (isset($data["message"]["publisher"])) {
        $doc_obra_array["doc"]["publisher"]["organization"]["name"] = $data["message"]["publisher"];
      }
    } else {
      $doc_obra_array["doc"]["tipo"] = $data["message"]["type"];
    }

    /* Título */
    $doc_obra_array["doc"]["name"] = str_replace('"', '', trim($data["message"]["title"][0]));
    $doc_obra_array["doc"]["name"] = str_replace('\'', ' ', trim($doc_obra_array["doc"]["name"]));
    $doc_obra_array["doc"]["name"] = str_replace("\n", "", $doc_obra_array["doc"]["name"]);

    if (isset($data["message"]["subtitle"][0])) {
      $doc_obra_array["doc"]["subtitulo"] = $data["message"]["subtitle"][0];
    }
    if (isset($data["message"]["published-online"]["date-parts"][0][0])) {
      $doc_obra_array["doc"]["datePublished"] = (string) $data["message"]["published-online"]["date-parts"][0][0];
    } elseif (isset($data["message"]["published-print"]["date-parts"][0][0])) {
      $doc_obra_array["doc"]["datePublished"] = $data["message"]["published-print"]["date-parts"][0][0];
    }
    if (isset($data["message"]["URL"])) {
      $doc_obra_array["doc"]["url"] = $data["message"]["URL"];
    }
    $doc_obra_array["doc"]["doi"] = $doi;


    if (isset($data["message"]["subject"])) {
      foreach ($data["message"]["subject"] as $assunto) {
        $doc_obra_array["doc"]["about"][] = $assunto;
      }
    }

    if (isset($data["message"]["funder"])) {
      $doc_obra_array["doc"]["sponsor"]["funder"] = $data["message"]["funder"];
    }

    $i = 0;
    foreach ($data["message"]["author"] as $autores) {
      $doc_obra_array["doc"]["author"][$i]["person"]["name"] = $autores["given"] . " " . $autores["family"];
      $doc_obra_array["doc"]["author"][$i]["nomeParaCitacao"] = $autores["family"] . ", " . $autores["given"];
      if (isset($autores["ORCID"])) {
        $doc_obra_array["doc"]["author"][$i]["id_orcid"] = $autores["ORCID"];
      }
      $i++;
    }

    $doc_obra_array["doc"]["concluido"] = "Não";
    $doc_obra_array["doc_as_upsert"] = true;

    // Retorna resultado
    echo '<br/><br/><br/>';
    print_r($doc_obra_array);

    $body = json_encode($doc_obra_array, JSON_UNESCAPED_UNICODE);

    $resultado_crossref = Elasticsearch::storeRecord($sha256, $body);
    print_r($resultado_crossref);
  }
}

/**
 * Classe que processa dados do JSON obtido da Base Lattes
 */
class processaLattes
{

  static function processaFormacaoAcaddemica($dados, $nivel, $campos, $autor, $id_lattes)
  {
    $i = 0;
    foreach ($dados as $curso) {
      foreach ($campos as $nivel_campos) {
        if (!empty($curso[$nivel_campos])) {
          $doc_curriculo_array["doc"]["formacao_academica_titulacao_$nivel"][$i][$nivel_campos] = $curso[$nivel_campos];
        }
      }
      $i++;
    }
    foreach ($dados as $curso) {
      if ($curso["statusDoCurso"] == "CONCLUIDO") {
        foreach ($campos as $nivel_campos) {
          if (!empty($curso[$nivel_campos])) {
            $doc_tese["doc"]["tese"][$nivel_campos] = $curso[$nivel_campos];
          }
        }
        $doc_tese["doc"]["tese"]["nivel"] = $nivel;
        $doc_tese["doc"]["tese"]["autor"] = $autor;
        $doc_tese["doc"]["tese"]["id_lattes"] = $id_lattes;
        $doc_tese["doc_as_upsert"] = true;
        $sha256_tese = hash('sha256', '' . $id_lattes . $curso["sequenciaFormacao"] . '');
        $doc_tese_json = json_encode($doc_tese, JSON_UNESCAPED_UNICODE);
        Elasticsearch::update($sha256_tese, $doc_tese_json);
      }
    }


    return $doc_curriculo_array;
  }

  static function processaObra($obra, $tipo_de_obra, $tag, $id_lattes, $unidade, $numfuncional)
  {
    switch ($tipo_de_obra) {

      case "trabalhoEmEventos":
        $tipo_de_obra_nome = "Trabalhos em eventos";
        $campos_dadosBasicosDoTrabalho = ["natureza", "tituloDoTrabalho", "anoDoTrabalho", "paisDoEvento", "idioma", "meioDeDivulgacao", "homePageDoTrabalho", "flagRelevancia", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["classificacaoDoEvento", "nomeDoEvento", "cidadeDoEvento", "anoDeRealizacao", "tituloDosAnaisOuProceedings", "paginaInicial", "paginaFinal", "doi", "isbn", "nomeDaEditora", "cidadeDaEditora", "volumeDosAnais", "fasciculoDosAnais", "serieDosAnais"];
        $resultado_comparador_local = compararRegistros::lattesEventos($obra["dadosBasicosDoTrabalho"]["anoDoTrabalho"], str_replace('"', '', $obra["dadosBasicosDoTrabalho"]["tituloDoTrabalho"]), str_replace('"', '', $obra["detalhamentoDoTrabalho"]["nomeDoEvento"]), "Trabalhos em eventos");
        $dadosBasicosNomeCampo = "dadosBasicosDoTrabalho";
        $detalhamentoNomeCampo = "detalhamentoDoTrabalho";
        $campos_sha256 = ["natureza", "tituloDoTrabalho", "anoDoTrabalho", "paisDoEvento", "nomeDoEvento", "paginaInicial", "homePageDoTrabalho"];

        break;

      case "artigoPublicado":
        $tipo_de_obra_nome = "Artigo publicado";
        $campos_dadosBasicosDoTrabalho = ["natureza", "tituloDoArtigo", "anoDoArtigo", "idioma", "meioDeDivulgacao", "homePageDoTrabalho", "flagRelevancia", "doi", "tituloDoArtigoIngles", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["tituloDoPeriodicoOuRevista", "issn", "volume", "serie", "paginaInicial", "paginaFinal", "localDePublicacao"];
        $dadosBasicosNomeCampo = "dadosBasicosDoArtigo";
        $detalhamentoNomeCampo = "detalhamentoDoArtigo";
        if (isset($obra["dadosBasicosDoArtigo"]["doi"])) {
          $campos_sha256 = ["doi"];
          $resultado_comparador_local = compararRegistros::lattesArtigos($obra["dadosBasicosDoArtigo"]["anoDoArtigo"], str_replace('"', '', $obra["dadosBasicosDoArtigo"]["tituloDoArtigo"]), str_replace('"', '', $obra["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"]), $obra["dadosBasicosDoArtigo"]["doi"], "ARTIGO-PUBLICADO");
        } else {
          $campos_sha256 = ["natureza", "tituloDoArtigo", "anoDoArtigo", "tituloDoPeriodicoOuRevista", "nomeDoEvento", "paginaInicial", "homePageDoTrabalho"];
          $resultado_comparador_local = compararRegistros::lattesArtigos($obra["dadosBasicosDoArtigo"]["anoDoArtigo"], str_replace('"', '', $obra["dadosBasicosDoArtigo"]["tituloDoArtigo"]), str_replace('"', '', $obra["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"]), NULL, "Artigo publicado");
        }
        break;

      case "livrosPublicadosOuOrganizado":
        $tipo_de_obra_nome = "Livros publicados ou organizados";
        $campos_dadosBasicosDoTrabalho = ["natureza", "tituloDoLivro", "ano", "paisDePublicacao", "idioma", "meioDeDivulgacao", "homePageDoTrabalho", "flagRelevancia", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["numeroDeVolumes", "numeroDePaginas", "isbn", "numeroDaEdicaoRevisao", "cidadeDaEditora", "nomeDaEditora"];
        $dadosBasicosNomeCampo = "dadosBasicosDoLivro";
        $detalhamentoNomeCampo = "detalhamentoDoLivro";
        if (isset($obra["dadosBasicosDoLivro"]["isbn"])) {
          $campos_sha256 = ["isbn"];
          $resultado_comparador_local = compararRegistros::lattesLivros(str_replace('"', '', $obra["dadosBasicosDoLivro"]["tituloDoLivro"]), str_replace('"', '', $obra["detalhamentoDoLivro"]["isbn"]), "LIVRO-PUBLICADO");
        } else {
          $campos_sha256 = ["natureza", "tituloDoLivro"];
          $resultado_comparador_local = compararRegistros::lattesLivros(str_replace('"', '', $obra["dadosBasicosDoLivro"]["tituloDoLivro"]), NULL, "Livros publicados ou organizados");
        }
        break;

      case "capituloDeLivroPublicado":
        $tipo_de_obra_nome = "Capítulo de livro publicado";
        $campos_dadosBasicosDoTrabalho = ["tituloDoCapituloDoLivro", "ano", "paisDePublicacao", "idioma", "meioDeDivulgacao", "homePageDoTrabalho", "flagRelevancia", "tituloDoCapituloDoLivroIngles", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["tituloDoLivro", "paginaInicial", "paginaFinal", "isbn", "organizadores", "numeroDaEdicaoRevisao", "cidadeDaEditora", "nomeDaEditora"];
        $dadosBasicosNomeCampo = "dadosBasicosDoCapitulo";
        $detalhamentoNomeCampo = "detalhamentoDoCapitulo";
        $campos_sha256 = ["natureza", "tituloDoCapituloDoLivro", "isbn"];
        $resultado_comparador_local = compararRegistros::lattesCapitulos(str_replace('"', '', $obra["dadosBasicosDoCapitulo"]["tituloDoCapituloDoLivro"]), str_replace('"', '', $obra["detalhamentoDoCapitulo"]["tituloDoLivro"]), "Capítulo de livro publicado");
        break;

      case "textoEmJornalOuRevista":
        $tipo_de_obra_nome = "Textos em jornais de notícias/revistas";
        $campos_dadosBasicosDoTrabalho = ["natureza", "tituloDoTexto", "anoDoTexto", "paisDePublicacao", "idioma", "meioDeDivulgacao", "flagRelevancia", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["tituloDoJornalOuRevista", "formatoDataDePublicacao", "dataDePublicacao", "dataPublicacaoFormatoSimples"];
        $dadosBasicosNomeCampo = "dadosBasicosDoTexto";
        $detalhamentoNomeCampo = "detalhamentoDoTexto";
        $campos_sha256 = ["natureza", "tituloDoTexto", "tituloDoJornalOuRevista"];
        break;

      case "midiaSocialWebsiteBlog":
        $tipo_de_obra_nome = "Mídia Social ou Website ou Blog";
        $campos_dadosBasicosDoTrabalho = ["natureza", "titulo", "ano", "pais", "idioma", "homePage", "flagRelevancia", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["tema"];
        $dadosBasicosNomeCampo = "dadosBasicosDaMidiaSocialWebsiteBlog";
        $detalhamentoNomeCampo = "detalhamentoDaMidiaSocialWebsiteBlog";
        $campos_sha256 = ["natureza", "titulo", "homePage"];
        $resultado_comparador_local = compararRegistros::lattesMidiaSocial(str_replace('"', '', $obra["dadosBasicosDaMidiaSocialWebsiteBlog"]["titulo"]), $obra["dadosBasicosDaMidiaSocialWebsiteBlog"]["homePage"], "Mídia Social ou Website ou Blog");
        break;

      case "outraProducaoArtisticaCultural":
        $tipo_de_obra_nome = "Outra produção Artística Cultural";
        $campos_dadosBasicosDoTrabalho = ["natureza", "titulo", "ano", "pais", "idioma", "meioDeDivulgacao", "homePage", "flagRelevancia", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["instituicaoPromotoraDoEvento", "localDoEvento", "cidade"];
        $dadosBasicosNomeCampo = "dadosBasicosDeOutraProducaoArtisticaCultural";
        $detalhamentoNomeCampo = "detalhamentoDeOutraProducaoArtisticaCultural";
        $campos_sha256 = ["natureza", "titulo", "homePage"];
        $resultado_comparador_local = compararRegistros::lattesMidiaSocial(str_replace('"', '', $obra["dadosBasicosDeOutraProducaoArtisticaCultural"]["titulo"]), $obra["dadosBasicosDeOutraProducaoArtisticaCultural"]["homePage"], "Outra produção Artística Cultural");
        $doc_obra_array["doc"]["informacoesAdicionais"]["descricaoInformacoesAdicionais"] = str_replace('"', '', $obra["informacoesAdicionais"]["descricaoInformacoesAdicionais"]);
        break;

      case "artesVisuais":
        $tipo_de_obra_nome = "Artes visuais";
        $campos_dadosBasicosDoTrabalho = ["natureza", "titulo", "ano", "pais", "idioma", "meioDeDivulgacao", "homePage", "flagRelevancia", "flagDivulgacaoCientifica"];
        $campos_detalhamentoDoTrabalho = ["instituicaoPromotoraDoEvento", "localDoEvento", "cidade"];
        $dadosBasicosNomeCampo = "dadosBasicosDeArtesVisuais";
        $detalhamentoNomeCampo = "detalhamentoDeArtesVisuais";
        $campos_sha256 = ["natureza", "titulo", "homePage"];
        print_r(stripslashes($obra["dadosBasicosDeArtesVisuais"]["titulo"]));
        $resultado_comparador_local = compararRegistros::lattesMidiaSocial(stripslashes(str_replace('"', '', $obra["dadosBasicosDeArtesVisuais"]["titulo"])), $obra["dadosBasicosDeArtesVisuais"]["homePage"], "Artes visuais");
        $doc_obra_array["doc"]["informacoesAdicionais"]["descricaoInformacoesAdicionais"] = str_replace('"', '', $obra["informacoesAdicionais"]["descricaoInformacoesAdicionais"]);
        break;
    }

    $doc_obra_array["doc"]["tipo"] = $tipo_de_obra_nome;
    $doc_obra_array["doc"]["source"] = "Base Lattes";
    $doc_obra_array["doc"]["lattes_ids"][] = $id_lattes;
    $doc_obra_array["doc"]["tag"][] = $tag;
    $doc_obra_array["doc"]["unidade"][] = $unidade;
    $doc_obra_array["doc"]["numfuncional"] = $numfuncional;

    $titulos_array = ["tituloDoTrabalho", "tituloDoArtigo", "tituloDoLivro", "tituloDoCapituloDoLivro", "tituloDoTexto"];
    $ano_array = ["anoDoTrabalho", "anoDoArtigo", "anoDoTexto"];
    foreach ($campos_dadosBasicosDoTrabalho as $dados_basicos) {
      if (isset($obra[$dadosBasicosNomeCampo][$dados_basicos])) {
        $doc_obra_array["doc"][$dados_basicos] = $obra[$dadosBasicosNomeCampo][$dados_basicos];
      }
      if (in_array($dados_basicos, $titulos_array)) {
        $doc_obra_array["doc"]["titulo"] = $obra[$dadosBasicosNomeCampo][$dados_basicos];
      }
      if (in_array($dados_basicos, $ano_array)) {
        $doc_obra_array["doc"]["ano"] = $obra[$dadosBasicosNomeCampo][$dados_basicos];
      }
    }

    foreach ($campos_detalhamentoDoTrabalho as $detalhamento) {
      if (isset($obra[$detalhamentoNomeCampo][$detalhamento])) {
        $doc_obra_array["doc"][$tipo_de_obra][$detalhamento] = $obra[$detalhamentoNomeCampo][$detalhamento];
      }
    }

    if (isset($obra["autores"])) {
      $array_result = self::processaAutoresLattes($obra["autores"]);
      $doc_obra_array = array_merge_recursive($doc_obra_array, $array_result);
    }


    if (isset($obra["palavrasChave"])) {
      $array_result = self::processaPalavrasChaveLattes($obra["palavrasChave"]);
      $doc_obra_array = array_merge_recursive($doc_obra_array, $array_result);
    }

    if (isset($obra["areasDoConhecimento"])) {
      $array_result = self::processaAreaDoConhecimentoLattes($obra["areasDoConhecimento"]);
      $doc_obra_array = array_merge_recursive($doc_obra_array, $array_result);
    }

    // Constroi sha256
    $sha256 = self::constroi_sha256($obra, $campos_sha256, $dadosBasicosNomeCampo, $detalhamentoNomeCampo);

    // Comparador Local
    $i = 0;
    if (!empty($resultado_comparador_local["hits"]["hits"])) {
      foreach ($resultado_comparador_local["hits"]["hits"] as $result1) {
        if ($result1["_id"] != $sha256) {
          if (!empty($result1["_id"])) {
            $doc_obra_array["doc"]["ids_match"][$i]["id_match"] = $result1["_id"];
          }
          if (isset($result1["_score"])) {
            $doc_obra_array["doc"]["ids_match"][$i]["nota"] = $result1["_score"];
          }
        }
        $i++;
      }
    }


    $doc_obra_array["doc"]["bdpi"] = DadosExternos::query_bdpi_index($doc_obra_array["doc"]["titulo"], $doc_obra_array["doc"]["ano"]);
    $doc_obra_array["doc"]["concluido"] = "Não";
    $doc_obra_array["doc_as_upsert"] = true;

    // Retorna resultado

    //$body = json_encode($doc_obra_array, JSON_UNESCAPED_UNICODE);
    $body = $doc_obra_array;
    print_r($body);

    return compact('body', 'sha256');
  }

  static function processaAutoresLattes($autores_array)
  {
    $i = 0;
    foreach ($autores_array as $autor) {
      $autor_campos = ["nomeCompletoDoAutor", "nomeParaCitacao", "ordemDeAutoria", "nroIdCnpq"];
      foreach ($autor_campos as $campos) {
        if (isset($autor[$campos])) {
          $array_result["doc"]["autores"][$i][$campos] = $autor[$campos];
        }
        if (isset($autor["nroIdCnpq"])) {
          $array_result["doc"]["lattes_ids"][] = $autor["nroIdCnpq"];
        }
      }
      $i++;
    }
    return $array_result;
  }

  static function processaPalavrasChaveLattes($palavras_chave)
  {
    foreach (range(1, 6) as $number) {
      if (isset($palavras_chave["palavraChave$number"])) {
        $array_result["doc"]["palavras_chave"][] = $palavras_chave["palavraChave$number"];
      }
    }
    return $array_result;
  }

  static function processaAreaDoConhecimentoLattes($areas_do_conhecimento)
  {
    $campos = ["nomeGrandeAreaDoConhecimento", "nomeDaAreaDoConhecimento", "nomeDaSubAreaDoConhecimento", "nomeDaEspecialidade"];
    $i = 0;
    foreach ($areas_do_conhecimento as $ac) {
      foreach ($campos as $c) {
        if (isset($ac[$c])) {
          $array_result["doc"]["area_do_conhecimento"][$i][$c] = $ac[$c];
        }
      }
      $i++;
    }
    return $array_result;
  }

  static function constroi_sha256($obra, $campos_sha256, $dadosBasicosNomeCampo, $detalhamentoNomeCampo)
  {
    $sha_array = [];

    foreach ($campos_sha256 as $campos) {
      if (isset($obra[$dadosBasicosNomeCampo][$campos])) {
        $sha_array[] = $obra[$dadosBasicosNomeCampo][$campos];
      } elseif (isset($obra[$detalhamentoNomeCampo][$campos])) {
        $sha_array[] = $obra[$detalhamentoNomeCampo][$campos];
      }
    }
    $sha256 = hash('sha256', '' . implode("", $sha_array) . '');
    return $sha256;
  }
}

/**
 * Classe que processa dados obtidos por meio de servidores z39.50
 */
class z3950
{

  static function parse_usmarc_string($record)
  {
    $ret = array();
    // there was a case where angle brackets interfered
    $record = str_replace(array("<", ">"), array("", ""), $record);
    //$record = utf8_decode($record);
    // split the returned fields at their separation character (newline)
    $record = explode("\n", $record);
    //examine each line for wanted information (see USMARC spec for details)
    foreach ($record as $category) {
      // subfield indicators are preceded by a $ sign
      $parts = explode("$", $category);
      // remove leading and trailing spaces
      array_walk($parts, "z3950::custom_trim");
      // the first value holds the field id,
      // depending on the desired info a certain subfield value is retrieved
      switch (substr($parts[0], 0, 3)) {
        case "008":
          $ret["language"] = substr($parts[0], 39, 3);
          break;
        case "020":
          $ret["isbn"] = z3950::get_subfield_value($parts, "a");
          break;
        case "022":
          $ret["issn"] = z3950::get_subfield_value($parts, "a");
          break;
        case "100":
          $ret["author"] = z3950::get_subfield_value($parts, "a");
          break;
        case "245":
          $ret["title"] = z3950::get_subfield_value($parts, "a");
          $ret["subtitle"] = z3950::get_subfield_value($parts, "b");
          break;
        case "250":
          $ret["edition"] = z3950::get_subfield_value($parts, "a");
          break;
        case "260":
          $ret["pub_date"] = z3950::get_subfield_value($parts, "c");
          $ret["pub_place"] = z3950::get_subfield_value($parts, "a");
          $ret["publisher"] = z3950::get_subfield_value($parts, "b");
          break;
        case "300":
          $ret["extent"] = z3950::get_subfield_value($parts, "a");
          $ext_b = z3950::get_subfield_value($parts, "b");
          $ret["extent"] .= ($ext_b != "") ? (" : " . $ext_b) : "";
          break;
        case "490":
          $ret["series"] = z3950::get_subfield_value($parts, "a");
          break;
        case "502":
          $ret["diss_note"] = z3950::get_subfield_value($parts, "a");
          break;
        case "700":
          $ret["editor"] = z3950::get_subfield_value($parts, "a");
          break;
      }
    }
    return $ret;
  }

  // fetches the value of a certain subfield given its label
  static function get_subfield_value($parts, $subfield_label)
  {
    $ret = "";
    foreach ($parts as $subfield)
      if (substr($subfield, 0, 1) == $subfield_label)
        $ret = substr($subfield, 2);
    return $ret;
  }

  // wrapper function for trim to pass it to array_walk
  static function custom_trim(&$value, &$key)
  {
    $value = trim($value);
  }

  static function query_z3950($query, $host, $host_name, $type)
  {
    if ($type == "isbn") {
      $query_data = '@attr 1=7 ' . $query . '';
    } elseif ($type == "title") {
      if ((!empty($query[0])) && (!empty($query[1])) && (!empty($query[2]))) {
        $query_data = '@attrset gils @and @attr 1=4 @attr 2=3 ' . $query[0] . ' @attr 1=1003 @attr 2=3 ' . $query[1] . ' @attr 1=31 @attr 2=3 ' . $query[2] . '';
      } elseif ((!empty($query[0])) && (!empty($query[1]))) {
        $query_data = '@attrset gils @and @attr 1=4 @attr 2=3 ' . $query[0] . ' @attr 1=1003 @attr 2=3 ' . $query[1] . '';
      } elseif ((!empty($query[0])) && (!empty($query[2]))) {
        $query_data = '@attrset gils @and @attr 1=4 @attr 2=3 ' . $query[0] . ' @attr 1=31 @attr 2=3 ' . $query[2] . '';
      } elseif ((!empty($query[1])) && (!empty($query[2])) && (empty($query[0]))) {
        $query_data = '@attrset gils @and @attr 1=1003 @attr 2=3 ' . $query[1] . ' @attr 1=31 @attr 2=3 ' . $query[2] . '';
      } else {
        $query_data = '@attrset gils @attr 1=4 ' . $query[0] . '';
      }
      //print_r($query_data);


    } elseif ($type == "sysno") {
      $query_data = '@attr 1=12 ' . $query . '';
    }

    $id = yaz_connect($host);
    yaz_range($id, 1, 10);
    yaz_syntax($id, "usmarc");
    yaz_search($id, "rpn", $query_data);
    yaz_wait();

    $error = yaz_error($id);

    if (!empty($error)) {
      echo "$host_name error: $error";
    } else {
      $hits = yaz_hits($id);
      echo "<p>$host_name - $hits resultado(s) </p>";

      if ($hits >= 1) {




        for ($p = 1; $p <= $hits; $p++) {

          echo '<ul class="uk-subnav uk-subnav-pill" uk-switcher>
                    <li><a href="#">Resumo</a></li>
                    <li><a href="#">Registro completo</a></li>
                    </ul>         
                    <ul class="uk-switcher uk-margin">';

          echo '<li><table class="uk-table">    
                        <thead>
                            <tr>
                                <th>Fonte</th>    
                                <th>ISBN</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Editora</th>
                                <th>Local</th>
                                <th>Ano</th>
                                <th>Edição</th>
                                <th>Descrição física</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody>    
                    ';



          $rec = yaz_record($id, $p, "string");
          //print_r($rec);
          $result_record = z3950::parse_usmarc_string($rec);
          //print_r($result_record);
          $rec_download = yaz_record($id, $p, "raw");
          $rec_download = str_replace('"', '', $rec_download);
          echo '<tr>';
          echo '<th>' . $host_name . '</th>';
          if (isset($result_record["isbn"])) {
            echo '<td>' . $result_record["isbn"] . '</td>';
          } else {
            echo '<td></td>';
          }
          echo '<td>' . $result_record["title"] . '</td>';

          if (!empty($result_record["author"])) {
            echo '<td>' . $result_record["author"] . '</td>';
          } else {
            echo '<td>Sem autor cadastrado</td>';
          }

          if (!empty($result_record["publisher"])) {
            echo '<td>' . $result_record["publisher"] . '</td>';
          } else {
            echo '<td>Sem editora cadastrada</td>';
          }

          if (!empty($result_record["pub_place"])) {
            echo '<td>' . $result_record["pub_place"] . '</td>';
          } else {
            echo '<td>Sem local</td>';
          }

          if (!empty($result_record["pub_date"])) {
            echo '<td>' . $result_record["pub_date"] . '</td>';
          } else {
            echo '<td>Sem data</td>';
          }

          if (isset($result_record["edition"])) {
            echo '<td>' . $result_record["edition"] . '</td>';
          } else {
            echo '<td></td>';
          }

          if (isset($result_record["extent"])) {
            echo '<td>' . $result_record["extent"] . '</td>';
          } else {
            echo '<td></td>';
          }
          echo '<td><button onclick="SaveAsFile(\'' . addslashes($rec_download) . '\',\'record.mrc\',\'text/plain;charset=CP1252\')">Baixar MARC</button></td>';
          echo '</tr>';
          echo '</tbody>
                    </table></li>';

          echo '<li>' . nl2br($rec) . '</li>';

          echo '</ul>';

          flush();
        }
      }
    }
  }
}

class Testadores
{
  public static function existe($variavel)
  {
    $resultado_teste = ((isset($variavel) && $variavel) ? $variavel : '');
    return $resultado_teste;
  }
  public static function testDOI($DOI)
  {
    $pattern = '/^10.\d{4,9}\/[-._;()\:A-Z0-9]+$/i';
    $result_test_doi = preg_match($pattern, $DOI);
    return $result_test_doi;
  }
}


/**
 * Exporters
 *
 * @category Class
 * @package  Exporters
 * @author   Tiago Rodrigo Marçal Murakami <tiago.murakami@dt.sibi.usp.br>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://github.com/sibiusp/nav_elastic 
 */
class Exporters
{

  static function RIS($cursor)
  {

    $record = [];
    switch ($cursor["_source"]["type"]) {
      case "ARTIGO DE PERIODICO":
        $record[] = "TY  - JOUR";
        break;
      case "PARTE DE MONOGRAFIA/LIVRO":
        $record[] = "TY  - CHAP";
        break;
      case "TRABALHO DE EVENTO-RESUMO":
        $record[] = "TY  - CPAPER";
        break;
      case "TEXTO NA WEB":
        $record[] = "TY  - ICOMM";
        break;
    }

    $record[] = "TI  - " . $cursor["_source"]['name'] . "";

    if (!empty($cursor["_source"]['datePublished'])) {
      $record[] = "PY  - " . $cursor["_source"]['datePublished'] . "";
    }

    foreach ($cursor["_source"]['author'] as $autores) {
      $record[] = "AU  - " . $autores["person"]["name"] . "";
    }

    if (!empty($cursor["_source"]["isPartOf"]["name"])) {
      $record[] = "T2  - " . $cursor["_source"]["isPartOf"]["name"] . "";
    }

    if (!empty($cursor["_source"]['isPartOf']['issn'])) {
      $record[] = "SN  - " . $cursor["_source"]['isPartOf']['issn'][0] . "";
    }

    if (!empty($cursor["_source"]["doi"])) {
      $record[] = "DO  - " . $cursor["_source"]["doi"] . "";
    }

    if (!empty($cursor["_source"]["url"])) {
      $record[] = "UR  - " . $cursor["_source"]["url"][0] . "";
    }

    if (!empty($cursor["_source"]["publisher"]["organization"]["location"])) {
      $record[] = "PP  - " . $cursor["_source"]["publisher"]["organization"]["location"] . "";
    }

    if (!empty($cursor["_source"]["publisher"]["organization"]["name"])) {
      $record[] = "PB  - " . $cursor["_source"]["publisher"]["organization"]["name"] . "";
    }

    if (!empty($cursor["_source"]["isPartOf"]["USP"]["dados_do_periodico"])) {
      $periodicos_array = explode(",", $cursor["_source"]["isPartOf"]["USP"]["dados_do_periodico"]);
      foreach ($periodicos_array as $periodicos_array_new) {
        if (strpos($periodicos_array_new, 'v.') !== false) {
          $record[] = "VL  - " . trim(str_replace("v.", "", $periodicos_array_new)) . "";
        } elseif (strpos($periodicos_array_new, 'n.') !== false) {
          $record[] = "IS  - " . str_replace("n.", "", trim(str_replace("n.", "", $periodicos_array_new))) . "";
        } elseif (strpos($periodicos_array_new, 'p.') !== false) {
          $record[] = "SP  - " . str_replace("p.", "", trim(str_replace("p.", "", $periodicos_array_new))) . "";
        }
      }
    }

    $record[] = "ER  - ";
    $record[] = "";
    $record[] = "";

    $record_blob = implode("\\n", $record);

    return $record_blob;
  }

  static function alephseq($r)
  {

    $author_number = count($r["_source"]['author']);

    $record = [];
    $record[] = "000000001 FMT   L BK";
    $record[] = "000000001 LDR   L ^^^^^nab^^22^^^^^Ia^4500";
    $record[] = '000000001 BAS   L $$a04';
    $record[] = "000000001 008   L ^^^^^^s^^^^^^^^^^^^^^^^^^^^^^000^0^^^^^d";
    if (isset($r["_source"]['doi'])) {
      $record[] = '000000001 0247  L $$a' . $r["_source"]["doi"] . '$$2DOI';
    } else {
      $record[] = '000000001 0247  L $$a$$2DOI';
    }
    $record[] = '000000001 040   L $$aUSP/SIBI';
    $record[] = '000000001 0410  L $$a';
    $record[] = '000000001 044   L $$a';
    if ($author_number > 1) {
      if (isset($r["_source"]['author'][0]["nomeParaCitacao"])) {
        $record[] = '000000001 1001  L $$a' . $r["_source"]['author'][0]["nomeParaCitacao"] . '$$d$$1$$4$$5$$7$$8$$9';
      } else {
        $record[] = '000000001 1001  L $$a' . $r["_source"]['author'][0]["person"]["name"] . '$$d$$1$$4$$5$$7$$8$$9';
      }
      for ($i = 1; $i < $author_number; $i++) {
        if (isset($r["_source"]['author'][$i]["nomeParaCitacao"])) {
          $record[] = '000000001 7001  L $$a' . $r["_source"]['author'][$i]["nomeParaCitacao"] . '$$d$$1$$4$$5$$7$$8$$9';
        } else {
          $record[] = '000000001 7001  L $$a' . $r["_source"]['author'][$i]["person"]["name"] . '$$d$$1$$4$$5$$7$$8$$9';
        }
      }
    } else {
      if (isset($r["_source"]['author'][0]["nomeParaCitacao"])) {
        $record[] = '000000001 1001  L $$a' . $r["_source"]['author'][0]["nomeParaCitacao"] . '$$d$$1$$4$$5$$7$$8$$9';
      } else {
        $record[] = '000000001 1001  L $$a' . $r["_source"]['author'][0]["person"]["name"] . '$$d$$1$$4$$5$$7$$8$$9';
      }
    }
    $record[] = '000000001 24510 L $$a' . $r["_source"]["name"] . '';
    if (isset($r["_source"]["trabalhoEmEventos"])) {
      $record[] = '000000001 260   L $$a' . ((isset($r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"]) && $r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"]) ? $r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"] : '') . '$$b' . ((isset($r["_source"]["trabalhoEmEventos"]["nomeDaEditora"]) && $r["_source"]["trabalhoEmEventos"]["nomeDaEditora"]) ? $r["_source"]["trabalhoEmEventos"]["nomeDaEditora"] : '') . '$$c' . $r["_source"]["datePublished"] . '';
    } else {
      $record[] = '000000001 260   L $$a$$b' . ((isset($r["_source"]["publisher"]["organization"]["name"]) ? $r["_source"]["publisher"]["organization"]["name"] : '')) . '$$c' . $r["_source"]["datePublished"] . '';
    }
    $record[] = '000000001 300   L $$ap. ' . ((isset($r["_source"]["pageStart"]) ? $r["_source"]["pageStart"] : "")) . '-' . ((isset($r["_source"]["pageEnd"]) ? $r["_source"]["pageEnd"] : "")) . '';

    if (isset($r["_source"]['doi'])) {
      $record[] = '000000001 500   L $$aDisponível em: <https://doi.org/' . $r["_source"]["doi"] . '>. Acesso em: ';
    } else {
      $record[] = '000000001 500   L $$a';
    }

    if (isset($r["_source"]["artigoPublicado"])) {
      $record[] = '000000001 5101  L $$aIndexado no:';
    }

    if (isset($r["_source"]["sponsor"]["funder"])) {
      foreach ($r["_source"]["sponsor"]["funder"] as $funder) {
        if (count($funder["award"]) > 0) {
          $funder_string = '$$f' . implode("\$\$f", $funder["award"]) . '';
        } else {
          $funder_string = "";
        }
        $record[] = '000000001 536   L $$a' . $funder["name"] . '' . $funder_string . '';
      }
    }

    $record[] = '000000001 650 7 L $$a';
    $record[] = '000000001 650 7 L $$a';
    $record[] = '000000001 650 7 L $$a';
    $record[] = '000000001 650 7 L $$a';

    if (isset($r["_source"]["trabalhoEmEventos"])) {
      if (empty($r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"])) {
        $r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"] = "Não informado";
      }

      $record[] = '000000001 7112  L $$a' . $r["_source"]["trabalhoEmEventos"]["nomeDoEvento"] . '$$d(' . ((isset($r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"]) && $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"]) ? $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"] : '') . '$$c' . $r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"] . ')';

      $record[] = '000000001 7730  L $$t' . ((isset($r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"]) && $r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"]) ? $r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"] : '') . '$$x' . ((isset($r["_source"]["trabalhoEmEventos"]["isbn"]) && $r["_source"]["trabalhoEmEventos"]["isbn"]) ? $r["_source"]["trabalhoEmEventos"]["isbn"] : '') . '$$hv. , n. , p.' . ((isset($r["_source"]["trabalhoEmEventos"]["paginaInicial"]) && $r["_source"]["trabalhoEmEventos"]["paginaInicial"]) ? $r["_source"]["trabalhoEmEventos"]["paginaInicial"] : '') . '-' . ((isset($r["_source"]["trabalhoEmEventos"]["paginaFinal"]) && $r["_source"]["trabalhoEmEventos"]["paginaFinal"]) ? $r["_source"]["trabalhoEmEventos"]["paginaFinal"] : '') . ', ' . ((isset($r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"]) && $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"]) ? $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"] : '') . '';
    }

    if (isset($r["_source"]["isPartOf"])) {
      $record[] = '000000001 7730  L $$t' . $r["_source"]["isPartOf"]["name"] . '$$x' . ((isset($r["_source"]["isPartOf"]["issn"]) ? $r["_source"]["isPartOf"]["issn"] : '')) . '$$hv.' . ((isset($r["_source"]["volume"]) ? $r["_source"]["volume"] : '')) . ', n. ' . ((isset($r["_source"]["serie"]) ? $r["_source"]["serie"] : '')) . ', p.' . ((isset($r["_source"]["pageStart"]) ? $r["_source"]["pageStart"] : '')) . '-' . ((isset($r["_source"]["pageEnd"]) ? $r["_source"]["pageEnd"] : '')) . ', ' . $r["_source"]["datePublished"] . '';
    }


    if (isset($r["_source"]['doi'])) {
      $record[] = '000000001 8564  L $$zClicar sobre o botão para acesso ao texto completo$$uhttps://doi.org/' . $r["_source"]["doi"] . '$$3DOI';
    } else {
      $record[] = '000000001 8564  L $$zClicar sobre o botão para acesso ao texto completo$$u$$3DOI';
    }

    if (isset($r["_source"]["trabalhoEmEventos"])) {
      $record[] = '000000001 945   L $$aP$$bTRABALHO DE EVENTO$$c10$$j' . $r["_source"]["datePublished"] . '$$l';
    }
    if (isset($r["_source"]["isPartOf"])) {
      $record[] = '000000001 945   L $$aP$$bARTIGO DE PERIODICO$$c01$$j' . $r["_source"]["datePublished"] . '$$l';
    }
    $record[] = '000000001 946   L $$a';

    //sort($record);

    $record_blob = implode("\\n", $record);

    return $record_blob;
  }

  static function bibtex($cursor)
  {

    $record = [];

    if (!empty($cursor["_source"]['name'])) {
      $recordContent[] = 'title   = {' . $cursor["_source"]['name'] . '}';
    }

    if (!empty($cursor["_source"]['author'])) {
      $authorsArray = [];
      foreach ($cursor["_source"]['author'] as $author) {
        $authorsArray[] = $author["person"]["name"];
      }
      $recordContent[] = 'author = {' . implode(" and ", $authorsArray) . '}';
    }

    if (!empty($cursor["_source"]['datePublished'])) {
      $recordContent[] = 'year = {' . $cursor["_source"]['datePublished'] . '}';
    }

    if (!empty($cursor["_source"]['doi'])) {
      $recordContent[] = 'doi = {' . $cursor["_source"]['doi'] . '}';
    }

    if (!empty($cursor["_source"]['publisher']['organization']['name'])) {
      $recordContent[] = 'publisher = {' . $cursor["_source"]['publisher']['organization']['name'] . '}';
    }

    if (!empty($cursor["_source"]["releasedEvent"])) {
      $recordContent[] = 'booktitle   = {' . $cursor["_source"]["releasedEvent"] . '}';
    } else {
      if (!empty($cursor["_source"]["isPartOf"]["name"])) {
        $recordContent[] = 'journal   = {' . $cursor["_source"]["isPartOf"]["name"] . '}';
      }
    }


    $sha256 = hash('sha256', '' . implode("", $recordContent) . '');

    switch ($cursor["_source"]["tipo"]) {
      case "Artigo publicado":
        $record[] = '@article{article' . substr($sha256, 0, 8) . ',';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
        break;
      case "Livro publicado ou organizado":
        $record[] = '@book{book' . substr($sha256, 0, 8) . ',';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
        break;
      case "Capítulo de livro publicado":
        $record[] = '@inbook{inbook' . substr($sha256, 0, 8) . ',';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
        break;
      case "Trabalhos em eventos":
        $record[] = '@inproceedings{inproceedings' . substr($sha256, 0, 8) . ',';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
        break;
      case "TRABALHO DE EVENTO-RESUMO":
        $record[] = '@inproceedings{inproceedings' . substr($sha256, 0, 8) . ',';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
        break;
      case "TESE":
        $record[] = '@mastersthesis{mastersthesis' . substr($sha256, 0, 8) . ',';
        $recordContent[] = 'school = {Universidade de São Paulo}';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
        break;
      default:
        $record[] = '@misc{misc' . substr($sha256, 0, 8) . ',';
        $record[] = implode(",\\n", $recordContent);
        $record[] = '}';
    }


    $record_blob = implode("\\n", $record);

    return $record_blob;
  }

  public static function citation($record, $citation_format)
  {
    /* Citeproc-PHP*/
    include_once 'inc/citeproc-php/CiteProc.php';
    $csl_abnt = file_get_contents('inc/citeproc-php/style/abnt.csl');
    $csl_apa = file_get_contents('inc/citeproc-php/style/apa.csl');
    $csl_nlm = file_get_contents('inc/citeproc-php/style/nlm.csl');
    $csl_vancouver = file_get_contents('inc/citeproc-php/style/vancouver.csl');
    $lang = "br";
    $citeproc_abnt = new citeproc($csl_abnt, $lang);
    $citeproc_apa = new citeproc($csl_apa, $lang);
    $citeproc_nlm = new citeproc($csl_nlm, $lang);
    $citeproc_vancouver = new citeproc($csl_nlm, $lang);
    $mode = "bibliography";

    if ($citation_format == "ABNT") {
      $data = citation::citationQuery($record["_source"]);
      return $citeproc_abnt->render($data[0], $mode);
    }

    if ($citation_format == "APA") {
      $data = citation::citationQuery($record["_source"]);
      return $citeproc_apa->render($data, $mode);
    }

    if ($citation_format == "NLM") {
      $data = citation::citationQuery($record["_source"]);
      return $citeproc_nlm->render($data, $mode);
    }

    if ($citation_format == "Vancouver") {
      $data = citation::citationQuery($record["_source"]);
      return $citeproc_vancouver->render($data, $mode);
    }
  }
}

class ActiveFilters
{
  public static function Filters($get, $url_base)
  {
    $activeFilters[] = '<div class="alert alert-info mt-3" role="alert">';
    $activeFilters[] = 'Filtros ativos:&nbsp;&nbsp;';
    $activeFilters[] = '<ul class="list-inline">';

    if (!empty($get["search"])) {
      $getUnsetSearch = $get;
      unset($getUnsetSearch["search"]);
      unset($getUnsetSearch["page"]);
      $url_push = $url_base . 'result.php?' . http_build_query($getUnsetSearch);
      $activeFilters[] = '<li class="list-inline-item"><a class="text-danger" href="' . $url_push . '" title="Remover filtro">' . $get["search"] . ' <span aria-hidden="true">&times;</span></a></li>';
    }

    if (!empty($get["filter"])) {
      foreach ($get["filter"] as $filters) {
        if (!empty($filters)) {
          $filters_array = $get;
          $pos = array_search($filters, $filters_array["filter"]);
          unset($filters_array["filter"][$pos]);
          $filters_array["filter"] = array_filter($filters_array["filter"]);
          unset($filters_array["page"]);
          $url_push = $url_base . 'result.php?' . http_build_query($filters_array);
          $activeFilters[] = '<li class="list-inline-item"><a class="text-success" href="' . $url_push . '" title="Remover filtro">' . $filters . ' <span aria-hidden="true">&times;</span></a></li>';
        }
      }
    }

    if (!empty($get["notFilter"])) {
      $notFilterText = sizeof($get["notFilter"]) > 1 ? 'Removidos' : 'Removido';
      $activeFilters[] = '<span class="not-filter"> ' . $notFilterText . ': </span>';
      foreach ($get["notFilter"] as $notFilters) {
        $notFiltersArray[] = $notFilters;
        $name_field = explode(":", $notFilters);
        $notFilters = str_replace($name_field[0] . ":", "", $notFilters);
        $diff["notFilter"] = array_diff($get["notFilter"], $notFiltersArray);
        $url_push = $_SERVER['SERVER_NAME'] . $_SERVER["SCRIPT_NAME"] . '?' . http_build_query($diff);
        $activeFilters[] = '<li class="list-inline-item"><a href="http://' . $url_push . '" title="Remover filtro">' . $notFilters . ' <span aria-hidden="true">&times;</span></span></a></li>';
        unset($notFiltersArray);
      }
    }

    $activeFilters[] = '</ul></div>';

    return $activeFilters;
  }
}

class AuthorFacets
{
  public function authorfacet($fileName, $field, $size, $field_name, $sort, $sort_type, $get_search, $alternative_index = null, $collapsed = true)
  {
    global $url_base;

    if (isset($get_search["page"])) {
      unset($get_search["page"]);
    }

    $query = $this->query;
    $lattesID = $query['query']['bool']['filter'][1]['term']['lattesID.keyword'];
    unset($query['query']['bool']['filter']);
    $query['query']['bool']['filter']['term']['vinculo.lattes_id.keyword'] = $lattesID;

    $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
    if (!empty($_SESSION['oauthuserdata'])) {
      $query["aggs"]["counts"]["terms"]["missing"] = "Não preenchido";
    }
    if (isset($sort)) {
      $query["aggs"]["counts"]["terms"]["order"][$sort_type] = $sort;
    }
    $query["aggs"]["counts"]["terms"]["size"] = $size;

    $response = Elasticsearch::search(null, 0, $query, $alternative_index);

    $result_count = count($response["aggregations"]["counts"]["buckets"]);

    // echo "<br/><br/>";
    // print("<pre>" . print_r($query, true) . "</pre>");
    // echo "<br/><br/>";

    $i = 0;
    foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
      $response_array[$i]["category"] = $facets['key'];
      $response_array[$i]["amount"] = $facets['doc_count'];
      $i++;
    }

    return json_encode($response_array);
  }
}

/**
 * Elasticsearch Class
 */
class Elasticsearch
{

  /**
   * Executa o commando get no Elasticsearch
   *
   * @param string   $_id               ID do documento.
   * @param string[] $fields            Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
   * @param string   $alternative_index Caso use indice alternativo
   *
   */
  public static function get($_id, $fields, $alternative_index = "")
  {
    global $index;
    global $client;
    $params = [];

    if (strlen($alternative_index) > 0) {
      $params["index"] = $alternative_index;
    } else {
      $params["index"] = $index;
    }

    $params["id"] = $_id;
    $params["_source"] = $fields;

    $response = $client->get($params);
    return $response;
  }

  /**
   * Executa o commando search no Elasticsearch
   *
   * @param string[] $fields Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
   * @param int      $size   Quantidade de registros nas respostas
   * @param resource $body   Arquivo JSON com os parâmetros das consultas no Elasticsearch
   *
   */
  public static function search($fields, $size, $body, $alternative_index = "")
  {
    global $index;
    global $client;
    $params = [];

    if ($alternative_index == "") {
      $params["index"] = $index;
    } else {
      $params["index"] = $alternative_index;
    }

    $params["_source"] = $fields;
    $params["size"] = $size;
    $params["body"] = $body;

    $response = $client->search($params);
    return $response;
  }

  /**
   * Executa o commando update no Elasticsearch
   *
   * @param string   $_id  ID do documento
   * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch
   *
   */
  public static function update($_id, $body, $alternative_index = "")
  {
    global $index;
    global $client;
    $params = [];

    if (strlen($alternative_index) > 0) {
      $params["index"] = $alternative_index;
    } else {
      $params["index"] = $index;
    }

    $params["id"] = $_id;
    $params["body"] = $body;

    $response = $client->update($params);
    return $response;
  }

  /**
   * Executa o commando delete no Elasticsearch
   *
   * @param string $_id  ID do documento
   *
   */
  public static function delete($_id, $alternative_index = "")
  {
    global $index;
    global $client;
    $params = [];

    if (strlen($alternative_index) > 0) {
      $params["index"] = $alternative_index;
    } else {
      $params["index"] = $index;
    }

    $params["id"] = $_id;
    $params["client"]["ignore"] = 404;

    $response = $client->delete($params);
    return $response;
  }

  /**
   * Executa o commando delete_by_query no Elasticsearch
   *
   * @param resource $body              Arquivo JSON com os parâmetros das consultas no Elasticsearch
   * @param resource $alternative_index Se tiver indice alternativo
   * 
   * @return array Resposta do comando
   */
  public static function deleteByQuery($body, $alternative_index = "")
  {
    global $index;
    global $client;
    $params = [];

    if (strlen($alternative_index) > 0) {
      $params["index"] = $alternative_index;
    } else {
      $params["index"] = $index;
    }

    $params["body"] = $body;

    $response = $client->deleteByQuery($params);
    return $response;
  }

  /**
   * Executa o commando update no Elasticsearch e retorna uma resposta em html
   *
   * @param string   $_id  ID do documento
   * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch
   *
   */
  static function storeRecord($_id, $body)
  {
    $response = Elasticsearch::update($_id, $body);
    echo '<br/>Resultado: ' . ($response["_id"]) . ', ' . ($response["result"]) . ', ' . ($response["_shards"]['successful']) . '<br/>';
  }

  /**
   * Cria o indice
   *
   * @param string   $indexName  Nome do indice
   *
   */
  static function createIndex($indexName, $client)
  {
    $createIndexParams = [
      'index' => $indexName,
      'body' => [
        'settings' => [
          'number_of_shards' => 1,
          'number_of_replicas' => 0,
          'analysis' => [
            'filter' => [
              'portuguese_stop' => [
                'type' => 'stop',
                'stopwords' => '_portuguese_'
              ],
              'my_ascii_folding' => [
                'type' => 'asciifolding',
                'preserve_original' => true
              ],
              'portuguese_stemmer' => [
                'type' => 'stemmer',
                'language' => 'light_portuguese'
              ]
            ],
            'analyzer' => [
              'rebuilt_portuguese' => [
                'tokenizer' => 'standard',
                'filter' => [
                  'lowercase',
                  'my_ascii_folding',
                  'portuguese_stop',
                  'portuguese_stemmer'
                  ]
                ],
                'case_insensitive_analyzer' => [
                  'tokenizer' => 'keyword',
                  'filter' => ['lowercase']
                ]
            ],
            'normalizer' => [
              'case_insensitive' => [
                'filter' => 'lowercase'
              ]
            ]
          ]
        ]
      ]
    ];
    $responseCreateIndex = $client->indices()->create($createIndexParams);
  }


  /**
   * Cria o mapeamento
   *
   * @param string   $indexName  Nome do indice
   *
   */
  static function mappingsIndex($indexName, $client, $mappings = null)
  {
    if (isset($mappings)) {
      $mappingsParams = $mappings;
    } else {
      $mappingsParams = [
        'index' => $indexName,
        'body' => [
          'properties' => [
            'name' => [
              'type' => 'text',
              'analyzer' => 'portuguese',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'alternateName' => [
              'type' => 'text',
              'analyzer' => 'portuguese',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'nome_completo' => [
              'type' => 'text',
              'analyzer' => 'portuguese',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'author' => [
              'properties' => [
                'person' => [
                  'type' => 'nested',
                  'properties' => [
                    'name' => [
                      'type' => 'text',
                      'analyzer' => 'rebuilt_portuguese',
                      'fields' => [
                        'keyword' => [
                          'type' => 'keyword',
                          'ignore_above' => 256
                        ]
                      ]
                    ]
                  ]
                ],
                'organization' => [
                  'type' => 'nested',
                  'properties' => [
                    'name' => [
                      'type' => 'text',
                      'analyzer' => 'portuguese',
                      'fields' => [
                        'keyword' => [
                          'type' => 'keyword',
                          'ignore_above' => 256
                        ]
                      ]
                    ]
                  ]
                ]
              ]
            ],
            'source' => [
              'type' => 'text',
              'analyzer' => 'portuguese',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'about' => [
              'type' => 'text',
              'analyzer' => 'portuguese',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'citedby' => [
              'type' => 'integer'
            ],
            'description' => [
              'type' => 'text',
              'analyzer' => 'portuguese'
            ],
            'datePublished' => [
              'type' => 'text',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'doi' => [
              'type' => 'text',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'ignore_above' => 256
                ]
              ]
            ],
            'ExternalData' => [
              'type' => 'nested'
            ],
            'facebook' => [
              'properties' => [
                'facebook_total' => [
                  'type' => 'integer'
                ]
              ]
            ],
            'vinculo' => [
              'properties' => [
                'nome' => [
                  'type' => 'text',
                  'analyzer' => 'portuguese',
                  'fields' => [
                    'keyword' => [
                      'type' => 'keyword',
                      'ignore_above' => 256
                    ]
                  ]
                ]
              ]
            ]
          ]
        ]
      ];
    }
    // Update the index mapping
    $client->indices()->putMapping($mappingsParams);
  }
  static function mappingsIndexCV($indexName, $client, $mappings = null)
  {
    if (isset($mappings)) {
      $mappingsParams = $mappings;
    } else {
      $mappingsParams = [
        'index' => $indexName,
        'body' => [
          'properties' => [
            'nome_completo' => [
              'type' => 'text',
              'analyzer' => 'rebuilt_portuguese',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                  'normalizer' => 'case_insensitive',
                  'ignore_above' => 256
                ]
              ]
            ]
          ]
        ]
      ];
    }
    // Update the index mapping
    $client->indices()->putMapping($mappingsParams);
  }

}

class Requests
{

  static function getParser($get)
  {
    $query = [];

    /* Pagination */
    if (isset($get['page'])) {
      $page = $get['page'];
      unset($get['page']);
    } else {
      $page = 1;
    }

    /* Pagination variables */
    $limit = 20;
    $skip = ($page - 1) * $limit;
    $next = ($page + 1);
    $prev = ($page - 1);

    $i_filter = 0;
    if (!empty($get['filter'])) {
      foreach ($get['filter'] as $filter) {
        if (!empty($filter)) {
          $filter_array = explode(":", $filter);
          $filter_array_term = str_replace('"', "", (string) $filter_array[1]);
          $query["query"]["bool"]["filter"][$i_filter]["term"][(string) $filter_array[0] . ".keyword"] = $filter_array_term;
          $i_filter++;
        }
      }
    }

    if (!empty($get['notFilter'])) {
      $i_notFilter = 0;
      foreach ($get['notFilter'] as $notFilter) {
        $notFilterArray = explode(":", $notFilter);
        $notFilterArrayTerm = str_replace('"', "", (string) $notFilterArray[1]);
        $query["query"]["bool"]["must_not"][$i_notFilter]["term"][(string) $notFilterArray[0] . ".keyword"] = $notFilterArrayTerm;
        $i_notFilter++;
      }
    }

    if (!empty($get['search'])) {
      $cleanQuery = strip_tags($get['search']);
      $queryArray["query_string"]["query"] = str_replace('and', 'AND', $cleanQuery);
      $queryArray["query_string"]["fields"] = ["name", "alternateName", "author.person.name", "author.organization.name", "about", "source", "description", "vinculo.lattes_id", "vinculo.nome"];
    } else {
      $queryArray["query_string"]["query"] = "*";
    }

    if (!empty($get['initialYear']) || !empty($get['finalYear'])) {
      if (!empty($get['initialYear'])) {
        $rangeQuery = $query["query"]["bool"]["filter"][$i_filter]["range"]["datePublished"]["gte"] = $get['initialYear'];
      }
      if (!empty($get['finalYear'])) {
        $rangeQuery = $query["query"]["bool"]["filter"][$i_filter]["range"]["datePublished"]["lte"] = $get['finalYear'];
      }
    }

    if (!empty($get['range'])) {
      $query["query"]["bool"]["must"]["query_string"][0]["query"] = $get['range'][0];
    }

    if (isset($query["query"]["bool"])) {
      $query["query"]["bool"]["must"] = $queryArray;
    } else {
      $query["query"] = $queryArray;
    }

    return compact('page', 'query', 'limit', 'skip');
  }

  static function postParser($post)
  {
    $query = [];

    /* Pagination */
    if (isset($post['page'])) {
      $page = $post['page'];
      unset($post['page']);
    } else {
      $page = 1;
    }

    /* Pagination variables */
    $limit = 20;
    $skip = ($page - 1) * $limit;
    $next = ($page + 1);
    $prev = ($page - 1);


    $i_filter = 0;
    if (!empty($post['filter'])) {
      foreach ($post['filter'] as $filter) {
        if (!empty($filter)) {
          $filter_array = explode(":", $filter);
          $filter_array_term = str_replace('"', "", (string) $filter_array[1]);
          $query["query"]["bool"]["filter"][$i_filter]["term"][(string) $filter_array[0] . ".keyword"] = $filter_array_term;
          $i_filter++;
        }
      }
    }

    if (!empty($post['notFilter'])) {
      $i_notFilter = 0;
      foreach ($post['notFilter'] as $notFilter) {
        $notFilterArray = explode(":", $notFilter);
        $notFilterArrayTerm = str_replace('"', "", (string) $notFilterArray[1]);
        $query["query"]["bool"]["must_not"][$i_notFilter]["term"][(string) $notFilterArray[0] . ".keyword"] = $notFilterArrayTerm;
        $i_notFilter++;
      }
    }

    if (!empty($post['search'])) {
      $cleanQuery = strip_tags($post['search']);
      $queryArray["query_string"]["query"] = str_replace('and', 'AND', $cleanQuery);
      $queryArray["query_string"]["fields"] = ["name", "alternateName", "author.person.name", "author.organization.name", "about", "source", "description", "vinculo.lattes_id", "vinculo.nome"];
    } else {
      $queryArray["query_string"]["query"] = "*";
    }

    if (!empty($post['initialYear']) || !empty($post['finalYear'])) {
      if (!empty($post['initialYear'])) {
        $rangeQuery = $query["query"]["bool"]["filter"][$i_filter]["range"]["datePublished"]["gte"] = $post['initialYear'];
      }
      if (!empty($post['finalYear'])) {
        $rangeQuery = $query["query"]["bool"]["filter"][$i_filter]["range"]["datePublished"]["lte"] = $post['finalYear'];
      }
    }

    if (!empty($post['range'])) {
      $query["query"]["bool"]["must"]["query_string"][0]["query"] = $post['range'][0];
    }

    if (isset($query["query"]["bool"])) {
      $query["query"]["bool"]["must"] = $queryArray;
    } else {
      $query["query"] = $queryArray;
    }

    return compact('page', 'query', 'limit', 'skip');
  }
}

class FacetsNew
{
  public function facet($fileName, $field, $size, $field_name, $sort, $sort_type, $get_search, $alternative_index = null)
  {
    global $url_base;

    if (isset($get_search["page"])) {
      unset($get_search["page"]);
    }

    $query = $this->query;
    $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
    if (!empty($_SESSION['oauthuserdata'])) {
      $query["aggs"]["counts"]["terms"]["missing"] = "Não preenchido";
    }
    if (isset($sort)) {
      $query["aggs"]["counts"]["terms"]["order"][$sort_type] = $sort;
    }
    $query["aggs"]["counts"]["terms"]["size"] = $size;

    $response = Elasticsearch::search(null, 0, $query, $alternative_index);

    $result_count = count($response["aggregations"]["counts"]["buckets"]);

    if ($result_count === 0) {
      return;
    }

    if ($result_count === 1 && empty($response["aggregations"]["counts"]["buckets"][0]['key'])) {
      return;
    }

    $facet_array = array();
    $facet_array[] = '<details class="c-filterdrop" open="true">';
    $facet_array[] = '<summary class="c-filterdrop__header"><span class="c-filterdrop__name">' . $field_name . '</span></summary>';
    $facet_array[] = '<ul class="c-filterdrop__content" name="bloc1">';

    foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {

      $facet_array[] = '<li class="c-filterdrop__item">';

      if ($alternative_index == false) {
        $facet_array[] = '<form action="result.php" method="post">';
      } else {
        $facet_array[] = '<form action="result_autores.php" method="post">';
      }
      $facet_array[] = '<input type="hidden" name="search" value="' . $get_search["search"] . '">';

      $facet_array[] = '<input type="hidden" name="filter[]" value="' . $field . ':' . str_replace('&', '%26', $facets['key']) . '">';

      if(isset($get_search['filter'])){              
        if (count($get_search['filter']) > 0) {
          foreach ($get_search['filter'] as $filter) {
            $facet_array[] = '<input type="hidden" name="filter[]" value=\''.$filter.'\'>';
          }
        }
      }
      $facet_array[] = '<input class="c-filterdrop__item-name" style="text-decoration: none; color: initial;" type="submit" value="' . $facets['key'] . '" />';
      $facet_array[] = '</form>';

      $facet_array[] = '<span class="c-filterdrop__count">' . number_format($facets['doc_count'], 0, ',', '.') . '</span>';
      $facet_array[] = '</li>';
    }

    $facet_array[] = '</ul>';
    $facet_array[] = '</details>';
    $facet_string = implode("", $facet_array);

    return $facet_string;
  }

  public function facetExistsField($fileName, $field, $size, $field_name, $sort, $sort_type, $get_search, $alternative_index = null)
  {
    global $url_base;

    if (isset($get_search["page"])) {
      unset($get_search["page"]);
    }

    $query = $this->query;
    $query["aggs"]["field_not_exists"]["missing"]["field"] = "$field.keyword";
    $query["aggs"]["field_exists"]["filter"]["exists"]["field"] = "$field.keyword";

    $response = Elasticsearch::search(null, 0, $query);


    $facet_array = array();
    $facet_array[] = '<details class="c-filterdrop" open="true">';
    $facet_array[] = '<summary class="c-filterdrop__header"><span class="c-filterdrop__name">' . $field_name . '</span></summary>';
    $facet_array[] = '<ul class="c-filterdrop__content" name="bloc1">';

    $facet_array[] = '<li class="c-filterdrop__item">';

    if ($alternative_index == false) {
      $facet_array[] = '<form action="result.php" method="post">';
    } else {
      $facet_array[] = '<form action="result_autores.php" method="post">';
    }
    $facet_array[] = '<input type="hidden" name="search" value="_exists_:' . $field . '">';

    if(isset($get_search['filter'])){
      if (count($get_search['filter']) > 0) {
        foreach ($get_search['filter'] as $filter) {
          $facet_array[] = '<input type="hidden" name="filter[]" value=\''.$filter.'\'>';
        }
      }
    }
    $facet_array[] = '<input class="c-filterdrop__item-name" style="text-decoration: none; color: initial;" type="submit" value="Está preenchido" />';
    $facet_array[] = '</form>';

    $facet_array[] = '<span class="c-filterdrop__count">' . number_format($response["aggregations"]["field_exists"]["doc_count"], 0, ',', '.') . '</span>';

    $facet_array[] = '</li>';

    $facet_array[] = '<li class="c-filterdrop__item">';

    if ($alternative_index == false) {
      $facet_array[] = '<form action="result.php" method="post">';
    } else {
      $facet_array[] = '<form action="result_autores.php" method="post">';
    }
    $facet_array[] = '<input type="hidden" name="search" value="-_exists_:' . $field . '">';

    if(isset($get_search['filter'])){              
      if (count($get_search['filter']) > 0) {
        foreach ($get_search['filter'] as $filter) {
          $facet_array[] = '<input type="hidden" name="filter[]" value=\''.$filter.'\'>';
        }
      }
    }
    $facet_array[] = '<input class="c-filterdrop__item-name" style="text-decoration: none; color: initial;" type="submit" value="Não está preenchido" />';
    $facet_array[] = '</form>';

    $facet_array[] = '<span class="c-filterdrop__count">' . number_format($response["aggregations"]["field_not_exists"]["doc_count"], 0, ',', '.') . '</span>';

    $facet_array[] = '</li>';

    $facet_array[] = '</ul>';
    $facet_array[] = '</details>';
    $facet_string = implode("", $facet_array);

    return $facet_string;

  }

  public function rebuild_facet($field, $size, $nome_do_campo)
  {
    $query = $this->query;
    $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
    if (isset($sort)) {
      $query["aggs"]["counts"]["terms"]["order"]["_count"] = "desc";
    }
    $query["aggs"]["counts"]["terms"]["size"] = $size;

    $response = Elasticsearch::elasticSearch(null, 0, $query);

    echo '<li class="uk-parent">';
    echo '<a href="#" style="color:#333">' . $nome_do_campo . '</a>';
    echo ' <ul class="uk-nav-sub">';
    foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
      $termCleaned = str_replace("&", "*", $facets['key']);
      echo '<li">';
      echo "<div uk-grid>";
      echo '<div class="uk-width-2-3 uk-text-small" style="color:#333">';
      echo '<a href="admin/autoridades.php?term=&quot;' . $termCleaned . '&quot;" style="color:#0040ff;font-size: 90%">' . $termCleaned . ' (' . number_format($facets['doc_count'], 0, ',', '.') . ')</a>';
      echo '</div>';
      echo '</li>';
    };
    echo '</ul>
          </li>';
  }

  public function facet_range($fileName, $field, $size, $field_name, $type_of_number = "")
  {
    $query = $this->query;
    if ($type_of_number == "INT") {
      $query["aggs"]["ranges"]["range"]["field"] = "$field";
      $query["aggs"]["ranges"]["range"]["ranges"][0]["to"] = 1;
      $query["aggs"]["ranges"]["range"]["ranges"][1]["from"] = 1;
      $query["aggs"]["ranges"]["range"]["ranges"][1]["to"] = 2;
      $query["aggs"]["ranges"]["range"]["ranges"][2]["from"] = 2;
      $query["aggs"]["ranges"]["range"]["ranges"][2]["to"] = 5;
      $query["aggs"]["ranges"]["range"]["ranges"][3]["from"] = 5;
      $query["aggs"]["ranges"]["range"]["ranges"][3]["to"] = 10;
      $query["aggs"]["ranges"]["range"]["ranges"][4]["from"] = 10;
      $query["aggs"]["ranges"]["range"]["ranges"][3]["to"] = 20;
      $query["aggs"]["ranges"]["range"]["ranges"][4]["from"] = 20;
    } else {
      $query["aggs"]["ranges"]["range"]["field"] = "$field";
      $query["aggs"]["ranges"]["range"]["ranges"][0]["to"] = 1;
      $query["aggs"]["ranges"]["range"]["ranges"][1]["from"] = 1;
      $query["aggs"]["ranges"]["range"]["ranges"][1]["to"] = 2;
      $query["aggs"]["ranges"]["range"]["ranges"][2]["from"] = 2;
      $query["aggs"]["ranges"]["range"]["ranges"][2]["to"] = 5;
      $query["aggs"]["ranges"]["range"]["ranges"][3]["from"] = 5;
      $query["aggs"]["ranges"]["range"]["ranges"][3]["to"] = 10;
      $query["aggs"]["ranges"]["range"]["ranges"][4]["from"] = 10;
      $query["aggs"]["ranges"]["range"]["ranges"][3]["to"] = 20;
      $query["aggs"]["ranges"]["range"]["ranges"][4]["from"] = 20;
    }

    //$query["aggs"]["counts"]["terms"]["size"] = $size;

    $response = Elasticsearch::search(null, 0, $query);

    $result_count = count($response["aggregations"]["ranges"]["buckets"]);

    if ($result_count > 0) {

      $facet_array = array();
      $facet_array[] = '<details class="c-filterdrop" open="true">';
      $facet_array[] = '<summary class="c-filterdrop__header"><span class="c-filterdrop__name">' . $field_name . '</span></summary>';
      $facet_array[] = '<ul class="c-filterdrop__content" name="bloc1">';  

      foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
        $facet_array[] = '<li class="c-filterdrop__item">';
        $facet_array[] = '<form action="result.php" method="post">';
        $facets_array = explode("-", $facets['key']);
        $facet_array[] = '<input type="hidden" name="search" value="' . $field . ':[' . $facets_array[0] . ' TO ' . $facets_array[1] . ']">';
        if(isset($get_search['filter'])){
          if (count($get_search['filter']) > 0) {
            foreach ($get_search['filter'] as $filter) {
              $facet_array[] = '<input type="hidden" name="filter[]" value=\''.$filter.'\'>';
            }
          }
        }

        $facet_array[] = '<input class="c-filterdrop__item-name" style="text-decoration: none; color: initial;" type="submit" value="Entre: ' . $facets['key'] . '" />';
        $facet_array[] = '<span class="c-filterdrop__count">' . number_format($facets['doc_count'], 0, ',', '.') . '</span>';
        $facet_array[] = '</li>';
        $facet_array[] = '</form>';
      };

      // echo '<a href="#" class="list-group-item list-group-item-action active">' . $field_name . '</a>';
      // echo '<ul class="list-group list-group-flush">';
      // foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
      //   $facets_array = explode("-", $facets['key']);
      //   echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
      //   echo '<a href="' . $fileName . '?&search=' . $field . ':[' . $facets_array[0] . ' TO ' . $facets_array[1] . ']" style="color:#0040ff;font-size: 90%">Intervalo ' . $facets['key'] . '</a>
      //           <span class="badge badge-primary badge-pill">' . number_format($facets['doc_count'], 0, ',', '.') . '</span>';
      //   echo '</li>';
      // };
      // echo '</ul>';


      $facet_array[] = '</ul>';
      $facet_array[] = '</details>';
      $facet_string = implode("", $facet_array);
  
      return $facet_string;

    }
  }
  public function facetcited($fileName, $field, $size, $field_name, $sort, $sort_type, $get_search, $alternative_index = null)
  {
    global $url_base;

    if (isset($get_search["page"])) {
      unset($get_search["page"]);
    }

    $query = $this->query;
    $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
    if (!empty($_SESSION['oauthuserdata'])) {
      $query["aggs"]["counts"]["terms"]["missing"] = "Não preenchido";
    }
    if (isset($sort)) {
      $query["aggs"]["counts"]["terms"]["order"][$sort_type] = $sort;
    }
    $query["aggs"]["counts"]["terms"]["size"] = $size;

    $response = Elasticsearch::search(null, 0, $query, $alternative_index);

    $result_count = count($response["aggregations"]["counts"]["buckets"]);

    if ($result_count === 0) {
      return;
    }

    if ($result_count === 1 && empty($response["aggregations"]["counts"]["buckets"][0]['key'])) {
      return;
    }

    $facet_array = array();
    $facet_array[] = '<details class="c-filterdrop" open="true">';
    $facet_array[] = '<summary class="c-filterdrop__header"><span class="c-filterdrop__name">' . $field_name . '</span></summary>';
    $facet_array[] = '<ul class="c-filterdrop__content" name="bloc1">';

    foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {

      $facet_array[] = '<li class="c-filterdrop__item">';

      if ($alternative_index == false) {
        $facet_array[] = '<form action="result.php" method="post">';
      } else {
        $facet_array[] = '<form action="result_autores.php" method="post">';
      }
      $facet_array[] = '<input type="hidden" name="search" value="' . $get_search["search"] . '">';

      $facet_array[] = '<input type="hidden" name="filter[]" value="' . $field . ':' . $facets['key'] . '">';

      if(isset($get_search['filter'])){              
        if (count($get_search['filter']) > 0) {
          foreach ($get_search['filter'] as $filter) {
            $facet_array[] = '<input type="hidden" name="filter[]" value=\''.$filter.'\'>';
          }
        }
      }
      
      $openalex_result = openalexAPIID(str_replace("https://openalex.org/", "", $facets['key']));
      $facet_array[] = '<input class="c-filterdrop__item-name" style="text-decoration: none; color: initial;" type="submit" value="' . $openalex_result['title'] . '" />';
      $facet_array[] = '</form>';

      $facet_array[] = '<span class="c-filterdrop__count">' . number_format($facets['doc_count'], 0, ',', '.') . '</span>';
      $facet_array[] = '</li>';
    }

    $facet_array[] = '</ul>';
    $facet_array[] = '</details>';
    $facet_string = implode("", $facet_array);

    return $facet_string;
  }


}

class Citation
{

  static function getType($material_type)
  {
    switch ($material_type) {
      case "ARTIGO DE JORNAL":
        return "article-newspaper";
        break;
      case "ARTIGO DE PERIODICO":
        return "article-journal";
        break;
      case "PARTE DE MONOGRAFIA/LIVRO":
        return "chapter";
        break;
      case "MONOGRAFIA/LIVRO-ED/ORG":
        return "book";
        break;
      case "LIVRO":
        return "book";
        break;
      case "APRESENTACAO SONORA/CENICA/ENTREVISTA":
        return "interview";
        break;
      case "TRABALHO DE EVENTO-RESUMO":
        return "paper-conference";
        break;
      case "TRABALHO DE EVENTO":
        return "paper-conference";
        break;
      case "TESE":
        return "thesis";
        break;
      case "TEXTO NA WEB":
        return "post-weblog";
        break;
      case "Artigo publicado":
        return "article-journal";
        break;
      case "Trabalhos em eventos":
        return "paper-conference";
        break;
      case "Capítulo de livro publicado":
        return "chapter";
        break;
      case "Livro publicado ou organizado":
        return "book";
        break;
      case "Textos em jornais de notícias/revistas":
        return "article-newspaper";
        break;
    }
  }

  static function citationQuery($citacao)
  {
    //var_dump($citacao);
    $array_citation = [];
    $array_citation["tipo"] = Citation::getType($citacao["tipo"]);
    $array_citation["title"] = $citacao["name"];

    if (!empty($citacao["author"])) {
      $i = 0;
      foreach ($citacao["author"] as $authors) {
        $array_authors = explode(',', $authors["person"]["name"]);
        $array_citation["author"][$i]["family"] = $array_authors[0];
        if (!empty($array_authors[1])) {
          $array_citation["author"][$i]["given"] = $array_authors[1];
        }
        $i++;
      }
    }

    if (!empty($citacao["isPartOf"]["name"])) {
      $array_citation["container-title"] = $citacao["isPartOf"]["name"];
    }
    if (!empty($citacao["doi"])) {
      $array_citation["DOI"] = $citacao["doi"];
    }
    if (!empty($citacao["url"][0])) {
      $array_citation["URL"] = $citacao["url"][0];
    }

    if (!empty($citacao["publisher"]["organization"]["name"])) {
      $array_citation["publisher"] = $citacao["publisher"]["organization"]["name"];
    }
    if (!empty($citacao["publisher"]["organization"]["location"])) {
      $array_citation["publisher-place"] = $citacao["publisher"]["organization"]["location"];
    }
    if (!empty($citacao["datePublished"])) {
      $array_citation["issued"]["date-parts"][0][] = intval($citacao["datePublished"]);
    }

    if (!empty($citacao["isPartOf"]["USP"]["dados_do_periodico"])) {
      $periodicos_array = explode(",", $citacao["isPartOf"]["USP"]["dados_do_periodico"]);
      foreach ($periodicos_array as $periodicos_array_new) {
        if (strpos($periodicos_array_new, 'v.') !== false) {
          $array_citation["volume"] = str_replace("v.", "", $periodicos_array_new);
        } elseif (strpos($periodicos_array_new, 'n.') !== false) {
          $array_citation["issue"] = str_replace("n.", "", $periodicos_array_new);
        } elseif (strpos($periodicos_array_new, 'p.') !== false) {
          $array_citation["page"] = str_replace("p.", "", $periodicos_array_new);
        }
      }
    }

    if (!empty($citacao["doi"]) || !empty($citacao["url"][0])) {
      $array_citation["accessed"]["date-parts"][0][] = date("Y");
      $array_citation["accessed"]["date-parts"][0][] = date("m");
      $array_citation["accessed"]["date-parts"][0][] = date("d");
    }

    $json = json_encode($array_citation);
    $data = json_decode($json);
    //var_dump($data);
    return array($data);
  }
}


class UI
{

  static function pagination($page, $total, $limit, $post, $type_of_page)
  {
    echo '<nav>';
    echo '<ul class="list-group list-group-horizontal">';
    if ($page == 1) {
      echo '<li class="list-group-item w-25 disabled">Anterior</li>';
    } else {
      $last_page = $page - 1;
      echo '<li class="list-group-item w-25">';
      echo '<form action="' . $type_of_page . '.php" method="post">';
      echo '<input type="hidden" name="search" value="' . $post["search"] . '">';
      echo '<input type="hidden" name="page" value="' . $last_page . '">';
      if (isset($post['filter'])) {
        if (count($post['filter']) < 0) {
          foreach ($post['filter'] as $filter) {
            echo '<input type="hidden" name="filter[]" value="' . $filter . '">';
          }
        }
      }
      echo '<input class="list-group-item d-flex justify-content-between align-items-center" style="text-decoration: none; color: initial;" type="submit" value="Anterior" />';
      echo '</form>';

      echo '</li>';
    }
    echo '<li class="list-group-item w-25 disabled">Página ' . number_format($page, 0, ',', '.') . '</li>';
    echo '<li class="list-group-item w-25 disabled">' . number_format($total, 0, ',', '.') . '&nbsp;registros</li>';
    if ($total / $limit > $page) {
      $next_page = $page + 1;
      echo '<li class="list-group-item w-25">';

      echo '<form action="' . $type_of_page . '.php" method="post">';
      echo '<input type="hidden" name="search" value="' . $post["search"] . '">';
      echo '<input type="hidden" name="page" value="' . $next_page . '">';
      if (isset($post['filter'])) {
        if (count($post['filter']) < 0) {
          foreach ($post['filter'] as $filter) {
            echo '<input type="hidden" name="filter[]" value="' . $filter . '">';
          }
        }
      }
      echo '<input class="list-group-item d-flex justify-content-between align-items-center" style="text-decoration: none; color: initial;" type="submit" value="Próxima" />';
      echo '</form>';

      echo '</li>';
    } else {
      echo '<li class="list-group-item w-25 disabled">Próxima</li>';
    }
    echo '</ul>';
    echo '</nav>';
  }

  static function newpagination($page, $total, $limit, $post, $type_of_page)
  {
   
    echo '<div class="c-navigator">';
    if ($page == 1) {
      echo '<i class="i i-arrow-left i-lg" style="color:rgb(128, 128, 128)"></i>';
    } else {
      //var_dump($post['filter']);
      $last_page = $page - 1;

      echo '<form action="' . $type_of_page . '.php" method="post">';
      echo '<input type="hidden" name="search" value="' . $post["search"] . '">';
      echo '<input type="hidden" name="page" value="' . $last_page . '">';
      if (isset($post['filter']) && is_array($post['filter'])) {
        foreach ($post['filter'] as $filter) {
          echo '<input type="hidden" name="filter[]" value=\'' . $filter . '\'>';
        }
      }
      echo '<button class="c-navigator-btn c-btn--c1"><i class="i i-arrow-left i-lg"></i></button>';
      echo '</form>';
    }
    echo '<span>Página ' . number_format($page, 0, ',', '.') . '  | ' . number_format($total, 0, ',', '.') . '&nbsp;registros</span>';
    if ($total / $limit > $page) {
      //var_dump($post['filter']);
      $next_page = $page + 1;

      echo '<form action="' . $type_of_page . '.php" method="post">';
      echo '<input type="hidden" name="search" value="' . $post["search"] . '">';
      echo '<input type="hidden" name="page" value="' . $next_page . '">';
      
      if (isset($post['filter']) && is_array($post['filter'])) {        
        foreach ($post['filter'] as $filter) {         
          echo '<input type="hidden" name="filter[]" value=\'' . $filter . '\'>';
        }
      }
      echo '<button class="c-navigator-btn c-btn--c1"><i class="i i-arrow-right i-lg"></i></button>';
      echo '</form>';
    } else {
      echo '<i class="i i-arrow-right i-lg" style="color:rgb(128, 128, 128)"></i>';
    }
    echo '</div>';
  }
}



class Authorities
{

  public static function tematresQuery($term, $tematresWebServicesUrl)
  {
    // Clean term
    $term = preg_replace("/\s+/", " ", $term);
    $clean_term = str_replace(array("\r\n", "\n", "\r"), "", $term);
    $clean_term = preg_replace('/^\s+|\s+$/', '', $clean_term);
    $clean_term = str_replace("\t\n\r\0\x0B\xc2\xa0", " ", $clean_term);
    $clean_term = trim($clean_term, " \t\n\r\0\x0B\xc2\xa0");
    $clean_term = rawurlencode($clean_term);
    $clean_term_p = $term;
    $clean_term = str_replace("%C2%A0", "%20", $clean_term);
    $clean_term = str_replace("&", "e", $clean_term);
    // Query tematres
    $ch = curl_init();
    $method = "GET";
    $url = '' . $tematresWebServicesUrl . '?task=fetch&arg=' . $clean_term . '&output=json';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    $result_get_id_tematres = curl_exec($ch);
    $resultado_get_id_tematres = json_decode($result_get_id_tematres, true);
    curl_close($ch);
    // Get correct term
    if ($resultado_get_id_tematres["resume"]["cant_result"] != 0) {
      foreach ($resultado_get_id_tematres["result"] as $key => $val) {
        $term_key = $key;
      }
      $ch = curl_init();
      $method = "GET";
      $url = '' . $tematresWebServicesUrl . '?task=fetchTerm&arg=' . $term_key . '&output=json';
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
      $result_term = curl_exec($ch);
      $resultado_term = json_decode($result_term, true);
      $foundTerm = $resultado_term["result"]["term"]["string"];
      $termNotFound = "ND";
      curl_close($ch);
      $ch_country = curl_init();
      $method = "GET";
      $url_country = '' . $tematresWebServicesUrl . '?task=fetchUp&arg=' . $term_key . '&output=json';
      curl_setopt($ch_country, CURLOPT_URL, $url_country);
      curl_setopt($ch_country, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch_country, CURLOPT_CUSTOMREQUEST, strtoupper($method));
      $result_country = curl_exec($ch_country);
      $resultado_country = json_decode($result_country, true);
      foreach ($resultado_country["result"] as $country_list) {
        if ($country_list["order"] == 1) {
          $topTerm = $country_list["string"];
        }
      }
      curl_close($ch_country);
    } else {
      $termNotFound = $clean_term_p;
      $foundTerm = "ND";
      $topTerm = "ND";
    }
    return compact('foundTerm', 'termNotFound', 'topTerm');
  }
}

/**
 * DSpaceREST
 *
 * @category Class
 * @package  DSpaceREST
 * @author   Tiago Rodrigo Marçal Murakami <tiago.murakami@dt.sibi.usp.br>
 * @license  
 * @link     
 */
class DSpaceREST
{
  static function loginREST()
  {

    global $dspaceRest;
    global $dspaceEmail;
    global $dspacePassword;

    // API URL
    $url = '' . $dspaceRest . '/rest/login';

    // Create a new cURL resource
    $ch = curl_init($url);

    // Setup request to send json via POST
    $data = array(
      'email' => $dspaceEmail,
      'password' => $dspacePassword
    );
    $jsonData = json_encode($data);

    // Attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

    // Return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the POST request
    $result = curl_exec($ch);

    // // Close cURL resource
    // curl_close($ch);

    // $ch = curl_init();

    // curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/login");
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_HEADER, 1);        
    // curl_setopt($ch, CURLOPT_POSTFIELDS,
    //     http_build_query(array('email' => $dspaceEmail,'password' => $dspacePassword))
    // );
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // $server_output = curl_exec($ch);
    // $output_parsed = explode(" ", $server_output);

    return $result;

    curl_close($ch);
  }

  static function logoutREST($DSpaceCookies)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $DSpaceCookies"));
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/logout");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);
  }

  static function searchItemDSpace($sysno, $DSpaceCookies = null)
  {
    global $dspaceRest;
    $data_string = "{\"key\":\"usp.sysno\", \"value\":\"$sysno\"}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/items/find-by-metadata-field");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    if (!empty($DSpaceCookies)) {
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          "Cookie: $DSpaceCookies",
          'Content-Type: application/json'
        )
      );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    if (!empty($result)) {
      return $result[0]["uuid"];
    } else {
      return "";
    }
    curl_close($ch);
  }

  static function getBitstreamDSpace($itemID, $DSpaceCookies = NULL)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/items/$itemID/bitstreams");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    if (!empty($DSpaceCookies)) {
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          "Cookie: $DSpaceCookies",
          'Content-Type: application/json'
        )
      );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
  }

  static function getBitstreamPolicyDSpace($bitstreamID, $DSpaceCookies = null)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/bitstreams/$bitstreamID/policy");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    if (!empty($DSpaceCookies)) {
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          "Cookie: $DSpaceCookies",
          'Content-Type: application/json'
        )
      );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
  }

  static function deleteBitstreamPolicyDSpace($bitstreamID, $policyID, $DSpaceCookies)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/bitstreams/$bitstreamID/policy/$policyID");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        "Cookie: $DSpaceCookies",
        'Content-Type: application/json'
      )
    );
    $output = curl_exec($ch);
    //var_dump($output);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
  }

  static function addBitstreamPolicyDSpace($bitstreamID, $policyAction, $groupId, $resourceType, $rpType, $DSpaceCookies, $embargoStartDate = "", $embargoEndDate = "")
  {
    global $dspaceRest;
    $policyArray["action"] = $policyAction;
    $policyArray["epersonId"] = "";
    $policyArray["groupId"] = $groupId;
    $policyArray["resourceId"] = $bitstreamID;
    $policyArray["resourceType"] = $resourceType;
    $policyArray["rpDescription"] = "";
    $policyArray["rpName"] = "";
    $policyArray["rpType"] = $rpType;
    $policyArray["startDate"] = "$embargoStartDate";
    $policyArray["endDate"] = "$embargoEndDate";
    $data_string = json_encode($policyArray);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/bitstreams/$bitstreamID/policy");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    if (!empty($DSpaceCookies)) {
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          "Cookie: $DSpaceCookies",
          'Content-Type: application/json'
        )
      );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
  }

  // static function getBitstreamRestrictedDSpace($bitstreamID, $DSpaceCookies)
  // {
  //     global $dspaceRest;
  //     $ch = curl_init();
  //     curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/bitstreams/$bitstreamID/retrieve/64171-196117-1-PB.pdf");
  //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  //     if (!empty($DSpaceCookies)) {
  //         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  //             "Cookie: $DSpaceCookies",
  //             'Content-Type: application/json'
  //             )
  //         );
  //     }
  //     $output = curl_exec($ch);
  //     var_dump($output);
  //     //$result = json_decode($output, true);
  //     return $result;
  //     curl_close($ch);
  // }

  static function createItemDSpace($dataString, $collection, $DSpaceCookies)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/collections/$collection/items");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        "rest-dspace-token: $DSpaceCookies",
        'Content-Type: application/json'
      )
    );
    $output = curl_exec($ch);
    return $output;
    curl_close($ch);
  }

  static function deleteItemDSpace($uuid, $DSpaceCookies)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/items/$uuid");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    if (!empty($DSpaceCookies)) {
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          "Cookie: $DSpaceCookies",
          'Content-Type: application/json'
        )
      );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
  }

  static function addBitstreamDSpace($uuid, $file, $userBitstream, $DSpaceCookies)
  {
    global $dspaceRest;
    $filename = rawurlencode($file["file"]["name"]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/items/$uuid/bitstreams?name=$filename&description=$userBitstream");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file["file"]["tmp_name"]));
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        "Cookie: $DSpaceCookies",
        'Content-Type: text/plain',
        'Accept: application/json'
      )
    );
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    curl_close($ch);
    return $result;
  }

  static function deleteBitstreamDSpace($bitstreamId, $DSpaceCookies)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/bitstreams/$bitstreamId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    if (!empty($DSpaceCookies)) {
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          "Cookie: $DSpaceCookies",
          'Content-Type: application/json'
        )
      );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
  }

  static function buildDC($cursor, $sysno)
  {
    $arrayDC["type"] = "item";

    /* Title */
    $title["key"] = "dc.title";
    $title["language"] = "pt_BR";
    $title["value"] = $cursor["_source"]["name"];
    $arrayDC["metadata"][] = $title;
    $title = [];

    // /* Sysno */
    // $sysnoArray["key"] = "usp.sysno";
    // $sysnoArray["language"] = "pt_BR";
    // $sysnoArray["value"] = $sysno;
    // $arrayDC["metadata"][] = $sysnoArray;
    // $sysnoArray = [];

    // /* Abstract */
    // if (!empty($marc["record"]["940"]["a"])){
    //     $abstractArray["key"] = "dc.description.abstract";
    //     $abstractArray["language"] = "pt_BR";
    //     $abstractArray["value"] = $marc["record"]["940"]["a"][0];
    //     $arrayDC["metadata"][] = $abstractArray;
    //     $abstractArray = [];
    // } elseif (!empty($marc["record"]["520"]["a"])){
    //     $abstractArray["key"] = "dc.description.abstract";
    //     $abstractArray["language"] = "pt_BR";
    //     $abstractArray["value"] = $marc["record"]["520"]["a"][0];
    //     $arrayDC["metadata"][] = $abstractArray;
    //     $abstractArray = [];
    // }


    /* DateIssued */
    $dateIssuedArray["key"] = "dc.date.issued";
    $dateIssuedArray["language"] = "pt_BR";
    $dateIssuedArray["value"] = $cursor["_source"]["datePublished"];
    $arrayDC["metadata"][] = $dateIssuedArray;
    $dateIssuedArray = [];

    /* DOI */
    if (!empty($cursor["_source"]["doi"])) {
      $DOIArray["key"] = "dc.identifier";
      $DOIArray["language"] = "pt_BR";
      $DOIArray["value"] = $cursor["_source"]["doi"];
      $arrayDC["metadata"][] = $DOIArray;
      $DOIArray = [];
    }

    /* IsPartOf */
    if (!empty($cursor["_source"]["isPartOf"])) {
      $IsPartOfArray["key"] = "dc.relation.ispartof";
      $IsPartOfArray["language"] = "pt_BR";
      $IsPartOfArray["value"] = $cursor["_source"]["isPartOf"]["name"];
      $arrayDC["metadata"][] = $IsPartOfArray;
      $IsPartOfArray = [];
    }

    /* Authors */
    foreach ($cursor["_source"]["author"] as $author) {
      $authorArray["key"] = "dc.contributor.author";
      $authorArray["language"] = "pt_BR";
      $authorArray["value"] = $author["person"]["name"];
      $arrayDC["metadata"][] = $authorArray;
      $authorArray = [];
    }


    // /* Unidade USP */
    // if (isset($cursor["_source"]["authorUSP"])) {
    //     foreach ($cursor["_source"]["authorUSP"] as $unidadeUSP) {
    //         $unidadeUSPArray["key"] = "usp.unidadeUSP";
    //         $unidadeUSPArray["language"] = "pt_BR";
    //         $unidadeUSPArray["value"] = $unidadeUSP["unidadeUSP"];
    //         $arrayDC["metadata"][] = $unidadeUSPArray;
    //         $unidadeUSPArray = [];

    //         $authorUSPArray["key"] = "usp.authorUSP.name";
    //         $authorUSPArray["language"] = "pt_BR";
    //         $authorUSPArray["value"] = $unidadeUSP["name"];
    //         $arrayDC["metadata"][] = $authorUSPArray;
    //         $authorUSPArray = [];
    //     }
    // }

    // /* Subject */
    // foreach ($cursor["_source"]["about"] as $subject) {
    //     $subjectArray["key"] = "dc.subject.other";
    //     $subjectArray["language"] = "pt_BR";
    //     $subjectArray["value"] = $subject;
    //     $arrayDC["metadata"][] = $subjectArray;
    //     $subjectArray = [];
    // }

    // /* USP Type */
    // $USPTypeArray["key"] = "usp.type";
    // $USPTypeArray["language"] = "pt_BR";
    // $USPTypeArray["value"] = $cursor["_source"]["type"];
    // $arrayDC["metadata"][] = $USPTypeArray;
    // $USPTypeArray = [];

    $jsonDC = json_encode($arrayDC);
    return $jsonDC;
  }

  static function testREST($DSpaceCookies)
  {
    global $dspaceRest;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $DSpaceCookies"));
    curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/status");
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    print_r($server_output);
    curl_close($ch);
  }
}

class Work
{
  public $type;
  public $source;
  public $lattes_ids;
  public $tag;
  public $name;
  public $author;
  public $datePublished;
  public $language;
  public $url;
  public $doi;
  public $pageStart;
  public $pageEnd;

  function __construct()
  {
    $this->type = "Work";
  }
  function getDoc()
  {
    $doc = $this->type;
    return $doc;
  }
}

class LattesWork extends Work
{
  public $lattes;
  public $vinculo;
  public function __construct()
  {
    parent::__construct();
    $this->source = "Base Lattes";
  }
}

class TrabalhosEmEventosLattes extends LattesWork
{
  public $detalhamentoDoTrabalho;
  function __construct()
  {
    parent::__construct();
  }
}

function openalexAPI($doi)
{
    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.openalex.org/works/https://doi.org/' . $doi . '',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A'
        )
    );
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    $data = json_decode($resp, true);
    return $data;
    // Close request to clear up some resources
    curl_close($curl);
}

function openalexAPIID($ID)
{
    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.openalex.org/works/' . $ID . '',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A'
        )
    );
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    $data = json_decode($resp, true);
    return $data;
    // Close request to clear up some resources
    curl_close($curl);
}

function openalexGetDOI($title)
{   
    // Set API endpoint URL
    $url = 'https://api.openalex.org/works';

    // Set search query
    $search_query = $title;

    // Setup headers - I used the same headers from Firefox version 2.0.0.6
    // below was split up because php.net said the line was too long. :/
    $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
    $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
    $header[] = "Cache-Control: max-age=0";
    $header[] = "Connection: keep-alive";
    $header[] = "Keep-Alive: 300";
    $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $header[] = "Accept-Language: en-us,en;q=0.5";
    // browsers keep this blank. 

    $browsers = array("Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.3) Gecko/2008092510 Ubuntu/8.04 (hardy) Firefox/3.0.3", "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1) Gecko/20060918 Firefox/2.0", "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3", "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)");
    $choice2 = array_rand($browsers);
    $browser = $browsers[$choice2]; 

    $referers = array("google.com", "yahoo.com", "msn.com", "ask.com", "live.com");
    $choice = array_rand($referers);
    $referer = "http://" . $referers[$choice] . "";

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_USERAGENT, $browser);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
    curl_setopt($ch, CURLOPT_URL, $url . '?search="' . urlencode($search_query) . '"');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request and get response
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);

    $data = json_decode($response, true);
    return $data;

}