<!DOCTYPE html>
<?php
    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    require 'inc/config.php';
    require 'inc/functions.php';

    if (!empty($_REQUEST["lattesID"])) {
    // delete user from cv

    $resultDeleteCV = Elasticsearch::delete($_REQUEST["lattesID"], $index_cv);
    var_dump($resultDeleteCV);

    // delete all works

    $query["query"]["bool"]["filter"][0]["term"]["vinculo.lattes_id.keyword"] = $_REQUEST["lattesID"];
    $resultDeleteByQuery = Elasticsearch::deleteByQuery($query);
    var_dump($resultDeleteByQuery);

    } else {
        echo "Não foi enviado um lattesID";
    }
