<?php

/* Exibir erros - Use somente durante os testes */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// ============== CONFIGURAÇÕES ============== //

/* Endereço do server, sem http:// */
$hosts = ['localhost:9200'];
//$elasticsearch_user = "elastic";
//$elasticsearch_password = "";

/* Endereço base */
$url_base = "http://localhost/prodmais";


/* Configurações do Elasticsearch */
$index = "prodmais";
$index_cv = "prodmaiscv";
$index_ppg = "prodmaisppg";
$index_projetos = "prodmaisprojetos";

/* Login */
$login_user = "admin";
$login_password = "admin";


// ============== CUSTOMIZAÇÃO ============== //

// Definir Instituição
$instituicao = "Instituição";
$branch = "Prodmais Instituição";
$branch_description = "Descrição";
$facebook_image = "";
$slogan = 'Slogan';

$mostrar_instituicao = true;
$mostrar_area_concentracao = true;
$mostrar_existe_doi = true;
$mostrar_openalex = true;
$mostrar_link_dashboard = true;

// Tema
// $theme = 'Prodmais';
// $theme = 'Waterbeach';
// $theme = 'Tomaton';
$theme = 'Blueberry';