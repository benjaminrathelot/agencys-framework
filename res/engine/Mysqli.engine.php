<?php
// MysqliEngine -- (c) 2014 Benjamin Rathelot
// Bridge between engine and sql

if(!class_exists("MysqliEngine")) {
	class MysqliEngine extends Engine {
		protected $request;
		protected $host;
		public $user;
		protected $password;
		protected $db;
		protected $mysqli_connector;
		protected $config_path="res/config/sql.config.php";
		protected $is_connected;

		public function __construct() {
			$this->connectFromConfig();
		}
		public function connect($h, $u, $p, $d) {
			$this->host = $h;
			$this->user = $u;
			$this->password = $p;
			$this->db = $d;
			$this->mysqli_connector = mysqli_connect($this->host, $this->user, $this->password);
			if($this->mysqli_connector) {
				mysqli_select_db($this->mysqli_connector, $this->db);
				$this->is_connected = true;
				return true;
			}
			else
			{
				$this->is_connected = false;
				return false;
			}
		}
		public function connectFromConfig($cpath = false) {
			if(!$cpath) {
				$cpath = $this->config_path;
			}
			$cpath = str_replace("http://", "", $cpath); // Deny remote include
			include($cpath);
			$this->connect($m_host, $m_user, $m_pwd, $m_db);
		}
		public function setConfigPath($path) {
			$this->config_path = $path;
		}
		public function setRequest($req) {
			$this->request = $req;
		}
		public function query($rq) { $this->setRequest($rq); }
		protected function callback($sql_return_obj) {
			// You should remake this function in the child class
			$this->put("Default run function.");
		}
		public function close() {
			mysqli_close($this->mysqli_connector);
		}
		public function run() {
			if($this->is_connected) {
				$exec = mysqli_query($this->mysqli_connector, $this->request);
				if($exec) {
					while($sqlobj = mysqli_fetch_object($exec)) {
						$this->callback($sqlobj);
					}
					return true;
				}
				else
				{
					$this->put("DB Error.");
					false;
				}
			}
		}
	}
}