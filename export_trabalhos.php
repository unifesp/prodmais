<!DOCTYPE html>
<?php
    include('inc/config.php'); 
    include('inc/functions.php');

    if (!empty($_POST)) {
        foreach ($_POST as $key=>$value) {            
            $var_concluido["doc"]["concluido"] = $value;
            $var_concluido["doc"]["doc_as_upsert"] = true; 
            Elasticsearch::update($key, $var_concluido);
        }
        sleep(6);
        header("Refresh:0");
    }
    
    

    $result_get = get::analisa_get($_GET);
    $query = $result_get['query'];
    $limit = 20;
    $page = $result_get['page'];
    $skip = $result_get['skip'];

    $query['sort'] = [
	    ['datePublished.keyword' => ['order' => 'desc']],
    ];
    
    $params = [];
    $params["index"] = $index;
    $params["type"] = $type;
    $params["size"] = $limit;
    $params["from"] = $skip;
    $params["body"] = $query;
    
    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    /*pagination - start*/
    $get_data = $_GET;    
    /*pagination - end*/      

?>
<html>
    <head>
        <?php
            include('inc/meta-header.php'); 
        ?>        
        <title>Lattes - Resultado da busca por trabalhos</title>
        <script src="inc/uikit/js/components/accordion.min.js"></script>
        <script src="inc/uikit/js/components/pagination.min.js"></script>
        <script src="inc/uikit/js/components/datepicker.min.js"></script>
        <script src="inc/uikit/js/components/tooltip.min.js"></script>
        
        <script src="http://cdn.jsdelivr.net/g/filesaver.js"></script>
        <script>
              function SaveAsFile(t,f,m) {
                    try {
                        var b = new Blob([t],{type:m});
                        saveAs(b, f);
                    } catch (e) {
                        window.open("data:"+m+"," + encodeURIComponent(t), '_blank','');
                    }
                }
        </script>         
        
    </head>
    <body>
        <div class="uk-container">
            <?php include('inc/navbar.php'); ?>        
	        <div class="uk-width-1-1@s uk-width-1-1@m">	    
                <nav class="uk-navbar-container uk-margin" uk-navbar>
                    <div class="nav-overlay uk-navbar-left">
                        <a class="uk-navbar-item uk-logo" uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#">Clique para uma nova pesquisa</a>        
                    </div>
                    <div class="nav-overlay uk-navbar-right">
                        <a class="uk-navbar-toggle" uk-search-icon uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
                    </div>

                    <div class="nav-overlay uk-navbar-left uk-flex-1" hidden>
                    <div class="uk-navbar-item uk-width-expand">
                        <form class="uk-search uk-search-navbar uk-width-1-1">
                        <input type="hidden" name="fields[]" value="name">
                        <input type="hidden" name="fields[]" value="author.person.name">
                        <input type="hidden" name="fields[]" value="authorUSP.name">
                        <input type="hidden" name="fields[]" value="about">
                        <input type="hidden" name="fields[]" value="description"> 	    
                        <input class="uk-search-input" type="search" name="search[]" placeholder="Nova pesquisa" autofocus>
                        </form>
                    </div>
                        <a class="uk-navbar-toggle" uk-close uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
                    </div>
                </nav>
	    </div>

	    <div class="uk-width-1-1@s uk-width-1-1@m">
	    
		    <?php if (!empty($_SERVER["QUERY_STRING"])) : ?>
		    				    
			<p class="uk-margin-top" uk-margin>
				<a class="uk-button uk-button-default uk-button-small" href="index.php">Começar novamente</a>	
				<?php 
				
					if (!empty($_GET["search"])){
                        foreach($_GET["search"] as $filters) {
                            $filters_array[] = $filters;
                            $name_field = explode(":",$filters);	
                            $filters = str_replace($name_field[0].":","",$filters);				
                            $diff["search"] = array_diff($_GET["search"],$filters_array);						
                            $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                            echo '<a class="uk-button uk-button-default uk-button-small" href="http://'.$url_push.'">'.$filters.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                            unset($filters_array); 	
                        }
                    }	
	
				?>
				
			</p>
		    <?php endif;?> 
	    
	    </div>	
        <div class="uk-grid-divider" uk-grid>
        <div class="uk-width-1-4@s uk-width-2-6@m">  
        
            <div class="uk-panel uk-panel-box">
                
                <hr>
                <h3 class="uk-panel-title">Refinar meus resultados</h3>    
                <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
                    <hr>
                <?php
                    $facets = new facets();
                    $facets->query = $query;

                    if (!isset($_GET["search"])) {
                        $_GET["search"] = null;                                    
                    }                       
                    
                    $facets->facet("natureza",100,"Natureza",null,"_term",$_GET["search"]);
                    $facets->facet("tipo",100,"Tipo de material",null,"_term",$_GET["search"]);
                    $facets->facet("tag",100,"Tag",null,"_term",$_GET["search"]);
                    
                    $facets->facet("autores.nomeCompletoDoAutor",100,"Nome completo do autor",null,"_term",$_GET["search"]);
                    $facets->facet("lattes_ids",100,"Número do lattes",null,"_term",$_GET["search"]);
                    $facets->facet("numfuncional",100,"Número funcional",null,"_term",$_GET["search"]);
                    $facets->facet("unidade",100,"Unidade",null,"_term",$_GET["search"]);
                    
                    echo '<hr><li>Informações da publicação</li>';
                    $facets->facet("pais",200,"País de publicação",null,"_term",$_GET["search"]);
                    $facets->facet("ano",120,"Ano de publicação","desc","_term",$_GET["search"]);
                    $facets->facet("idioma",40,"Idioma",null,"_term",$_GET["search"]);
                    $facets->facet("meioDeDivulgacao",100,"Meio de divulgação",null,"_term",$_GET["search"]);
                    $facets->facet("palavras_chave",100,"Palavras-chave",null,"_term",$_GET["search"]);
                    $facets->facet("agencia_de_fomento",100,"Agências de fomento",null,"_term",$_GET["search"]);
                    $facets->facet("citacoes_recebidas",100,"Citações recebidas",null,"_term",$_GET["search"]);
                    
                    echo '<hr><li>Área do conhecimento</li>';
                    $facets->facet("area_do_conhecimento.nomeGrandeAreaDoConhecimento",100,"Nome da Grande Área do Conhecimento",null,"_term",$_GET["search"]);
                    $facets->facet("area_do_conhecimento.nomeDaAreaDoConhecimento",100,"Nome da Área do Conhecimento",null,"_term",$_GET["search"]);
                    $facets->facet("area_do_conhecimento.nomeDaSubAreaDoConhecimento",100,"Nome da Sub Área do Conhecimento",null,"_term",$_GET["search"]);
                    $facets->facet("area_do_conhecimento.nomeDaEspecialidade",100,"Nome da Especialidade",null,"_term",$_GET["search"]);
                    
                    echo '<hr><li>Eventos</li>';
                    $facets->facet("trabalhoEmEventos.classificacaoDoEvento",100,"Classificação do evento",null,"_term",$_GET["search"]); 
                    $facets->facet("trabalhoEmEventos.nomeDoEvento",100,"Nome do evento",null,"_term",$_GET["search"]);
                    $facets->facet("trabalhoEmEventos.cidadeDoEvento",100,"Cidade do evento",null,"_term",$_GET["search"]);
                    $facets->facet("trabalhoEmEventos.anoDeRealizacao",100,"Ano de realização do evento",null,"_term",$_GET["search"]);
                    $facets->facet("trabalhoEmEventos.tituloDosAnaisOuProceedings",100,"Título dos anais",null,"_term",$_GET["search"]);
                    $facets->facet("trabalhoEmEventos.isbn",100,"ISBN dos anais",null,"_term",$_GET["search"]);
                    $facets->facet("trabalhoEmEventos.nomeDaEditora",100,"Editora dos anais",null,"_term",$_GET["search"]);
                    $facets->facet("trabalhoEmEventos.cidadeDaEditora",100,"Cidade da editora",null,"_term",$_GET["search"]);

                    echo '<hr><li>Mídias sociais e blogs</li>';
                    $facets->facet("midiaSocialWebsiteBlog.formacao_maxima",100,"Formação máxima - Blogs e mídias sociais",null,"_term",$_GET["search"]);
                    
                    echo '<hr><li>Periódicos</li>';
                    $facets->facet("artigoPublicado.tituloDoPeriodicoOuRevista",100,"Título do periódico",null,"_term",$_GET["search"]);

                    echo '<hr><li>Concluído</li>';
                    $facets->facet("concluido",100,"Concluído",null,"_term",$_GET["search"]);
                    $facets->facet("bdpi",100,"Está na FONTE?",null,"_term",$_GET["search"]);

                ?>
                </ul>
                    <?php if(!empty($_SESSION['oauthuserdata'])): ?>
                        <h3 class="uk-panel-title uk-margin-top">Informações administrativas</h3>
                        <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
                        <hr>
                        <?php         

                        ?>
                        </ul>
                    <?php endif; ?>
                <hr>
                <form class="uk-form">
                <fieldset>
                    <legend>Limitar datas</legend>

                    <script>
                        $( function() {
                        $( "#limitar-data" ).slider({
                        range: true,
                        min: 1900,
                        max: 2030,
                        values: [ 1900, 2030 ],
                        slide: function( event, ui ) {
                            $( "#date" ).val( "ano:[" + ui.values[ 0 ] + " TO " + ui.values[ 1 ] + "]" );
                        }
                        });
                        $( "#date" ).val( "ano:[" + $( "#limitar-data" ).slider( "values", 0 ) +
                        " TO " + $( "#limitar-data" ).slider( "values", 1 ) + "]");
                        } );
                    </script>
                    <p>
                    <label for="date">Selecionar período de tempo:</label>
                    <input type="text" id="date" readonly style="border:0; color:#f6931f; font-weight:bold;" name="search[]">
                    </p>        
                    <div id="limitar-data" class="uk-margin-bottom"></div>        
                    <?php if(!empty($_GET["search"])): ?>
                        <?php foreach($_GET["search"] as $search_expression): ?>
                            <input type="hidden" name="search[]" value="<?php echo str_replace('"','&quot;',$search_expression); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="uk-form-row"><button class="uk-button-primary">Limitar datas</button></div>
                </fieldset>        
                </form>
                <hr>     
                        
            </div>
        </div>
                
        <div class="uk-width-3-4@s uk-width-4-6@m">
        
            <!-- Navegador de resultados - Início -->
            <div class="uk-child-width-expand@s uk-grid-divider" uk-grid>
                <div>
                    <ul class="uk-pagination">
                        <?php if ($page == 1) :?>
                            <li><a href="#"><span class="uk-margin-small-right" uk-pagination-previous></span>Anterior</a></li>
                        <?php else :?>
                            <?php $get_data["page"] = $page-1 ; ?>
                            <li><a href="export_trabalhos.php?<?php echo http_build_query($get_data); ?>"><span class="uk-margin-small-right" uk-pagination-previous></span> Anterior</a></li>
                        <?php endif; ?>
                    </ul>    
                </div>
                <div>
                    <p class="uk-text-center"><?php print_r(number_format($total,0,',','.'));?> registros</p>
                </div>
                <div>
                    <ul class="uk-pagination">
                        <?php if ($total/$limit > $page): ?>
                            <?php $get_data["page"] = $page+1 ; ?>
                            <li class="uk-margin-auto-left"><a href="export_trabalhos.php?<?php echo http_build_query($get_data); ?>">Próxima <span class="uk-margin-small-left" uk-pagination-next></span></a></li>
                        <?php else :?>
                            <li class="uk-margin-auto-left"><a href="#">Próxima <span class="uk-margin-small-left" uk-pagination-next></span></a></li>
                        <?php endif; ?>
                    </ul>                            
                </div>
            </div>
            <!-- Navegador de resultados - Fim -->                    
                    
            <hr class="uk-grid-divider">           
                    
            <div class="uk-width-1-1 uk-margin-top uk-description-list-line">                        
                <ul class="uk-list uk-list-divider">

                <table class="uk-table">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th>Titulo</th>
                            <th>Autores</th>
                            <th>Ano</th>
                            <th>Idioma</th>
                        </tr>
                    </thead>
                    <tbody>
                        

                   
                <?php foreach ($cursor["hits"]["hits"] as $r) : ?>
                    <?php if (empty($r["_source"]['ano'])) {
                        $r["_source"]['ano'] = "";
                    }
                    ?>
                    <?php if (!empty($r["_source"]['autores'])) : ?>
                    <?php foreach ($r["_source"]['autores'] as $autores) {
                        $authors_array[]='<a href="result.php?search[]=autores.nomeCompletoDoAutor.keyword:&quot;'.$autores["nomeCompletoDoAutor"].'&quot;">'.$autores["nomeCompletoDoAutor"].'</a>';
                    } 
                    $array_aut = implode(", ",$authors_array);
                    unset($authors_array);                   
                    ?>
                    <?php endif; ?>                        
                        <tr>
                            <td><?php echo ($r["_source"]['titulo']);?></td>
                            <td><?php echo ($array_aut);?></td>
                            <td><?php echo $r["_source"]['ano']; ?></td>
                            <td><?php echo $r["_source"]['idioma']; ?></td>
                        </tr>
 

                    <?php endforeach;?>
                                                       
                                                    </tbody>
                                                </table> 

                    </ul>
                    </div>
                    <hr class="uk-grid-divider">
                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-1-2"><p class="uk-text-center"><?php print_r($total);?> registros</p></div>
                        <div class="uk-width-1-2">
                            <ul class="uk-pagination" data-uk-pagination="{items:<?php print_r($total);?>,itemsOnPage:<?php print_r($limit);?>,displayedPages:3,edges:1,currentPage:<?php print_r($page-1);?>}"></ul>                         
                        </div>
                    </div>                   
                    

                    
                </div>
            </div>
            <hr class="uk-grid-divider">
<?php include('inc/footer.php'); ?>          
        </div>
                


        <script>
        $('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
            var url = window.location.href.split('&page')[0];
            window.location=url +'&page='+ (pageIndex+1);
        });
        </script>    

<?php include('inc/offcanvas.php'); ?>         
        
    </body>
</html>