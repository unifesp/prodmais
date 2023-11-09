<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
<style>
.form-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
</style>


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?php echo $url_base; ?>/inc/images/favicon-64x.png" type="image/x-icon">
    <link rel="stylesheet" href="../inc/sass/main.css" />

    <?php
    require '../inc/functions.php';
    ?>
    <title><?php echo $branch ?> - Status de APIs</title>


</head>

<?php
require '../inc/config.php';

$username = $login_user;
$password = $login_password;

if (isset($_POST['submit'])) {
    if ($_POST['username'] == $username && $_POST['password'] == $password) {
?>

<?php

        // Get status of APIs

        // Total records in database

        $params = [];
        $params["index"] = $index;
        //$params["body"] = $query;
        $cursorTotal = $client->count($params);
        $total_records = $cursorTotal["count"];


        // Total records with DOI

        $query["query"]["exists"]["field"] = "doi.keyword";
        $params["body"] = $query;
        $cursorTotalDOI = $client->count($params);
        $total_records_with_DOI = $cursorTotalDOI["count"];

        // Total records with OpenAlex

        $query["query"]["exists"]["field"] = "openalex";
        $params["body"] = $query;
        $cursorTotalOpenAlex = $client->count($params);
        $total_records_with_OpenAlex = $cursorTotalOpenAlex["count"];

        // Total records with DOI without OpenAlex


        $paramsDOIWithoutOpenAlex = [];
        $paramsDOIWithoutOpenAlex["index"] = $index;
        $queryDOIWithoutOpenAlex["query"]["query_string"]["query"] = '_exists_:doi doi:1* -_exists_:openalex';
        $paramsDOIWithoutOpenAlex["body"] = $queryDOIWithoutOpenAlex;
        $cursorTotalDOIWithoutOpenAlex = $client->count($paramsDOIWithoutOpenAlex);
        $total_records_with_DOI_without_OpenAlex = $cursorTotalDOIWithoutOpenAlex["count"];

        // Total records without DOI with OpenAlex

        $paramsOpenAlexWithoutDOI = [];
        $paramsOpenAlexWithoutDOI["index"] = $index;
        $queryOpenAlexWithoutDOI["query"]["query_string"]["query"] = '-_exists_:doi -_exists_:openalex_doi';
        $paramsOpenAlexWithoutDOI["body"] = $queryOpenAlexWithoutDOI;
        $cursorTotalOpenAlexWithoutDOI = $client->count($paramsOpenAlexWithoutDOI);
        $total_records_with_OpenAlex_without_DOI = $cursorTotalOpenAlexWithoutDOI["count"];

        ?>

<body class="c-wrapper-body">
    <main class="c-wrapper-container">
        <div class="c-wrapper-paper">
            <div class="c-wrapper-inner">
                <h1 class="t t-h1"><?php echo $branch; ?> - Status de APIs</h1>

                <h2 class='t t-h3'>Total de registros no banco de dados: <?php echo $total_records; ?></h2>

                <h2 class='t t-h3'>Total de registros no banco de dados com DOI: <?php echo $total_records_with_DOI; ?>
                </h2>

                <h2 class='t t-h3'>Total de registros no banco de dados com DOI e OpenAlex:
                    <?php echo $total_records_with_OpenAlex; ?>, faltando
                    <?php echo $total_records_with_DOI - $total_records_with_OpenAlex; ?>
                </h2>

                <h2 class='t t-h3'>Total de registros no banco de dados sem DOI e sem OpenAlex:
                    <?php echo $total_records_with_OpenAlex_without_DOI; ?></h2>
                <hr />

                <h2 class="t t-h3">Enriquecimento de registros</h2>
                <ul>
                    <li><a href="openalex_get_record.php?size=10">Coletar 10 registros no Openalex com DOI (É
                            necessário
                            repetir este procedimento até zerar a quantidade de registros)</a></li>
                    <li><a href="openalex_get_doi.php?size=5">Tentar obter o DOI no OpenAlex de registros que não
                            possuem
                            DOI, com base no título.
                            (É necessário repetir este procedimento até zerar a quantidade de registros, mas esse é
                            um
                            procedimento demorado, que deve ser realizado com poucos registros de cada vez)</a></li>
                </ul>


            </div>
        </div>
    </main>
</body>

</html>




<?php
    } else {
        echo "Usuário não encontrado";
    }
} else {
?>

<body>
    <div class="form-container">
        <form class="p-inclusao-form" method="post">
            <h1><?php echo $branch ?> - Login</h1>
            Usuário: <input class="c-input--sm" type="text" name="username" /><br />
            Senha: <input class="c-input--sm" type="password" name="password" /><br />
            <input class="c-btn" type='submit' name='submit' value="Login" />
        </form>
    </div>
</body>
<?php
}
?>