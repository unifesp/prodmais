<?php

require 'inc/config.php'; 
require 'inc/functions.php';

print_r($_REQUEST);
echo "<br/><br/>";

$cookies = DSpaceREST::loginREST();

print_r($cookies);

if (isset($_REQUEST['_id'])){
    $cursor = Elasticsearch::get($_REQUEST['_id'], null);
}

if (isset($_REQUEST["createRecord"])) {
    if ($_REQUEST["createRecord"] == "true") {
        
        $dataString = DSpaceREST::buildDC($cursor, $_REQUEST['_id']);

        echo "<br/><br/>";
        print_r($dataString);
        echo "<br/><br/>";

        $resultCreateItemDSpace = DSpaceREST::createItemDSpace($dataString, $dspaceCollection, $cookies);
        echo "<br/><br/>";
        print_r($resultCreateItemDSpace);
        
        // echo "<script type='text/javascript'>
        // $(document).ready(function(){  
        //         //Reload the page
        //         window.location = window.location.href;
        // });
        // </script>";
    } 
}

DSpaceREST::logoutREST($cookies);


?>
