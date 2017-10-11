<?php echo $this->generate(file_get_contents("res/inc/top.inc.php")); ?>
<h1>Formulaire de connexion par défaut</h1>
<form action='<?php  echo $GLOBALS["SiteDomain"];  ?>LoginEng' method='post'>
<p style='color:red'><?php  include($this->engineDir."ErrorMessage".".engine.php");if(isset($_engine)){ $_engine->run();echo $this->generate($_engine->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; } ?></p><br />
<form role="form">
  <div class="form-group">
    <label for="exampleInputEmail1">Identifiant</label>
    <input type="text" name="ident" class="form-control" id="exampleInputEmail1" placeholder="">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Mot de passe</label>
    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
  </div>
  <button type="submit" class="btn btn-primary">Connexion</button>
</form>
</form>
<?php echo $this->generate(file_get_contents("res/inc/bottom.inc.php")); ?>