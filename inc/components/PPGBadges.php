<?php
class PPGBadges {
  static function capes(
    $rate,
    $title
  ) {
    echo 
    "<div class='d-icon-text c-badge c-badge-line'>
      <i class='i i-decagram c-badge-i' title='title' alt='imagem $title'>$rate</i>
      <p class='t t-light c-badge-text2'><b> $title</b></p>
    </div>";
  }

  static function students(
    $rate,
    $title,
    $ico
  ) {
    echo 
    "<div class='c-badge c-badge-col'>
      <i class='i i-$ico c-badge-i' title='title' alt='imagem $title'></i>
      <p class='c-badge-text1'>$rate</p>
      <p class='t t-light'><b> $title</b></p>
    </div>";
  }
}
?>