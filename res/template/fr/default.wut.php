<?php echo $this->generate(file_get_contents("res/inc/top.inc.php")); ?>
<h1>Page par défaut</h1>
<p>Vous utilisez l'AgencysFramework et son moteur de template WorkupTemplate v<?php  echo $GLOBALS["workupData"]["version"];  ?>: <?php  if($GLOBALS["workupData"]["version"] == "2") { echo $this->generate("<b>Avanced WorkupTemplate (AWUT)</b>"); } ?></p>
<?php echo $this->generate(file_get_contents("res/inc/bottom.inc.php")); ?>