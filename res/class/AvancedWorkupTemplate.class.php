	<?php

// AvancedWorkupTemplate - (c) 2014 Agencys - Benjamin Rathelot 
// WorkupTemplate is designed to work with the Agencys Framework
// Compatible with MySQLi and TMO2.0	

include("res/class/Engine.class.php");
include("res/engine/Mysqli.engine.php");
include("res/engine/Tmo.engine.php");
include("res/config/site.config.php");
include("res/func/getLang.func.php");
if(!class_exists("AvancedWorkupTemplate")) {
	class AvancedWorkupTemplate {

		protected $forbiddenVars = array();
		protected $storedVars = array("workupData"=>array("version"=>"2.0"));
		protected $templateName = "";
		protected $templateDir = "res/template/error/";
		protected $engineDir = "res/engine/";
		protected $mode = "auto"; // auto = everything except forbidden vars / assign = only vars defined
		protected $errors = array();
		protected $content = "";
		protected $awutConversion = "";


		public function  __construct($tName="default", $mode="auto", $load=0) {
			$this->setMode($mode);
			$this->templateDir="res/template/".getLang()."/";	
			$this->templateName = $tName;
			if($load==1) {
				$this->loadTemplate($tName);
			}
		}

		public function setTemplateDir($tDir) {
			$this->templateDir = $tDir;
		}

		public function  setMode($mode) {
			if($mode == "auto" OR $mode=="assign") {
				$this->mode = $mode;
			}
			else
			{
				$this->errors[] = "->setMode() : Unknow mode.";
			}
		}
		public function saveCache($dname=false) {
			if(!$dname) { $dname = sha1($this->templateDir.$this->templateName); }
			file_put_contents("res/cache/wut/".$dname.".cache", $this->content);
		}
		public function loadCache($dname=false) {
			if(!$dname) { $dname = sha1($this->templateDir.$this->templateName); }
			$this->content = file_get_contents("res/cache/wut/".$dname.".cache");
		}

		public function generate($str) {

				$GLOBALS = array_merge($GLOBALS, $this->storedVars);
				if(preg_match("#\<:(.*?)/\>#", $str)) {
				// Functions
				$str = preg_replace("#\<:top\s?/\>#", '<?php echo $this->generate(file_get_contents("res/inc/top.inc.php")); ?>', $str);
				$str = preg_replace("#\<:bottom\s?/\>#", '<?php echo $this->generate(file_get_contents("res/inc/bottom.inc.php")); ?>', $str);
				$str = preg_replace("#\<:escape\(\"(.*?)\"\)\s?/\>#", '<?php  return htmlspecialchars(\"$1\");  ?>', $str);
				$str = preg_replace("#\<:increment\(([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  $GLOBALS["$1"]++;$GLOBALS["$1"]++;  ?>', $str);
				$str = preg_replace("#\<:increment\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  $GLOBALS["$1"]["$2"]++;$GLOBALS["$1"]["$2"]++; return "";  ?>', $str);
				$str = preg_replace("#\<:escape\>(.*?)\</:escape\>#", '<?php  return htmlspecialchars("$1");  ?>', $str);
				//Loops
				$str = preg_replace("#\<:foreach\(([a-zA-Z0-9_]+)\-\>([a-zA-Z0-9_]+)\)\>(.*?)\</:foreach\>#x", '<?php foreach($GLOBALS["$1"] as $u) {$this->addVar("$2",$u); $this->generate("$3"); }  ?>', $str);
				//Get Var or Array
				$str = preg_replace("#\<:var\(([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  echo $GLOBALS["$1"];  ?>', $str);
				$str = preg_replace("#\<:isset\(([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  if(isset($GLOBALS["$1"])) { echo $GLOBALS["$1"]; } ?>', $str);
				$str = preg_replace("#\<:array\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  echo $GLOBALS["$1"]["$2"];  ?>', $str);
				$str = preg_replace("#\<:object\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  echo $GLOBALS["$1"]->$2;  ?>', $str);
				$str = preg_replace("#\<:array\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", '<?php  echo $GLOBALS["$1"]["$2"]["$3"];  ?>', $str);
				//Script calling
				$str = preg_replace("#\<:engine\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", '<?php  include($this->engineDir."$1".".engine.php");if(isset($_engine)){ $_engine->run();echo $this->generate($_engine->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; } ?>', $str);
				$str = preg_replace("#\<:localengine\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", '<?php  if(isset($GLOBALS["$1"])){ $GLOBALS["$1"]->run();echo $this->generate($GLOBALS["$1"]->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; } ?>', $str);
				$str = preg_replace("#\<:mysqli\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", '<?php  if(isset($GLOBALS["$1"])){ $GLOBALS["$1"]->run();echo $this->generate($GLOBALS["$1"]->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; } ?>', $str);
				$str = preg_replace("#\<:tmo\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", '<?php  if(isset($GLOBALS["$1"])){ $GLOBALS["$1"]->run();echo $this->generate($GLOBALS["$1"]->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; } ?>', $str);

				$str = preg_replace("#\<:template\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", '<?php  if(file_exists($this->templateDir."$1".".wut.php")) { include($this->templateDir."$1".".wut.php"); } elseif(file_exists($this->templateDir."$1".".wut.html")) { echo $this->generate(file_get_contents($this->templateDir."$1".".wut.html")); } else { echo "Fatal Error: TEMPLATE DOESNT EXIST."; } ?>', $str);
				if(preg_match("#\</:if\>#",$str)) {
					//If equal to something (simple var)
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)==\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] == "$2") { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\<\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] < "$2") { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\>\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] > "$2") { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\<=\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] <= "$2") { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\>=\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] >= "$2") { echo $this->generate("$3"); } ?>', $str);
					//If equal to something (array) [AWUT]
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)==\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] == "$3") { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] < "$3") { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] > "$3") { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<=\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] <= "$3") { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>=\"(.*?)\"\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] >= "$3") { echo $this->generate("$4"); } ?>', $str);
					
					//If equal to a var (simple var)
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] == $GLOBALS["$2"]) { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] < $GLOBALS["$2"]) { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] > $GLOBALS["$2"]) { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] <= $GLOBALS["$2"]) { echo $this->generate("$3"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] >= $GLOBALS["$2"]) { echo $this->generate("$3"); } ?>', $str);
					//If equal to an array (simple var)
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] == $GLOBALS["$2"]["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] < $GLOBALS["$2"]["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] > $GLOBALS["$2"]["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] <= $GLOBALS["$2"]["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"] >= $GLOBALS["$2"]["$3"]) { echo $this->generate("$4"); } ?>', $str);
					//If equal to an array (array)
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] == $GLOBALS["$3"]["$4"]) { echo $this->generate("$5"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] < $GLOBALS["$3"]["$4"]) { echo $this->generate("$5"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] > $GLOBALS["$3"]["$4"]) { echo $this->generate("$5"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] <= $GLOBALS["$3"]["$4"]) { echo $this->generate("$5"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] >= $GLOBALS["$3"]["$4"]) { echo $this->generate("$5"); } ?>', $str);
					//If equal to a var (array)
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] == $GLOBALS["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] < $GLOBALS["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] > $GLOBALS["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] <= $GLOBALS["$3"]) { echo $this->generate("$4"); } ?>', $str);
					$str = preg_replace("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", '<?php  if($GLOBALS["$1"]["$2"] >= $GLOBALS["$3"]) { echo $this->generate("$4"); } ?>', $str);
				}
				//Add lines
				$str = preg_replace("#\<:line_n/\>#","\n", $str);
				$str = preg_replace("#\<:line_r/\>#","\r", $str);
			}
				$randhash = time().sha1(time().mt_rand(1,9999));
				file_put_contents("res/cache/wut/$randhash.generatetmo", $str);
				ob_start();
				include("res/cache/wut/$randhash.generatetmo");
				$c=ob_get_contents();
				ob_end_clean();
				unlink("res/cache/wut/$randhash.generatetmo");
				$this->awutConversion = $str;
				return $c;
		}

		public function  loadTemplate($cache=0, $insert=0) {
			if($cache AND file_exists("res/cache/wut/".sha1($this->templateDir.$this->templateName).".cache")) {
				$this->loadCache();
			}
			else
			{
				$GLOBALS = array_merge($GLOBALS, $this->storedVars);
				$kxw = $this->templateDir.$this->templateName.".wut.php";
				if(!file_exists($kxw)) {
					$this->content = preg_replace('/\n+/', '<:line_n/>', trim(file_get_contents($this->templateDir.$this->templateName.".wut.html")));
					$this->content = preg_replace('/\n+/', '<:line_r/>', $this->content);
					if(count($this->errors) !=0) { $this->errors[] = "->loadTemplate() : Some errors have been detected, the command won't run properly"; }
					if(preg_match("#\<:(.*?)/\>#", $this->content)) {
						$this->content = $this->generate($this->content);
						file_put_contents($kxw , $this->awutConversion);
					}
				}
				else
				{
					ob_start();
					include($this->templateDir.$this->templateName.".wut.php");
					$c=ob_get_contents();
					ob_end_clean();
					$this->content = $c;
				}
			}
			if($cache) { $this->saveCache(); }
			if($insert==1) { $this->insert(); }

		}
		public function templateName($tname) {
			$this->templateName = $tname;
		}
		public function name($tname) {
			$this->templateName($tname);
		}
		public function load($insert=0) { $this->loadTemplate($insert); }

		public function getHTML() {
			return $this->content;
		}

		public function insertHTML() {
			echo $this->content;
		}

		function get() { $this->getHTML(); }
		function insert() { $this->insertHTML(); }

		public function forbidVar($vName) {
			$this->forbiddenVars[] = $vName;
		}

		public function allowVar($vName) {
			if(isset($this->forbiddenVars[$vName])) {
				unset($this->forbiddenVars[$vName]);
			}
		}

		public function addVar($vName, $vValue) {
			$this->storedVars[$vName] = $vValue;
		}

		public function addVars($vArray) {
			$this->storedVars = array_merge($this->storedVars, $vArray);
		}

		public function add($v, $vv="") {
			if(is_array($v) AND $vv=="") {
				$this->addVars($v);
			}
			else
			{
				$this->addVar($v, $vv);
			}
		}

		public function delVar($vName) {
			unset($this->storedVars[$vName]);
		}

		public function del($vName) { $this->delVar($vName); }

	}
	class AWUT extends AvancedWorkupTemplate{}
	/*
	class localEngineExample extends Engine{
		public function run() {
			$this->setOutput("Hello ");
			$this->setOutput("WORLD!");
		}
	}
	$localEngineObjectExample = new localEngineExample();*/

}
