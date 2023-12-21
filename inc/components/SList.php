<?php
// require 'inc/functions.php';

class SList
{
  static function bulletGeneric($tipo)
  {
    $img = '';
    switch ($tipo) {
      case "professional":
        $img = 'working';
        break;
      case "orientation":
        $img = 'orientation';
        break;
      case "managing":
        $img = 'managment';
        break;
      case "research":
        $img = 'research';
        break;
      case "formation":
        $img = 'academic';
        break;
      case "ppg":
        $img = 'ppg-logo';
        break;
      default:
        $img = 'defaultProduction';
    }
    return "<i class='i i-$img s-list-ico' title='$tipo'></i>";
  }

  static function bulletIntelectualProduction($tipo)
  {
    $img = '';
    switch ($tipo) {
      case "Artigo publicado":
        $img = 'articlePublished';
        break;
      case "article":
        $img = 'articlePublished';
        break;
      case "editorial":
        $img = 'articlePublished';
        break;
      case "Capítulo de livro publicado":
        $img = 'chapter';
        break;
      case "book-chapter":
        $img = 'chapter';
        break;
      case "Livro publicado ou organizado":
        $img = 'book';
        break;
      case "book":
        $img = 'book';
        break;
      case "Patente":
        $img = 'patent';
        break;
      case "Software":
        $img = 'softwares';
        break;
      case "Textos em jornais de notícias/revistas":
        $img = 'papers';
        break;
      case "Trabalhos em eventos":
        $img = 'event';
        break;
      case "Tradução":
        $img = 'book';
        break;
      case "dissertation":
        $img = 'book';
        break;
      default:
        $img = 'defaultProduction';
    }
    return "<i class='i i-$img s-list-ico' title='$tipo'></i>";
  }


  // === Only for Slist::IntelectualProduction === //
  static function doiRendered($url)
  {
    return "
        <a class='t t-a d-icon-text' href='https://doi.org/$url' target='blank'>
        <img class='i-doi' src='inc/images/logos/doi.svg' title='doi' alt='doi' />
        </a>";
  }

  // === Only for Slist::IntelectualProduction === //
  static function doiCleaned($doi)
  {
    if (!isset($url)) {
      $url = "";
    }
    if (substr($doi, 0, 3) === "10.") {
      return $doi;
    } elseif (substr($url, 0, 5) === "[doi:") {
      $cleandoi = str_replace(['[doi:', ']'], '', $url);
      return $cleandoi;
    }
  }

  // === Only for Slist::IntelectualProduction === //
  static function urlRendered($url)
  {
    if (substr($url, 0, 3) === "10.") {
      return "
      <a class='t t-a d-icon-text' href='https://doi.org/$url' target='blank'> 
        <i class='i i-link i-link u-ml-05' title='Conteúdo completo' alt='Conteúdo completo'></i>
        Conteúdo completo
      </a>";
    } elseif (substr($url, 0, 5) === "[doi:") {
      $cleandoi = str_replace(['[doi:', ']'], '', $url);
      $doi = 'https://doi.org/' . $cleandoi . '';
      return "
      <a class='t t-a d-icon-text' href='$doi' target='blank'> 
        <i class='i i-link i-link u-ml-05' title='Conteúdo completo' alt='Conteúdo completo'></i>
        Conteúdo completo
      </a>";
    } elseif (substr($url, 0, 5) === "[http") {
      $cleanurl = str_replace(['[', ']'], '', $url);
      return "
      <a class='t t-a d-icon-text' href='$cleanurl' target='blank'> 
        <i class='i i-link i-link u-ml-05' title='Conteúdo completo' alt='Conteúdo completo'></i>
        Conteúdo completo
      </a>";
    } else {
      return "
      <a class='t t-a d-icon-text' href='$url' target='blank'> 
        <i class='i i-link i-link u-ml-05' title='Conteúdo completo' alt='Conteúdo completo'></i>
        Conteúdo completo
      </a>";
    }
  }
  // === Only for Slist::IntelectualProduction === //
  static function issnRendered($url)
  {
    return "
      <a class='t t-a d-icon-text'>
        &nbsp;&nbsp;&nbsp;&nbsp;ISSN: $url
      </a>";
  }


  static function date($start, $end)
  {
    $buf = '';
    if (!empty($start)) {
      !empty($end) ? $buf = "$start a $end" : $buf = "Desde $start";
      return  $buf;
    } else {
      !empty($end) ? $buf = "Concluído em $end"  : $buf = "";
      return  $buf;
    }
  }

  static function tags($tags)
  {
    $buf = '<ul class="s-list-tags">';
    if (is_array($tags)) {
      foreach ($tags as $t) {
        $buf = "$buf <li class='s-list-tag'>$t</li>";
      }
      $buf = "$buf </ul>";
    }
    return $buf;
  }

