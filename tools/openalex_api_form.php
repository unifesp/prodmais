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
    <title><?php echo $branch ?> - Importar dados da API do OpenAlex</title>


</head>

<?php
require '../inc/config.php';

$username = $login_user;
$password = $login_password;

if (isset($_POST['submit'])) {
    if ($_POST['username'] == $username && $_POST['password'] == $password) {
?>

<body class="c-wrapper-body">
    <main class="c-wrapper-container">
        <div class="c-wrapper-paper">
            <div class="c-wrapper-inner">
                <h1 class="t t-h1"><?php echo $branch; ?> - Importar dados da API do OpenAlex</h1>
                <form class="p-inclusao-form" action="openalex_api_import.php" method="post" accept-charset="utf-8"
                    enctype="multipart/form-data" title="Formulário de importação de registros do OpenAlex">
                    <div class="input-group">
                        <textarea class="c-input" id="openalex_expression" rows="3" type="text"
                            placeholder="Colar API do OpenAlex" name="openalex_expression"></textarea>
                    </div>
                    <div class="input-group-append">
                        <button class="c-btn" type="submit">Incluir</button>
                    </div>
                </form>
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