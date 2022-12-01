<?php

class Categories
{
  static function list($response, $field)
  {
    echo '<ul class="c-categories">';
    foreach ($response["aggregations"]["group_by_state"]["buckets"] as $facets) {
      echo '<li class="c-categories__item">';
      echo '<form action="result.php" method="post">';
      echo '<input type="hidden" name="search" value="">';
      echo '<input type="hidden" name="filter[]" value="' . $field . ':' . $facets['key'] . '">';
      echo '<div class="d-between">';
      echo '<input class="c-categories__text" type="submit" value="' . $facets['key'] . '" />';
      echo '<span class="c-categories__number">' . number_format($facets['doc_count'], 0, ',', '.') . '</span>';
      echo '</div>';
      echo '</form>';
      echo '</li>';
    }
    echo '</ul>';
  }
}