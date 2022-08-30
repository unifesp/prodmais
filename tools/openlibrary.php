<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php 
            include('inc/config.php');             
            include('inc/meta-header.php');
            include('inc/functions.php');

        ?> 
        <title>Conversor da API do OpenLibrary</title>     
        
    </head>

    <body>
        <?php require 'inc/navbar.php'; ?>
        <div class="container">        
        

            <?php 

            if (!empty($_GET["isbn"])) {
                echo "<br/><br/><br/><br/><br/>";
                //$query_isbn = $_GET["isbn"];
                //$type = "isbn";
                $resultISBN = DadosExternos::query_openlibrary($_GET["isbn"]);
                if (!empty($resultISBN)) {
                    print("<pre>".print_r($resultISBN, true)."</pre>");

                    echo "<br/><br/>";

                    foreach ($resultISBN as $recordISBN) {
                        $record["name"] = $recordISBN["details"]["title"];

                    }

                    echo "<br/><br/>";

                    print("<pre>".print_r($record, true)."</pre>");

                    $jsonRecord = json_encode($record);
                    echo '                    
                        <form class="form-signin" method="post" action="editor/index.php">
                            <input type="hidden" id="record" name="record" value="'.urlencode($jsonRecord).'">
                            <button class="btn btn-warning" type="submit">Editar antes de exportar</button>
                        </form>                    
                    ';

                } else {
                    echo "ISBN não foi encontrado na Base OpenLibrary";
                }
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

            ?>

        </div>
            
        <?php include('inc/footer.php'); ?>
        
    </body>
</html>                