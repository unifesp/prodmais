<?php

/* Exibir erros - Use somente durante os testes */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_FILES['file'])) {

  $fh = fopen($_FILES['file']['tmp_name'], 'r+');
  while (($IDLattes = fgetcsv($fh, 108192, "\t")) !== FALSE) {
    echo "Processando $IDLattes[0]"  . PHP_EOL;
    $xmlLattes = file_get_contents('http://200.133.208.25/api/proxy/' . $IDLattes[0] . '');
    $myfile = fopen("../../data/$IDLattes[0].xml", "w") or die("Unable to open file!");
    fwrite($myfile, $xmlLattes);
    fclose($myfile);
  }
}