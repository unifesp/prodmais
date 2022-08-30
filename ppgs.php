<!DOCTYPE html>
<html lang="pt-br" dir="ltr">

<?php
$ppgs = '
{
  "campus": [
    {
      "nome": "BAIXADA SANTISTA",
      "unidades": [
        {
          "nome": "Instituto de Saúde e Sociedade",
          "programas": [
            "Alimentos, Nutrição e Saúde",
            "Bioprodutos e Bioprocessos",
            "Ciências do Movimento Humano e Reabilitação",
            "Interdisciplinar em Ciências da Saúde",
            "Saúde da Família",
            "Serviço Social e Políticas Sociais"
          ]
        },
        {
          "nome": "Instituto do Mar",
          "programas": [
            "Biodiversidade e Ecologia Marinha e Costeira",
            "Interdisciplinar em Ciência e Tecnologia do Mar"
          ]
        }
      ]
    },
    {
      "nome": "DIADEMA",
      "unidades": [
        {
          "nome": "Instituto de Ciências Ambientais, Químicas e Farmacêuticas",
          "programas": [
            "Análise Ambiental Integrada",
            "Biologia Química",
            "Ciências Farmacêuticas",
            "Ecologia e Evolução",
            "Engenharia Química",
            "Ensino de Ciências e Matemática",
            "Matemática em Rede Nacional (Profmat-DM)",
            "Química - Ciência e Tecnologia da Sustentabilidade"
          ]
        }
      ]
    },
    {
      "nome": "GUARULHOS",
      "unidades": [
        {
          "nome": "Escola de Filosofia, Letras e Ciências Humanas",
          "programas": [
            "Ciências Sociais",
            "Educação",
            "Educação e Saúde na Infância e Adolescência",
            "Ensino de História",
            "Filosofia",
            "História",
            "História da Arte",
            "Letras"
          ]
        }
      ]
    },
    {
      "nome": "OSASCO",
      "unidades": [
        {
          "nome": "Escola Paulista de Política, Economia e Negócios",
          "programas": [
            "Economia e Desenvolvimento"
          ]
        }
      ]
    },
    {
      "nome": "SÃO JOSÉ DOS CAMPOS",
      "unidades": [
        {
          "nome": "Instituto de Ciência e Tecnologia",
          "programas": [
            "Biotecnologia",
            "Ciência da Computação",
            "Engenharia Biomédica",
            "Engenharia e Ciência de Materiais",
            "Inovação Tecnológica",
            "Matemática Pura e Aplicada",
            "Matemática em Rede Nacional (Profmat-SJC)",
            "Pesquisa Operacional"
          ]
        }
      ]
    },
    {
      "nome": "SÃO PAULO",
      "unidades": [
        {
          "nome": "Escola Paulista de Enfermagem",
          "programas": [
            "Enfermagem",
            "Ensino em Ciências da Saúde"
          ]
        },
        {
          "nome": "Escola Paulista de Medicina",
          "programas": [
            "Biologia Estrutural e Funcional",
            "Cirurgia Translacional",
            "Ciência Cirúrgica Interdisciplinar",
            "Ciência, Tecnologia e Gestão Aplicadas à Regeneração Tecidual",
            "Ciências Biológicas (Biologia Molecular)",
            "Ciências da Saúde Aplicada ao Esporte e à Atividade Física",
            "Ciências da Saúde Aplicadas à Reumatologia",
            "Distúrbios da Comunicação Humana (Fonoaudiologia)",
            "Farmacologia",
            "Gastroenterologia",
            "Gestão e Informática em Saúde",
            "Infectologia",
            "Medicina (Cardiologia)",
            "Medicina (Endocrinologia e Metabologia)",
            "Medicina (Ginecologia)",
            "Medicina (Hematologia e Oncologia)",
            "Medicina (Nefrologia)",
            "Medicina (Obstetrícia)",
            "Medicina (Otorrinolaringologia)",
            "Medicina (Pneumologia)",
            "Medicina (Radiologia Clínica)",
            "Medicina (Urologia)",
            "Medicina Translacional",
            "Microbiologia e Imunologia",
            "Neurologia - Neurociências",
            "Nutrição",
            "Oftalmologia e Ciências Visuais",
            "Patologia",
            "Pediatria e Ciências Aplicadas à Pediatria",
            "Psicobiologia",
            "Psiquiatria e Psicologia Médica",
            "Saúde Baseada em Evidências",
            "Saúde Coletiva",
            "Tecnologia, Gestão e Saúde Ocular"
          ]
        }
      ]
    }
  ]
}';

class ListPPGs {
  static function listAll($data) {
    $arr = json_decode($data);
    $campus = $arr -> campus;
   
    foreach( $campus as $c ) {
      echo '<h2 class="t t-h2 u-my-2">' . $c -> nome .'<h2>';

      foreach( $c -> unidades as $unidade)
      {
        $programas = str_replace(array('{', '}'), array('[',']'), $unidade -> programas);

        echo '
        <details class="p-ppgs-item">
          <summary class="p-ppgs-item-header">'
             . $unidade -> nome .
          '</summary>
        ';
          
        foreach($programas as $p) 
          SList::genericItem(
            $type = 'ppg',
            $itemName = $p,
            $itemNameLink = '',
            $itemInfoA = '',
            $itemInfoB = 'CAPES 1234566890',
            $itemInfoC = '',
            $itemInfoD = '',
            $itemInfoE = '',
            $authors = '',
            $tags = '',
            $yearStart = '',
            $yearEnd = ''
        );
        
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
  require 'components/SList.php';
  require 'components/TagCloud.php';
  require '_fakedata.php';
  ?>
  <meta charset="utf-8" />
  <title><?php echo $branch; ?> Programas de Pós-Graduação </title>
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <meta name="description" content="Prodmais Unifesp." />
  <meta name="keywords" content="Produção acadêmica, lattes, ORCID" />
  <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
  <link rel="stylesheet" href="sass/main.css" />
</head>

<body class="c-wrapper-body">
  <?php if(file_exists('inc/google_analytics.php')){include 'inc/google_analytics.php';}?>

  <?php require 'inc/navbar.php'; ?>

  <main class="c-wrapper-container">
    <div class="c-wrapper-paper">
      <div class="c-wrapper-inner">
        <h1 class=" t t-h1 u-mb-2">Programas de Pós-Graduação</h1>

        <div class="p-ppg-container">
          <!-- <div class="p-ppg-tags">
            <?php echo $bufTags ?>
          </div> -->

          <div class="p-ppg-main">
            <?php 
              ListPPGs::listAll($ppgs);
            ?>
          </div>
        </div>

      </div>
    </div>
  </main>

  <?php include('inc/footer.php'); ?>
</body>

</html>