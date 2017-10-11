<?php
if(!function_exists("p")) {
	function p($st) {
		$exp = explode(":", $st);
		switch($exp[0]) {
			case "f":
			$t = "func";
			$a = "func";
			break;
			case "func":
			$t = "func";
			$a = "func";
			break;
			case "fs":
			$t = "func";
			$a = "funcs";
			break;
			case "funcs":
			$t = "func";
			$a = "funcs";
			break;
			case "cl":
			$t = "class";
			$a = "class";
			break;
			case "class":
			$t = "class";
			$a = "class";
			break;
			case "cls":
			$t = "class";
			$a = "classes";
			break;
			case "cf":
			$t = "config";
			$a = "config";
			break;
			case "config":
			$t = "config";
			$a = "config";
			break;
			case "i":
			$t = "inc";
			$a = "inc";
			break;
			case "inc":
			$t = "inc";
			$a = "inc";
			break;
			case "s":
			$t = "script";
			$a = "script";
			break;
			case "script":
			$t = "script";
			$a = "script";
			break;
			case "e":
			$t = "engine";
			$a = "engine";
			break;
			case "engine":
			$t = "engine";
			$a = "engine";
			break;
			case "t":
			$t = "class";
			$a = "tmo";
			break;
			case "tmo":
			$t = "class";
			$a = "tmo";
			break;
			default:
			echo "Import error.";
			exit;
			break;			
		}
		$url = "res/$t/".$exp[1].".$a.php";
			return $url;
	}
}