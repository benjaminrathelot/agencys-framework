<?php
// ErrorMessage Engine - (c) 2014 Benjamin Rathelot
if(!class_exists("ErrorMessageEngine")) {
	include("sh/b");
	include("res/class/Engine.class.php");
	class ErrorMessageEngine extends Engine {

		public function run() {
			$u = getUrl();
			switch($u) {
				default:
				break;

				case "/login":
				if(isset($_GET['inv'])) {
					$this->put("Invalid login data.");
				}
				break;

				case "/signup":
				if(isset($_GET['uoet'])) {
					$this->put("Username or email already taken.");
				}
				if(isset($_GET['pwderr'])) {
					$this->put("Passwords don't match.");
				}
				break;
			}
		}
	}
$_engine = new ErrorMessageEngine;
}
