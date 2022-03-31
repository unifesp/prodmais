<?php 

require 'inc/config.php';             
require 'inc/functions.php';

if (isset($_GET["doi"])) {
    DadosExternos::query_doi($_GET["doi"], $_GET["tag"]);
    sleep(5); 
    echo '<script>window.location = \'result.php?filter[]=doi:"'.trim($_GET["doi"]).'"\'</script>';
} else {
    echo '<p>Favor inserir um DOI</p>';
}



?>