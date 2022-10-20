<?php

  include '../../inc/config.php';
  try {
    $dbh = new PDO ("dblib:charset=UTF-8;host=$hostname:$port;dbname=$dbname","$username","$pw");
  } catch (PDOException $e) {
    echo "Failed to get DB handle: " . $e->getMessage() . "\n";
    exit;
  }
  //$stmt = $dbh->prepare("SELECT * FROM V_PESSOA_LATTES WHERE codpes = '90029' ORDER BY nompes");
  $stmt = $dbh->prepare("SELECT * FROM V_PESSOA_LATTES");
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    unlink("zip.zip");
    unlink("curriculo.xml");
    //print_r($row);
    $zip = $row['imgarqxml'];
    //print_r($zip);
    $zipFile = fopen("zip.zip", "w");
    fwrite($zipFile, $zip); 
    fclose($zipFile);
    $zipFile = "zip.zip";
    $fileInsideZip = ''.$row['idfpescpq'].'.xml';
    $content = file_get_contents("zip://$zipFile#$fileInsideZip");
    $xmlFile = fopen("curriculo.xml", "w");
    fwrite($xmlFile, $content); 
    fclose($xmlFile);
    if (!is_null($row["nomabvset"])) {
      $output = shell_exec('curl -X POST -F "file=@'.__DIR__.'/curriculo.xml" -F "codpes='.$row["codpes"].'" -F "unidade=ECA" -F "tag='.trim($row["nomabvset"]).'" -F "tipvin='.$row["tipvinext"].'" '.$url_base.'/lattes_xml_to_elastic.php');
    } else {
      $output = shell_exec('curl -X POST -F "file=@'.__DIR__.'/curriculo.xml" -F "codpes='.$row["codpes"].'" -F "unidade=ECA" -F "tag='.trim($row["nomcur"]).'" -F "tipvin='.$row["tipvinext"].'" '.$url_base.'/lattes_xml_to_elastic.php');
    }
    
    //echo "<pre>$output</pre>";
    //var_dump($row);
  }
  unlink("zip.zip");
  unlink("curriculo.xml");
  unset($dbh); unset($stmt);
?>
