<?php
// require 'inc/functions.php';

class Production
{

  static function bullet($tipo)
  {
    $img = '';
    switch ($tipo) {
      case "Artigo publicado":
        $img = 'articlePublished';
        break;
      case "Capítulo de livro publicado":
        $img = 'chapter';
        break;
      case "Livro publicado ou organizado":
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
      default:
        $img = 'defaultProduction';
    }
    return "<i class='i i-$img s-list-ico' title='$tipo'></i>";
  }


  static function doiRendered($url)
  {
    return '
      <a class="t t-a d-icon-text" href="https://doi.org/' . $url . '" target="blank">
        <img class="i-doi" src="inc/images/logos/doi.svg" title="doi" alt="doi" />
        </a>';
  }

  static function urlRendered($url)
  {
    if (str_contains($url, '[')) {
      $url = str_replace('[', '', $url);
    }

    if (str_contains($url, ']')) {
      $url = str_replace(']', '', $url);
    }

    return '
        <a class="t t-a d-icon-text" href="' . $url . '" target="blank"> 
          <i class="i i-link i-link u-ml-05" title="Conteúdo completo" alt="Conteúdo completo"></i>
          Conteúdo completo
        </a>';
  }

  static function issnRendered($issn)
  {
    return "ISSN: $issn";
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
    $evento,
    $datePublished,
    $id
  )
  {

    $bullet = Production::bullet($type);
    $authorsRendered = implode('; ', $authors);

    !empty($doi) ? $doiRendered = Production::doiRendered($doi) : $doiRendered = '';
    !empty($url) ? $urlRendered = Production::urlRendered($url) : $urlRendered = '';
    !empty($issn) ? $issnRendered = Production::issnRendered($issn) : $issnRendered = '';
    !empty($refName) ? $refName = $refName : '';
    !empty($refVol) ? $refVol = ", v. $refVol" : '';
    !empty($refFascicle) ? $refFascicle = ", n. $refFascicle" : '';
    !empty($refPage) ? $refPage = ", p. $refPage" : '';

    // (!empty($datePublished) && !empty($id)) ? $query = DadosInternos::queryProdmais($name, $datePublished, $id) : $query = '';

    echo ("
			<div class='s-list'>
				<div class='s-list-bullet'>
					$bullet
				</div>

				<div class='s-list-content'>
					<p class='t-b'>$name<i> — $type </i ></p>
					<p class='t-gray'><b class='t-subItem'>Autores: </b> $authorsRendered </p>
					
					<div class='d-linewrap t-gray'>
            $doiRendered
            $urlRendered	
            				
					</div>
          $datePublished
					
					<p class='t t-light'>
						Fonte: $refName $refVol $refFascicle $refPage
					</p>
          <p class='t t-light'>
            $issnRendered
          </p>
					
				</div>
			</div>
    ");
  }
}