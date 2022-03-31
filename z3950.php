<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php 
            include('inc/config.php');             
            include('inc/meta-header.php');
            include('inc/functions.php');
            
            if(!empty($_SESSION['oauthuserdata'])) { 
                store_user($_SESSION['oauthuserdata'],$client);
            }
        ;      

        ?> 
        <title>Conversor do arquivo JSON da Plataforma Lattes para o ElasticSearch - Coleta Produção USP</title>
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
        
        
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
            <div class="uk-width-medium-1-1">
            <?php include('inc/navbar.php'); ?>
            <br/><br/>    

<?php 

if (!empty($_GET["isbn"])) {
    $query = $_GET["isbn"];
    $type = "isbn";
}

if (!empty($_GET["sysno"])) {
    $query = $_GET["sysno"];
    $type = "sysno";
}

if (!empty($_GET["title"])) {
    $query = [];
    $query[0] = '"'.$_GET["title"].'"';
    if (!empty($_GET["author"])) {
        $query[1] = '"'.$_GET["author"].'"';
    }
    if (!empty($_GET["year"])) {
        $query[2] = '"'.$_GET["year"].'"';
    }
    $type = "title";
}

//Consultas                
    z3950::query_z3950($query, "dedalus.usp.br:9991/usp01", "USP - DEDALUS", $type); 
    z3950::query_z3950($query, "biblioteca2.senado.gov.br:9991/sen01", "Biblioteca do Senado", $type);
    z3950::query_z3950($query, "lx2.loc.gov:210/LCDB", "Library of Congress", $type);
    z3950::query_z3950($query, "marte.biblioteca.upm.es:2200", "Universidade Politécnica de Madrid", $type);
    z3950::query_z3950($query, "sirsi.library.utoronto.ca:2200", "University of Toronto", $type);
    z3950::query_z3950($query, "ilsz3950.nlm.nih.gov:7091/VOYAGER", "U.S. National Library of Medicine (NLM)", $type);
    z3950::query_z3950($query, "168.176.5.96:9991/SNB01", "Universidade Nacional de Colombia(UNAL)", $type);
    z3950::query_z3950($query, "athena.biblioteca.unesp.br:9992/uep01", "UNESP - Athena", $type);
    z3950::query_z3950($query, "library.ox.ac.uk:210/aleph", "University of Oxford", $type);
    z3950::query_z3950($query, "zcat.libraries.psu.edu:2200", "Penn State University", $type);
    z3950::query_z3950($query, "ringding.law.yale.edu:210/INNOPAC", "Yale Law School", $type);
    z3950::query_z3950($query, "newton.lib.cam.ac.uk:7090/VOYAGER", "University of Cambridge", $type);
?>
            <hr class="uk-grid-divider">
            
<?php include('inc/footer.php'); ?>

        </div>
        
        
<?php include('inc/offcanvas.php'); ?>
          </div>  
        
    </body>
</html>                