<?php

require 'inc/config.php';
require 'inc/functions.php';

if (isset($_FILES['file'])) {

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');

    while (($row = fgetcsv($fh, 108192, "\t")) !== false) {
        $CPF = sprintf('%011d', $row[0]);
        $lattesID = getLattesIDbyCPF($CPF);
        echo "$CPF-$lattesID";
        echo "<br />";
        unset($row);
    }
    fclose($fh);
}

function getLattesIDbyCPF($CPF)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://200.133.208.25/api/proxy_cpf/' . $CPF . '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    return $output;
    curl_close($ch);
}
