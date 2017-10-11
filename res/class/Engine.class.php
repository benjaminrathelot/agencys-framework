<?php
//Engine class - every engines should extend this one
if(!class_exists("Engine")) {
	include("res/class/User.tmo.php");
	class Engine {

		protected $output;
		protected $name;
		protected $post;
		protected $get;
		protected $session;
		protected $cookie;
		protected $file;
		public $user;

		public function __construct() {
				// Rewrite the constructor if you don't need these vars
				if(!isset($_SESSION)) { session_start();}
				foreach($GLOBALS['_POST'] as $k=>$v) {
					$this->post[$k] = $v;
				}
				foreach($GLOBALS['_GET'] as $k=>$v) {
					$this->get[$k] = $v;
				}
				foreach($GLOBALS['_SESSION'] as $k=>$v) {
					$this->session[$k] = $v;
				}
				if(isset($GLOBALS['_COOKIE'])) {
					foreach($GLOBALS['_COOKIE'] as $k=>$v) {
						$this->cookie[$k] = $v;
					}
				}
				if(isset($GLOBALS['_FILE'])) {
					foreach($GLOBALS['_FILE'] as $k=>$v) {
						$this->file[$k] = $v;
					}
				}
				$this->user=false;
				if(isset($this->session['user_id'])) {
					if(file_exists("res/class/User.tmo.php")) {
						include("res/class/User.tmo.php");
						$u = new UserTmo();
						$u->from(array("id"=>$this->session['user_id']));
						if($u->get()) {
							$this->user=$u;
						}
						else
						{
							$this->user = false;
						}
					}
					else
					{
						$this->user = false;
					}
				}

		}
		public function glob($n, $v="___Engine_empty") {
			if($v=="___Engine_empty") {
				return $GLOBALS[$n];
			}
			else
			{
				$GLOBALS[$n]=$v;
				return true;
			}
		}
		public function getOutput() {
			return $this->output;
		}
		protected function setOutput($val) {
			$this->output.= $val;
		}
		protected function put($v) { $this->setOutput($v); }

		public function run() {
			//Where you have to put your code.
		}
	}
}