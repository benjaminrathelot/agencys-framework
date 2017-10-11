<?php
if(!function_exists("getDate")) {
	function getDate() {
		include("sh/b");
		include(p("config:site"));
		return date($SiteDateFormat);
	}
}