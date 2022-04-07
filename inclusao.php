<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php
    require 'inc/config.php';
    require 'inc/meta-header.php';
    require 'inc/functions.php';
    ?>
    <title><?php echo $branch ?> - Inclusão</title>

    <link rel="stylesheet" href="inc/css/style.css" />

</head>

<body>

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->


    <div class="jumbotron">
        <div class="container bg-light p-5 rounded mt-5">
            <h1 class="display-5"><?php echo $branch; ?> - Inclusão</h1>
            <p><?php echo $branch_description; ?></p>

            <?php isset($error_connection_message) ? print_r($error_connection_message) : "" ?>

            <form class="m-3" action="lattes_xml_to_elastic_dedup.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                <legend>Inserir um XML do Lattes</legend>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">XML Lattes</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fileXML" aria-describedby="fileXML" name="file">
                        <label class="custom-file-label" for="fileXML">Escolha o arquivo</label>
                    </div>
                    <input type="text" placeholder="TAG" class="form-control" name="tag">
                    <input type="text" placeholder="Núm. funcional" class="form-control" name="numfuncional">
                    <input type="text" placeholder="Unidade" class="form-control" name="unidade">
                </div>
                <div class="input-group">
                    <input type="text" placeholder="Departamento" class="form-control" name="departamento">
                    <input type="text" placeholder="Divisão" class="form-control" name="divisao">
                    <input type="text" placeholder="Seção" class="form-control" name="secao">
                    <input type="text" placeholder="Nome do PPG" class="form-control" name="ppg_nome">
                    <input type="text" placeholder="Tipo de vínculo" class="form-control" name="tipvin">
                </div>
                <div class="input-group">
                    <input type="text" placeholder="Genero" class="form-control" name="genero">
                    <input type="text" placeholder="Nível" class="form-control" name="desc_nivel">
                    <input type="text" placeholder="Curso" class="form-control" name="desc_curso">
                    <input type="text" placeholder="Campus" class="form-control" name="campus">
                    <input type="text" placeholder="Gestora" class="form-control" name="desc_gestora">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Incluir</button>
                    </div>
                </div>
            </form>

            <form class="m-3" action="doi_to_elastic.php" method="get">
                <legend>Inserir um DOI de artigo que queira incluir (sem http://doi.org/)</legend>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">DOI</span>
                    </div>
                    <input type="text" placeholder="Insira um DOI" class="form-control" name="doi" data-validation="required">
                    <input type="text" placeholder="TAG para formar um grupo" class="form-control" name="tag">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Enviar</button>
                    </div>
                </div>
            </form>

            <form class="m-3" action="wos_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                <legend>Enviar um arquivo da Web of Science (UTF-8, separado por tabulações)</legend>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Web of Science</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fileXML" aria-describedby="fileXML" name="file">
                        <label class="custom-file-label" for="fileXML">Escolha o arquivo</label>
                    </div>
                    <input type="text" placeholder="TAG para formar um grupo" class="form-control" name="tag">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </div>
            </form>

            <form class="m-3" action="incites_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                <legend>Enviar um arquivo do INCITES (CSV)</legend>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">INCITES</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fileXML" aria-describedby="fileXML" name="file">
                        <label class="custom-file-label" for="fileXML">Escolha o arquivo</label>
                    </div>
                    <input type="text" placeholder="TAG para formar um grupo" class="form-control" name="tag">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </div>
            </form>

            <form class="m-3" action="scopus_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                <legend>Enviar um arquivo do Scopus (CSV - All available information)</legend>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Scopus</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fileXML" aria-describedby="fileXML" name="file">
                        <label class="custom-file-label" for="fileXML">Escolha o arquivo</label>
                    </div>
                    <input type="text" placeholder="TAG para formar um grupo" class="form-control" name="tag">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </div>
            </form>

            <form class="m-3" action="scival_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                <legend>Enviar um arquivo do SCIVAL (CSV - All available information)</legend>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">SCIVAL</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fileXML" aria-describedby="fileXML" name="file">
                        <label class="custom-file-label" for="fileXML">Escolha o arquivo</label>
                    </div>
                    <input type="text" placeholder="TAG para formar um grupo" class="form-control" name="tag">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </div>
            </form>
            <div class="m-2">&nbsp;</div>    


            <hr/>
            <h2 class="display-5 mt-3">Excluir índices</h2>
            <div class="alert alert-danger" role="alert">
                Excluir todos os dados! Atenção, essa tarefa é irreversível! <a href="tools/delete_all_indexes.php">Clique aqui</a>
            </div>

        </div>
    </div>

    <?php include('inc/footer.php'); ?>


</body>

</html>