<?php
if(!function_exists("load")) {
	function load($engineName, $return_obj=0) {
		include("res/engine/".$engineName.".engine.php");
		$n = ucfirst($engineName)."Engine";
		$e = new $n;
		if($return_obj) {
			return $e;
		}
		else
		{
			$e->run();
			return $e->getOutput();
		}
	}
}