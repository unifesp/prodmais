<?php

require 'inc/config.php';
require 'inc/functions.php';

$identificador = $_REQUEST['ID_CURSO'];
if (isset($_REQUEST['ID_CURSO'])) {
    $doc_ppg_array['doc']['ID_CURSO'] = $_REQUEST['ID_CURSO'];
}
if (isset($_REQUEST['COD_CAPES'])) {
    $doc_ppg_array['doc']['COD_CAPES'] = $_REQUEST['COD_CAPES'];
}
if (isset($_REQUEST['NOME_CAMPUS'])) {
    $doc_ppg_array['doc']['NOME_CAMPUS'] = $_REQUEST['NOME_CAMPUS'];
}
if (isset($_REQUEST['SIGLA_CAMARA'])) {
    $doc_ppg_array['doc']['SIGLA_CAMARA'] = $_REQUEST['SIGLA_CAMARA'];
}
if (isset($_REQUEST['NOME_CAMARA'])) {
    $doc_ppg_array['doc']['NOME_CAMARA'] = $_REQUEST['NOME_CAMARA'];
}
if (isset($_REQUEST['NOME_PPG'])) {
    $doc_ppg_array['doc']['NOME_PPG'] = $_REQUEST['NOME_PPG'];
}
if (isset($_REQUEST['INI_PPG'])) {
    $doc_ppg_array['doc']['INI_PPG'] = $_REQUEST['INI_PPG'];
}
if (isset($_REQUEST['PPG_SITE'])) {
    $doc_ppg_array['doc']['PPG_SITE'] = $_REQUEST['PPG_SITE'];
}
if (isset($_REQUEST['PPG_EMAIL'])) {
    $doc_ppg_array['doc']['PPG_EMAIL'] = $_REQUEST['PPG_EMAIL'];
}
if (isset($_REQUEST['PRODMAIS_DSPACE'])) {
    $doc_ppg_array['doc']['PRODMAIS_DSPACE'] = $_REQUEST['PRODMAIS_DSPACE'];
}
if (isset($_REQUEST['PRODMAIS_DATAVERSE'])) {
    $doc_ppg_array['doc']['PRODMAIS_DATAVERSE'] = $_REQUEST['PRODMAIS_DATAVERSE'];
}
if (isset($_REQUEST['DT_INI_COORD'])) {
    $doc_ppg_array['doc']['DT_INI_COORD'] = $_REQUEST['DT_INI_COORD'];
}
if (isset($_REQUEST['CONCEITO_CAPES'])) {
    $doc_ppg_array['doc']['CONCEITO_CAPES'] = $_REQUEST['CONCEITO_CAPES'];
}
if (isset($_REQUEST['NIVEL'])) {
    $doc_ppg_array['doc']['NIVEL'] = $_REQUEST['NIVEL'];
}
if (isset($_REQUEST['NOME_COORDENADOR'])) {
    $doc_ppg_array['doc']['NOME_COORDENADOR'] = $_REQUEST['NOME_COORDENADOR'];
}
$doc_ppg_array["doc_as_upsert"] = true;

$resultado_ppg = Elasticsearch::update($identificador, $doc_ppg_array, $index_ppg);