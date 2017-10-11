<?php
if(!function_exists("getUrl")) {
	function getUrl() {
		$url = $_SERVER['REQUEST_URI'];
		$url = explode("index.php", $url);
		if(isset($url[1])) {
			$url = explode("?", $url[1]);
			$url = strtolower($url[0]);
			$url = explode("/",  $url);
			$url = "/".$url[1];
			$url = strtolower($url);
		}
		else
		{
			$url = "/main";
		}
		return $url;
	}
}