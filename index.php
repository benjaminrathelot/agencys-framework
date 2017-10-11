<?php
// Agencys Router
// (c) 2014 - BENJAMIN RATHELOT
@session_start();
if(isset($_SESSION['user_id'])) {
	$online = 1;
}
else
{
	$online = 0;
}
include("sh/b");
include(p("cf:router"));
include(p("class:Engine"));
$_engine = new Engine();

// Generate route array (if cannot open cache)
if(!file_exists("res/cache/router/route.cache")) {
	$_ROUTE = array();
	foreach($AgencysRouterRouteFiles as $routeFile) {
		$get_file = file_get_contents("res/config/router/".$routeFile.".route");
		$lines = explode("".PHP_EOL."", $get_file);
		foreach($lines as $line) {
			$data = explode(" ", $line);
			$name = strtolower($data[0]);
			$_ROUTE[$name]['type'] = $data[1];
			$_ROUTE[$name]['location'] = $data[2];
			if(isset($data[3]) AND $data[3]=="restricted") {
				$_ROUTE[$name]['restricted'] = true;
			}
			else
			{
				$_ROUTE[$name]['restricted'] = false;
			}
		}
	}
	file_put_contents("res/cache/router/route.cache", serialize($_ROUTE));
}
else
{
	$_ROUTE = unserialize(file_get_contents("res/cache/router/route.cache"));
}

//Treat URL
$url = getUrl();

if(isset($_ROUTE[$url])) {
	$type = strtoupper($_ROUTE[$url]['type']);
	$location = $_ROUTE[$url]['location'];
	if($_ROUTE[$url]['restricted']==true AND !isset($_SESSION['user_id'])) {
	$type = "TEMPLATE_CACHE";
	$location = "404";
}
} 
elseif($url=="/$$") {
	include("res/cms/config/cms.config.php");
	if($CMS['enableCMS'] == true AND $CMS['enableAdmin']== true) {
		include("res/cms/engine/AdminFrame.engine.php");
		$_engine->run();
	}
	else
	{
		$type = "TEMPLATE_CACHE";
		$location = "404";
	}
}
elseif(substr($url,0,2)=="/$") {
	include("res/cms/config/cms.config.php");
	if($CMS['enableCMS']) {
		include("res/cms/engine/CMSRouter.engine.php");
		$_engine->run();
	}
	else
	{
		$type = "TEMPLATE_CACHE";
		$location = "404";
	}
}
else
{
	$type = "TEMPLATE_CACHE";
	$location = "404";
}


switch($type) {
	case "TEMPLATE_CACHE":
	$w->name($location);
	$w->loadTemplate(1, 1);
	break;

	case "TEMPLATE":
	$w->name($location);
	$w->loadTemplate(0, 1);
	break;

	case "ENGINE":
	if(file_exists("res/engine/".ucfirst($location).".engine.php")) { 
		include("res/engine/".ucfirst($location).".engine.php");
		$_engine->run();
	}
	break;
}