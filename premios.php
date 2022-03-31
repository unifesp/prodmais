<?php

require 'inc/config.php';
require 'inc/functions.php';

if (isset($argv[1])) {
    $file = file_get_contents('http://200.133.208.25/api/proxy/' . $argv[1] . '');
    $curriculo = simplexml_load_string($file);
}


// Premios - Títulos

if (isset($curriculo->{'DADOS-GERAIS'}->{'PREMIOS-TITULOS'})) {
    foreach ($curriculo->{'DADOS-GERAIS'}->{'PREMIOS-TITULOS'}->{'PREMIO-TITULO'} as $premioTitulo) {
        $premioTitulo = get_object_vars($premioTitulo);
        echo 
            $argv[1] . '|||' . 
            (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-COMPLETO'} . '|||' . 
            $premioTitulo['@attributes']["NOME-DO-PREMIO-OU-TITULO"] . '|||' .
            $premioTitulo['@attributes']["NOME-DA-ENTIDADE-PROMOTORA"] . '|||' .
            $premioTitulo['@attributes']["ANO-DA-PREMIACAO"] . '|||' .
            $premioTitulo['@attributes']["NOME-DO-PREMIO-OU-TITULO-INGLES"]
            .PHP_EOL;
    }
}