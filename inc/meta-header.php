<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="<?php echo $url_base; ?>/inc/images/favicon-64x.png" type="image/x-icon">
<link rel="stylesheet" href="inc/sass/main.css" />

<?php if(file_exists('inc/js/vue.js')): ?>
<!-- Development -->
<script src="inc/js/vue.js"></script><!-- https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js -->
<!--Production -->
<!--
<script src="inc/js/vue.min.js"></script>
-->
<?php else: ?>
<!-- Development -->
<script src="../inc/js/vue.js"></script><!-- https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js -->
<!--Production -->
<!--
<script src="inc/js/vue.min.js"></script>
-->
<?php endif; ?>


<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
  integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script> -->