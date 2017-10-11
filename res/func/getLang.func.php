<?php
if(!function_exists("getLang")) {
	function getLang() {
		include("sh/p");
		include(p("config:site"));
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		if(in_array($lang, $SiteLanguagesList)) {
			return $lang;
		}
		else
		{
			return $SiteDefaultLanguage;
		}
	}
}