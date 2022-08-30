<?php
require 'inc/config.php';
session_start();
$errorMsg = "";
if (isset($_SESSION["login"])) {
    $validUser = $_SESSION["login"] === true;
}
if (isset($_POST["username"])) {
    $validUser = $_POST["username"] == $login_user && $_POST["password"] == $login_password;
    if (!$validUser) $errorMsg = "Usuário ou senha inválidos.";
    else $_SESSION["login"] = true;
}
if (isset($validUser) && ($validUser)) {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>

    <?php
    require 'inc/config.php';
    require 'inc/meta-header.php';
    require 'inc/functions.php';
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Tiago Murakami">
    <title>Login</title>

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        html,
        body {
            height: 100%;
        }

        body {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-align: center;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }

        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }

        .form-signin .checkbox {
            font-weight: 400;
        }

        .form-signin .form-control {
            position: relative;
            box-sizing: border-box;
            height: auto;
            padding: 10px;
            font-size: 16px;
        }

        .form-signin .form-control:focus {
            z-index: 2;
        }

        .form-signin input[type="text"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
</head>

<body class="text-center">
    <form class="form-signin" method="post" action="login.php">
        <h1 class="h3 mb-3 font-weight-normal">Login</h1>
        <label for="inputUser" class="sr-only">Usuário</label>
        <input type="text" id="inputUser" class="form-control" name="username" placeholder="Usuário" required autofocus>
        <label for="inputPassword" class="sr-only">Senha</label>
        <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Senha" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
        <p class="mt-5 mb-3 text-muted"><?= $errorMsg ?></p>
    </form>
</body>

</html>