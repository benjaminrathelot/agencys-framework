<?php echo $this->generate(file_get_contents("res/inc/top.inc.php")); ?>
<h1>La page demandée est introuvable</h1>
<p>Cliquez sur le lien suivant pour retourner à la page d'accueil : <a href="<?php  echo $GLOBALS["SiteDomain"];  ?>">Retour</a>.</p>
<?php echo $this->generate(file_get_contents("res/inc/bottom.inc.php")); ?>