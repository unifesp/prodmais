<?php
class Tag
{
  static function cloud($arr, $ppg = null, $author = null)
  {
    $buf = '';

    // if (array_key_exists('first', $search_array))


    foreach ($arr as $t) {
      if (isset($ppg)) {
        $buf = "$buf 
        <form action=\"result.php\" method=\"post\">
        <input type=\"hidden\" name=\"search\" value=\"\">
        <input type=\"hidden\" name=\"filter[]\" value=\"vinculo.ppg_nome:$ppg\">
        <input type=\"hidden\" name=\"filter[]\" value=\"about:{$t['category']}\">
        <input class=\"tag\" type=\"submit\" value=\"{$t['category']}\" data-weight={$t['amount']} ></form>
        ";
      }
      if (isset($author)) {
        $buf = "$buf 
        <form action=\"result.php\" method=\"post\">
        <input type=\"hidden\" name=\"search\" value=\"\">
        <input type=\"hidden\" name=\"filter[]\" value=\"vinculo.nome:$author\">
        <input type=\"hidden\" name=\"filter[]\" value=\"about:{$t['category']}\">
        <input class=\"tag\" type=\"submit\" value=\"{$t['category']}\" data-weight={$t['amount']} ></form>
        ";
      }
    }
    unset($t);

    echo ('<a class="u-skip" href=”#skip-tagcloud”>Pular nuvens de palavras</a>');

    echo ("
        <ul class='tag-cloud' role='navigation' aria-label='Tags mais usadas'>
          $buf
        </ul>
      ");

    echo ('<span class="u-skip" id="skip-tagcloud”"></span>');
  }
}