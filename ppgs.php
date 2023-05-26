<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php


require 'inc/config.php';

$limit = 300;
//$page = $result_post['page'];
$params = [];
$params["index"] = $index_ppg;
//$params["body"] = $result_post['query'];
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];
//$result_post['query']["sort"]["nome_completo.keyword"]["unmapped_type"] = "long";
//$result_post['query']["sort"]["nome_completo.keyword"]["missing"] = "_last";
//$result_post['query']["sort"]["nome_completo.keyword"]["order"] = "asc";
//$params["body"] = $result_post['query'];
$params["size"] = $limit;
//$params["from"] = $result_post['skip'];
$cursor = $client->search($params);

//echo "<pre>".print_r($cursor['hits']['hits'], true)."</pre>";

foreach ($cursor['hits']['hits'] as $campus) {
  //echo "<pre>".print_r($campus, true)."</pre>";
  
  $ppgs_array['campus'][$campus['_source']['NOME_CAMPUS']]['nome'] = $campus['_source']['NOME_CAMPUS'];
  $ppgs_array['campus'][$campus['_source']['NOME_CAMPUS']]['unidades'][$campus['_source']['NOME_CAMARA']][] = $campus['_source'];
  

}
//echo "<pre>".print_r($ppgs_array, true)."</pre>";

class ListPPGs {
  static function listAll($data) {
    $campus = $data['campus'];
   
    foreach($campus as $key => $value ) {
      //echo "<pre>".print_r($key, true)."</pre>";
      //echo "<pre>".print_r($value, true)."</pre>";
      echo '<h2 class="t t-h2 u-my-20">' . $key .'<h2>';

      foreach($value['unidades'] as $key_unidades => $value_unidades) {

        //echo "<pre>".print_r($value_unidades, true)."</pre>";

        echo '
        <details class="p-ppgs-item">
          <summary class="p-ppgs-item-header">'
             . $key_unidades .
          '</summary>
        ';

        

        //$programas = str_replace(array('{', '}'), array('[',']'), $unidade -> programas);


          
        foreach($value_unidades as $p) {

          //echo "<pre>".print_r($p, true)."</pre>";

          SList::genericItem(
            $type = 'ppg',
            $itemName = '<a href="ppg.php?ID='.$p['ID_CURSO'].'">'.$p['NOME_PPG'].'</a>',
            $itemNameLink = '',
            $itemInfoA = 'Código CAPES: ' . $p['COD_CAPES'],
            $itemInfoB = 'E-mail: ' . $p['PPG_EMAIL'],
            $itemInfoC = 'Site: <a href="'.$p['PPG_SITE'].'">'.$p['PPG_SITE'].'</a>',
            $itemInfoD = 'Nível: ' . $p['NIVEL'],
            $itemInfoE = 'Conceito CAPES: ' . $p['CONCEITO_CAPES'],
            $authors = '',
            $tags = '',
            $yearStart = $p['INI_PPG'],
            $yearEnd = ''
          );
        }
        
        echo  '</details>';
        
      }
    }

  }
}

?>

<head>
  <?php
  require 'inc/config.php';
  require 'inc/meta-header.php';
  require 'inc/functions.php';
  require 'inc/components/SList.php';
  require 'inc/components/TagCloud.php';
  require '_fakedata.php';
  ?>
  <meta charset="utf-8" />
  <title><?php echo $branch; ?> Programas de Pós-Graduação </title>
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <meta name="description" content="Prodmais Unifesp." />
  <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />

</head>

<body data-theme="<?php echo $theme; ?>" class="c-wrapper-body">
  <?php if(file_exists('inc/google_analytics.php')){include 'inc/google_analytics.php';}?>

  <?php require 'inc/navbar.php'; ?>

  <main class="c-wrapper-container">
    <div class="c-wrapper-paper">
      <div class="c-wrapper-inner">
        <h1 class=" t t-h1 u-mb-20">Programas de Pós-Graduação</h1>

        <div class="p-ppg-container">
          <!-- <div class="p-ppg-tags">
            <?php echo $bufTags ?>
          </div> -->

          <div class="p-ppg-main">
            <?php 
              ListPPGs::listAll($ppgs_array);
            ?>
          </div>
        </div>

      </div>
    </div>
  </main>

  <?php include('inc/footer.php'); ?>
</body>

</html>