  static function genericItem(
    $type,
    $itemName,
    $itemNameLink = '',
    $itemInfoA = '',
    $itemInfoB = '',
    $itemInfoC = '',
    $itemInfoD = '',
    $itemInfoE = '',
    $authors = '',
    $tags = '',
    $yearStart = '',
    $yearEnd = ''
  ) {

    $bullet = SList::bulletGeneric($type);
    $date = SList::date($yearStart, $yearEnd);

    if (!empty($itemNameLink)) {
      $header = "<p class='t t-b'><a class='t-a' href='$itemNameLink'> $itemName </a></p>";
    } else {
      $header = "<p class='t t-b'> $itemName </a></p>";
    }

    !empty($itemInfoB) && !empty($itemInfoC) ? $sepataror = ', ' : $sepataror = '';
    !empty($authors) ? $aut = "<b class='t-subItem'>Autores: </b> $authors </p>" : $aut = '';
    !empty($tags) ? $tagsRender = Slist::tags($tags) : $tagsRender = '';

    echo ("
    <li class='s-nobullet'>
			<div class='s-list'>
				<div class='s-list-bullet'>
					$bullet
				</div>

				<div class='s-list-content'>
					<p class='t t-b'>$header</p>
					<p class='ty'>$itemInfoA</p>

					<p class='t t-gray'>$itemInfoB</p>
					<p class='t t-gray'>$itemInfoC</p>
					<p class='t t-gray'>$itemInfoD</p>
					<p class='t t-gray'>$itemInfoE</p>
          $tagsRender
					<p class='t t-gray'>$aut</p>		
					<p class='t t-gray'>$date</p>			
				</div>
			</div>
    </li>
    ");
  }

  static function IntelectualProduction(
    $type,
    $name,
    $authors,
    $doi,
    $url,
    $issn,
    $refName,
    $refVol,
    $refFascicle,
    $refPage,
    $datePublished,
    $cited_by_count,
    $aurorasdg
  ) {

    $bullet = SList::bulletIntelectualProduction($type);
    $authorsRendered = implode('; ', $authors);

    !empty($doi) ? $doiCleaned = SList::doiCleaned($doi) : $doiCleaned = '';
    !empty($doiCleaned) ? $doiRendered = SList::doiRendered($doiCleaned) : $doiRendered = '';
    !empty($url) ? $urlRendered = SList::urlRendered($url) : $urlRendered = '';
    !empty($issn) ? $issnRendered = SList::issnRendered($issn) : $issnRendered = '';
    !empty($cited_by_count) ? $cited_by_count : $cited_by_count = 0;
    !empty($aurorasdg) ? $aurorasdg = $aurorasdg : $aurorasdg = '';
    !empty($refName) ? $refName = $refName : '';
    !empty($refVol) ? $refVol = ", v. $refVol" : '';
    !empty($refFascicle) ? $refFascicle = ", n. $refFascicle" : '';
    !empty($refPage) ? $refPage = ", p. $refPage" : '';
    !empty($datePublished) ? $datePublished = $datePublished : '';
    $name_cleaned = htmlspecialchars($name, ENT_QUOTES);

    if (!empty($aurorasdg['predictions'])) {
      foreach ($aurorasdg['predictions'] as $prediction) {
        if ($prediction['prediction'] > 0.5) {
          $sdg = $prediction['sdg']['name'];
          $score = $prediction['prediction'];
          $sdgRendered = '<p class="t t-light">ODS: ' . $sdg . ' - Probabilidade: ' . $score . '</p>';
        } else {
          if (empty($sdgRendered)) {
            $sdgRendered = '';
          }
        }
      }
    } else {
      $sdgRendered = '';
    }

    echo ("
			<li class='s-list-2'>
				<div class='s-list-bullet'>
					$bullet
				</div>

				<div class='s-list-content'>
					<p class='t t-b t-md'>$name</p>
					<p class='t t-b t-md'><i>$type</i></p>
					<p class='t-gray'><b class='t-subItem'>Autores: </b> $authorsRendered </p>
					
					<p class='d-linewrap t-gray'>
            $doiRendered
            $urlRendered
            $issnRendered
					</p>
          <p class='mt-3'>
          $datePublished
          </p>

          <p class='t t-light'>
          Fonte: $refName $refVol $refFascicle $refPage
          </p>

          ");
    if ($cited_by_count > 0) {
      echo "<p class='t t-light'>Quantidade de citações obtidas no OpenAlex: $cited_by_count</p>";
    }
    echo ("
          <p class='mt-3'>
            <a href='https://plu.mx/plum/a/?doi=$doiCleaned' class='plumx-details'></a>
          </p>
          <div class='sdg-wheel' data-wheel-height='100' data-model='elsevier-sdg-multi' data-text='$name_cleaned'></div>
          <p class='mt-3'>
            $sdgRendered
          </p>

        </div>
      </li>
    ");
  }
}
