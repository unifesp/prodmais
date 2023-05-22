<?php include "inc/config.php" ?>

<footer class="sitefooter">

  <?php if($instituicao == "UNIFESP"): ?>

  <div class="sitefooter-contacts">
    <a class="sitefooter-link" target="_blank" href="https://atendimento.unifesp.br/">
      Fale conosco</a>
    <!--<a class="sitefooter-link" target="_blank" href="https://atendimento.unifesp.br/">
      Política de privacidade</a> -->
    <a class="sitefooter-link" target="_blank" href="https://forms.gle/2QRcqg2YfxMvEqVX9">
      Relate erros</a>
  </div>

  <?php else: ?>

    <a class="sitefooter-link" href="inclusao.php">Inclusão</a>

  <?php endif ?>

  <p class="developed-by">Software livre desenvolvido originalmente pela Universidade Federal de São Paulo</p>
  <a href="https://unifesp.br/" target="_blank" class="sitefooter-link" title="Visite o site da Unifesp">
    <i class="i i-unifesp" alt="Logo da Unifesp"></i>
  </a>
</footer>