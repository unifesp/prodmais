<?php
/**
 * Classes file with main functions 
 */


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

        if (strlen($alternative_index) > 0 ) {
            $params["index"] = $alternative_index;
        } else {
            $params["index"] = $index;
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
        echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';

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
                                'language' =>  'light_portuguese'
                            ]
                        ],
                        'analyzer' => [
                            'rebuilt_portuguese' => [
                                'tokenizer' => 'standard',
                                'filter' =>  [ 
                                    'lowercase', 
                                    'my_ascii_folding',
                                    'portuguese_stop',
                                    'portuguese_stemmer'
                                ]
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
                        'author' => [
                            'properties' => [
                                'person' => [
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
                    $filter_array_term = str_replace('"', "", (string)$filter_array[1]);
                    $query["query"]["bool"]["filter"][$i_filter]["term"][(string)$filter_array[0].".keyword"] = $filter_array_term;
                    $i_filter++;
                }
            }

        }

        if (!empty($get['notFilter'])) {
            $i_notFilter = 0;
            foreach ($get['notFilter'] as $notFilter) {
                $notFilterArray = explode(":", $notFilter);
                $notFilterArrayTerm = str_replace('"', "", (string)$notFilterArray[1]);
                $query["query"]["bool"]["must_not"][$i_notFilter]["term"][(string)$notFilterArray[0].".keyword"] = $notFilterArrayTerm;
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

}

class Facets
{
    public function facet($fileName, $field, $size, $field_name, $sort, $sort_type, $get_search, $alternative_index = null, $collapsed = true)
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

        if ($result_count == 0) {

        } elseif (($result_count != 0) && ($result_count < 5)) {

            if (($result_count == 1) && ($response["aggregations"]["counts"]["buckets"][0]["key"] == "")) {

            } else {
                echo '<div class="accordion-item">';
                echo '<h2 class="accordion-header" id="heading'.hash('crc32', $field_name).'">
                <button class="accordion-button '.($collapsed == true ? "collapsed" : "").'" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.hash('crc32', $field_name).'" aria-expanded="'.($collapsed == true ? "true" : "false").'" aria-controls="collapse'.hash('crc32', $field_name).'">
                '.$field_name.'
                </button>
                </h2>';
                echo '<div id="collapse'.hash('crc32', $field_name).'" class="accordion-collapse collapse '.($collapsed == true ? "show" : "").'" aria-labelledby="heading'.hash('crc32', $field_name).'" data-bs-parent="#accordionExample">
                <div class="accordion-body">';

                echo '<ul class="list-group list-group-flush">';
                foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
                    if ($facets['key'] == "Não preenchido") {
                        echo '<li>';
                        echo '<div uk-grid>
                                <div class="uk-width-expand" style="color:#333">
                                    <a href="'.$fileName.'?'.http_build_query($get_search).'&search=(-_exists_:'.$field.')">'.$facets['key'].'</a>
                                </div>
                                <div class="uk-width-auto" style="color:#333">
                                    <span class="uk-badge" style="font-size:80%">'.number_format($facets['doc_count'], 0, ',', '.').'</span>
                                </div>';
                        echo '</div></li>';
                    } else {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo '<a href="'.$fileName.'?'.http_build_query($get_search).'&filter[]='.$field.':&quot;'.str_replace('&', '%26', $facets['key']).'&quot;"  title="E" style="color:#0040ff;font-size: 90%">'.$facets['key'].'</a>
                        <span class="badge bg-primary badge-pill">'.number_format($facets['doc_count'], 0, ',', '.').'</span>';
                        echo '</li>'; 
                    }
    
                };
                echo '</ul>';
                echo '</div></div>';
            }
        } else {
            $i = 0;
            echo '<div class="accordion-item">';
            echo '<h2 class="accordion-header" id="heading'.hash('crc32', $field_name).'">
            <button class="accordion-button '.($collapsed == true ? "collapsed" : "").'" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.hash('crc32', $field_name).'" aria-expanded="'.($collapsed == true ? "true" : "false").'" aria-controls="collapse'.hash('crc32', $field_name).'">
            '.$field_name.'
            </button>
            </h2>';
            echo '<div id="collapse'.hash('crc32', $field_name).'" class="accordion-collapse collapse '.($collapsed == true ? "show" : "").'" aria-labelledby="heading'.hash('crc32', $field_name).'" data-bs-parent="#accordionExample">';
            echo '<div class="accordion-body">';
            echo '<ul class="list-group list-group-flush">';
            while ($i < 5) {
                if ($response["aggregations"]["counts"]["buckets"][$i]['key'] == "Não preenchido") {
                    echo '<li>';
                    echo '<div uk-grid>
                            <div class="uk-width-expand uk-text-small" style="color:#333">
                                <a href="'.$fileName.''.http_build_query($get_search).'&search=(-_exists_:'.$field.')">'.$response["aggregations"]["counts"]["buckets"][$i]['key'].'</a>
                            </div>
                            <div class="uk-width-auto" style="color:#333">
                            <span class="uk-badge" style="font-size:80%">'.number_format($response["aggregations"]["counts"]["buckets"][$i]['doc_count'], 0, ',', '.').'</span>
                            </div>';
                    echo '</div></li>';
                } else {
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                    echo '<a href="'.$fileName.'?'.http_build_query($get_search).'&filter[]='.$field.':&quot;'.str_replace('&', '%26', $response["aggregations"]["counts"]["buckets"][$i]['key']).'&quot;"  title="E" style="color:#0040ff;font-size: 90%">'.$response["aggregations"]["counts"]["buckets"][$i]['key'].'</a>
                    <span class="badge bg-primary badge-pill">'.number_format($response["aggregations"]["counts"]["buckets"][$i]['doc_count'], 0, ',', '.').'</span>';
                    echo '</li>';
                }
                $i++;
            }


            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#'.str_replace(".", "", $field).'Modal">mais >>></button>  ';
            echo '</li>';
            echo '</ul>';
            echo '<div class="modal fade" id="'.str_replace(".", "", $field).'Modal" tabindex="-1" role="dialog" aria-labelledby="'.str_replace(".", "", $field).'ModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="'.$field.'ModalLabel">'.$field_name.'</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">';
                    foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo '<a href="'.$fileName.'?'.http_build_query($get_search).'&filter[]='.$field.':&quot;'.str_replace('&', '%26', $facets['key']).'&quot;"  title="E" style="color:#0040ff;font-size: 90%">'.$facets['key'].'</a>
                            <span class="badge bg-primary badge-pill">'.number_format($facets['doc_count'], 0, ',', '.').'</span>';
                        echo '</li>';
                    }
            echo '</ul>';
             echo '
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
                </div>
            </div></div></div>
            ';
            echo '</div></div>';
        }
        echo '</li>';
        

    }

    public function facetExistsField($fileName,$field, $size, $field_name, $sort, $sort_type, $get_search, $open = false)
    {
        global $url_base;

        if (isset($get_search["page"])) {
            unset($get_search["page"]);
        }

        $query = $this->query;
        $query["aggs"]["field_not_exists"]["missing"]["field"] = "$field.keyword";
        $query["aggs"]["field_exists"]["filter"]["exists"]["field"] = "$field.keyword";

        $response = Elasticsearch::search(null, 0, $query);


        echo '<a href="#" class="list-group-item list-group-item-action active">'.$field_name.'</a>';
        echo '<ul class="list-group list-group-flush">';

        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<a href="'.$fileName.'?search=_exists_:'.$field.'" style="color:#0040ff;font-size: 90%">Está preenchido</a>
        <span class="badge badge-primary badge-pill">'.number_format($response["aggregations"]["field_exists"]["doc_count"], 0, ',', '.').'</span>';
        echo '</li>';

        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<a href="'.$fileName.'?search=-_exists_:'.$field.'" style="color:#0040ff;font-size: 90%">Não está preenchido</a>
        <span class="badge badge-primary badge-pill">'.number_format($response["aggregations"]["field_not_exists"]["doc_count"], 0, ',', '.').'</span>';
        echo '</li>';

        echo '</ul>';
    }    

    public function rebuild_facet($field,$size,$nome_do_campo)
    {
        $query = $this->query;
        $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
        if (isset($sort)) {
            $query["aggs"]["counts"]["terms"]["order"]["_count"] = "desc";
        }
        $query["aggs"]["counts"]["terms"]["size"] = $size;

        $response = Elasticsearch::elasticSearch(null, 0, $query);

        echo '<li class="uk-parent">';
        echo '<a href="#" style="color:#333">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            $termCleaned = str_replace("&", "*", $facets['key']);
            echo '<li">';
            echo "<div uk-grid>";
            echo '<div class="uk-width-2-3 uk-text-small" style="color:#333">';
            echo '<a href="admin/autoridades.php?term=&quot;'.$termCleaned.'&quot;" style="color:#0040ff;font-size: 90%">'.$termCleaned.' ('.number_format($facets['doc_count'], 0, ',', '.').')</a>';
            echo '</div>';
            echo '</li>';
        };
        echo   '</ul>
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
            $query["aggs"]["ranges"]["range"]["ranges"][0]["to"] = 0.5;
            $query["aggs"]["ranges"]["range"]["ranges"][1]["from"] = 0.5;
            $query["aggs"]["ranges"]["range"]["ranges"][1]["to"] = 1;
            $query["aggs"]["ranges"]["range"]["ranges"][2]["from"] = 1;
            $query["aggs"]["ranges"]["range"]["ranges"][2]["to"] = 2;
            $query["aggs"]["ranges"]["range"]["ranges"][3]["from"] = 2;
            $query["aggs"]["ranges"]["range"]["ranges"][3]["to"] = 5;
            $query["aggs"]["ranges"]["range"]["ranges"][4]["from"] = 5;
            $query["aggs"]["ranges"]["range"]["ranges"][4]["to"] = 10;
            $query["aggs"]["ranges"]["range"]["ranges"][5]["from"] = 10;
            $query["aggs"]["ranges"]["range"]["ranges"][5]["to"] = 50;
            $query["aggs"]["ranges"]["range"]["ranges"][6]["from"] = 50;
            $query["aggs"]["ranges"]["range"]["ranges"][6]["to"] = 100;
            $query["aggs"]["ranges"]["range"]["ranges"][7]["from"] = 100;
        }

        //$query["aggs"]["counts"]["terms"]["size"] = $size;

        $response = Elasticsearch::search(null, 0, $query);

        $result_count = count($response["aggregations"]["ranges"]["buckets"]);

        if ($result_count > 0) {
            echo '<a href="#" class="list-group-item list-group-item-action active">'.$field_name.'</a>';
            echo '<ul class="list-group list-group-flush">';
            foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
                $facets_array = explode("-", $facets['key']);
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<a href="'.$fileName.'?&search='.$field.':['.$facets_array[0].' TO '.$facets_array[1].']" style="color:#0040ff;font-size: 90%">Intervalo '.$facets['key'].'</a>
                <span class="badge badge-primary badge-pill">'.number_format($facets['doc_count'],0,',','.').'</span>';
                echo '</li>';
            };
            echo '</ul>';
        }


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


