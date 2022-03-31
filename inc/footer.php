<footer class="myfooter">


    <div class="myfooter-contacts">
        <a class="myfooter-link" href="inclusao.php">
            Inclusão</a>
    </div>

    <div class="myfooter-logos">
        <?php if (file_exists("inc/images/logos/sti-branco.svg")) : ?>
            <a href="https://sti.unifesp.br/" target="_blank"><img class="myfooter-logo" src="inc/images/logos/sti-branco.svg" alt="STI"></a>
        <?php endif ?>
        <?php if (file_exists("../inc/images/logos/sti-branco.svg")) : ?>
            <a href="https://sti.unifesp.br/" target="_blank"><img class="myfooter-logo" src="../inc/images/logos/sti-branco.svg" alt="STI"></a>
        <?php endif ?>
        <?php if (file_exists("inc/images/logos/unifesp-branco.svg")) : ?>
            <a href="https://unifesp.br/" target="_blank"><img class="myfooter-logo" src="inc/images/logos/unifesp-branco.svg" alt="Unifesp"></a>
        <?php endif ?>
        <?php if (file_exists("../inc/images/logos/unifesp-branco.svg")) : ?>
            <a href="https://unifesp.br/" target="_blank"><img class="myfooter-logo" src="../inc/images/logos/unifesp-branco.svg" alt="Unifesp"></a>
        <?php endif ?>
    </div>


</footer>