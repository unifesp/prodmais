<?php
    session_start();
    if ($_SESSION["login"] === true) {

    } else {
        header("Location: ../login.php");
        die();
    }
?>
<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<head>
    <?php
    require '../inc/config.php';
    require '../inc/meta-header.php';
    require '../inc/functions.php';
    ?>
    <title>Administração</title>
    <link rel="stylesheet" href="../inc/css/style.css" />

</head>

<body>
    <!-- NAV -->
    <?php require '../inc/navbar.php'; ?>
    <!-- /NAV -->
    <main class="main">
        <h1>Administração</h1>
    </main>

    <?php include('../inc/footer.php'); ?>
</body>

</html>