class UI {
   
    static function pagination($page, $total, $limit, $url = null)
    {
        echo '<nav>';
        echo '<ul class="list-group list-group-horizontal">';
        if ($page == 1) {
            echo '<li class="list-group-item w-25 disabled">Anterior</li>';
        } else {
            $_GET["page"] = $page-1 ;
            echo '<li class="list-group-item w-25"><a href="'.(!empty($url) ? $url : ''.basename($_SERVER["SCRIPT_FILENAME"], '').'').'?'.http_build_query($_GET).'"> Anterior</a></li>';
        }
        echo '<li class="list-group-item w-25 disabled">Página '.number_format($page, 0, ',', '.') .'</li>';
        echo '<li class="list-group-item w-25 disabled">'.number_format($total, 0, ',', '.') .'&nbsp;registros</li>';
        if ($total/$limit > $page) {
            $_GET["page"] = $page+1;
            echo '<li class="list-group-item w-25"><a href="'.(!empty($url) ? $url : ''.basename($_SERVER["SCRIPT_FILENAME"], '').'').'?'.http_build_query($_GET).'"> Próxima</a></li>';
        } else {
            echo '<li class="list-group-item w-25 disabled">Próxima</li>';
        }
        echo '</ul>';
        echo '</nav>';
    }
}



