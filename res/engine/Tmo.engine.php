<?php
// MysqliEngine -- (c) 2014 Benjamin Rathelot
// Bridge between engine and Tmo

if(!class_exists("TmoEngine")) {
	class TmoEngine extends Engine{
		protected $tmo_object;
		protected $tmo_name;

		public function loadTmo($name, $from=false, $arg="") {
			include("res/class/".ucfirst(str_replace("://", "", $name)).".tmo.php");
			if(!$form) { $form= array(); }
			$this->tmo_object = new $name;
			$this->tmo_object->from($from);
			if($this->tmo_object->get($arg)) {
				return true;
			}
			else
			{
				return false;
			}
		}

		public function run() {
			// You should remake this function in the child class
			$this->put("Default run function.");
		}
	}
}