class Authorities {

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
        $url = ''.$tematresWebServicesUrl.'?task=fetch&arg='.$clean_term.'&output=json';
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
            $url = ''.$tematresWebServicesUrl.'?task=fetchTerm&arg='.$term_key.'&output=json';
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
            $url_country = ''.$tematresWebServicesUrl.'?task=fetchUp&arg='.$term_key.'&output=json';
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
        $url = ''.$dspaceRest.'/rest/login';

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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
        $policyArray["action"] =  $policyAction;
        $policyArray["epersonId"] =  "";
        $policyArray["groupId"] =  $groupId;
        $policyArray["resourceId"] =  $bitstreamID;
        $policyArray["resourceType"] =  $resourceType;
        $policyArray["rpDescription"] =  "";
        $policyArray["rpName"] =  "";
        $policyArray["rpType"] =  $rpType;
        $policyArray["startDate"] =  "$embargoStartDate";
        $policyArray["endDate"] =  "$embargoEndDate";
        $data_string = json_encode($policyArray);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$dspaceRest/rest/bitstreams/$bitstreamID/policy");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        if (!empty($DSpaceCookies)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
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

    static function buildDC($cursor,$sysno)
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

Class Work
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

Class LattesWork extends Work
{
    public $lattes;
    public $vinculo;
    public function __construct()
    {
        parent::__construct();
        $this->source = "Base Lattes";
    }
}

Class TrabalhosEmEventosLattes extends LattesWork
{
    public $detalhamentoDoTrabalho;
    function __construct()
    {
        parent::__construct();
    }
}

?>
