	<?php
/*



AgencysFramework Update 2.0 : Now deprecated. We have implemented AvancedWorkupTemplate (AWUT)
This class is no longer included by the router and the Engines.




*/
// WorkupTemplate - (c) 2014 Agencys - Benjamin Rathelot 
// WorkupTemplate is designed to work with the Agencys Framework
// Compatible with MySQLi and TMO

include("res/class/Engine.class.php");
include("res/engine/Mysqli.engine.php");
include("res/engine/Tmo.engine.php");
include("res/config/site.config.php");
include("res/func/getLang.func.php");
if(!class_exists("WorkupTemplate")) {
	class WorkupTemplate {

		protected $forbiddenVars = array();
		protected $storedVars = array("workupData"=>array("version"=>"1.0"));
		protected $templateName = "";
		protected $templateDir = "res/template/error/";
		protected $engineDir = "res/engine/";
		protected $mode = "auto"; // auto = everything except forbidden vars / assign = only vars defined
		protected $errors = array();
		protected $content = "";


		public function  __construct($tName="default", $mode="auto", $load=0) {
			//$this->setMode($mode);
			$this->templateDir="res/template/".getLang()."/";	
			$this->templateName = $tName;
			if($load==1) {
				$this->loadTemplate($tName);
			}
		}

		public function setTemplateDir($tDir) {
			$this->templateDir = $tDir;
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
				if($this->mode == "auto") {
					$GLOBALS['___vars_wut'] = $GLOBALS;
				}
				else
				{
					$GLOBALS['___vars_wut'] = array();
				}
				foreach($this->forbiddenVars as $k) {
					unset($GLOBALS['___vars_wut'][$k]);
				}
				$GLOBALS['___vars_wut'] = array_merge($GLOBALS['___vars_wut'], $this->storedVars);
				// Functions
				$str = preg_replace_callback("#\<:top\s?/\>#", function() { ob_start();include("res/inc/top.inc.php");$c=ob_get_contents();ob_end_clean(); return $this->generate($c); }, $str);
				$str = preg_replace_callback("#\<:bottom\s?/\>#", function() { ob_start();include("res/inc/bottom.inc.php");$c=ob_get_contents();ob_end_clean(); return $this->generate($c); }, $str);
				$str = preg_replace_callback("#\<:escape\(\"(.*?)\"\)\s?/\>#", function($matches) { return htmlspecialchars($matches[1]); }, $str);
				$str = preg_replace_callback("#\<:increment\(([a-zA-Z0-9_]+)\)\s?/\>#", function($matches) { $GLOBALS[$matches[1]]++;$GLOBALS['___vars_wut'][$matches[1]]++; return ""; }, $str);
				$str = preg_replace_callback("#\<:increment\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", function($matches) { $GLOBALS[$matches[1]][$matches[2]]++;$GLOBALS['___vars_wut'][$matches[1]][$matches[2]]++; return ""; }, $str);
				$str = preg_replace_callback("#\<:escape\>(.*?)\</:escape\>#", function($matches) { return htmlspecialchars($matches[1]); }, $str);
				//Loops
				$str = preg_replace_callback("#\<:foreach\(([a-zA-Z0-9_]+)\-\>([a-zA-Z0-9_]+)\)\>(.*?)\</:foreach\>#x", function($matches) {  $exp= explode("</:foreach>",$matches[3]);
					$var = "";
					foreach($GLOBALS[$matches[1]] as $u) {
						$this->addVar($matches[2],$u);

					$var.= $this->generate($exp[0]);
				}
				$exp[0] = $var;
				 $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:foreach>"; return $exp[0]; }, $str);
				//Get Var or Array
				$str = preg_replace_callback("#\<:var\(([a-zA-Z0-9_]+)\)\s?/\>#", function($matches) { return $GLOBALS['___vars_wut'][$matches[1]]; }, $str);
				$str = preg_replace_callback("#\<:isset\(([a-zA-Z0-9_]+)\)\s?/\>#", function($matches) { if(isset($GLOBALS['___vars_wut'][$matches[1]])) {return $GLOBALS['___vars_wut'][$matches[1]]; } else { return ""; } }, $str);
				$str = preg_replace_callback("#\<:array\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", function($matches) { return $GLOBALS['___vars_wut'][$matches[1]][$matches[2]]; }, $str);
				$str = preg_replace_callback("#\<:array\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\s?/\>#", function($matches) { return $GLOBALS['___vars_wut'][$matches[1]][$matches[2]][$matches[3]]; }, $str);
				//Script calling
				$str = preg_replace_callback("#\<:engine\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", function($matches) { include($this->engineDir.$matches[1].".engine.php");if(isset($_engine)){ $_engine->run();return $this->generate($_engine->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; }}, $str);
				$str = preg_replace_callback("#\<:localengine\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", function($matches) { if(isset($GLOBALS[$matches[1]])){ $GLOBALS[$matches[1]]->run();return $this->generate($GLOBALS[$matches[1]]->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; }}, $str);
				$str = preg_replace_callback("#\<:mysqli\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", function($matches) { if(isset($GLOBALS[$matches[1]])){ $GLOBALS[$matches[1]]->run();return $this->generate($GLOBALS[$matches[1]]->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; }}, $str);
				$str = preg_replace_callback("#\<:tmo\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", function($matches) { if(isset($GLOBALS[$matches[1]])){ $GLOBALS[$matches[1]]->run();return $this->generate($GLOBALS[$matches[1]]->getOutput()); } else { echo "Fatal Error: ENGINE IS NOT DEFINED."; }}, $str);
				$str = preg_replace_callback("#\<:form\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", function($matches) { if(file_exists("res/inc/".$matches[1].".tmostyle")) { return file_get_contents("res/inc/".$matches[1].".tmostyle"); } else { echo "Fatal Error: FORM DOESNT EXIST."; }}, $str);

				$str = preg_replace_callback("#\<:template\(([a-zA-Z0-9_\-/\.]+)\)\s?/\>#x", function($matches) { if(file_exists($this->templateDir.$matches[1].".wut.html")) { return $this->generate(file_get_contents($this->templateDir.$matches[1].".wut.html")); } else { echo "Fatal Error: TEMPLATE DOESNT EXIST."; }}, $str);
				if(preg_match("#\</:if\>#",$str)) {
					//If equal to something (simple var)
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)==\"(.*?)\"\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] == $matches[2]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\<\"(.*?)\"\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] < $matches[2]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\>\"(.*?)\"\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] > $matches[2]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\<=\"(.*?)\"\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] <= $matches[2]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\>=\"(.*?)\"\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] >= $matches[2]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					//If equal to a var (simple var)
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] == $GLOBALS['___vars_wut'][$matches[2]]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] < $GLOBALS['___vars_wut'][$matches[2]]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] > $GLOBALS['___vars_wut'][$matches[2]]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] <= $GLOBALS['___vars_wut'][$matches[2]]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] >= $GLOBALS['___vars_wut'][$matches[2]]) { $exp= explode("</:if>",$matches[3]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					//If equal to an array (simple var)
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] == $GLOBALS['___vars_wut'][$matches[2]][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] < $GLOBALS['___vars_wut'][$matches[2]][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] > $GLOBALS['___vars_wut'][$matches[2]][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] <= $GLOBALS['___vars_wut'][$matches[2]][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]] >= $GLOBALS['___vars_wut'][$matches[2]][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					//If equal to an array (array)
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] == $GLOBALS['___vars_wut'][$matches[3]][$matches[4]]) { $exp= explode("</:if>",$matches[5]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] < $GLOBALS['___vars_wut'][$matches[3]][$matches[4]]) { $exp= explode("</:if>",$matches[5]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] > $GLOBALS['___vars_wut'][$matches[3]][$matches[4]]) { $exp= explode("</:if>",$matches[5]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] <= $GLOBALS['___vars_wut'][$matches[3]][$matches[4]]) { $exp= explode("</:if>",$matches[5]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] >= $GLOBALS['___vars_wut'][$matches[3]][$matches[4]]) { $exp= explode("</:if>",$matches[5]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					//If equal to a var (array)
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)==([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] == $GLOBALS['___vars_wut'][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] < $GLOBALS['___vars_wut'][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] > $GLOBALS['___vars_wut'][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\<=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] <= $GLOBALS['___vars_wut'][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
					$str = preg_replace_callback("#\<:if\(([a-zA-Z0-9_]+),\s?([a-zA-Z0-9_]+)\>=([a-zA-Z0-9_]+)\)\>(.*?)\</:if\>#", function($matches) { if($GLOBALS['___vars_wut'][$matches[1]][$matches[2]] >= $GLOBALS['___vars_wut'][$matches[3]]) { $exp= explode("</:if>",$matches[4]); $c = "";foreach($exp as $h){ $c.=$h;} $exp[0]=$c."</:if>"; return $exp[0]; } else { return ""; }}, $str);
				}
				//Add lines
				$str = preg_replace("#\<:line_n/\>#","\n", $str);
				$str = preg_replace("#\<:line_r/\>#","\r", $str);
			$str = preg_replace("#\</:if\>#","", $str);
			$str = preg_replace("#\</:foreach\>#","", $str);
			return $str;
		}

		public function  loadTemplate($cache=0, $insert=0) {
			if($cache AND file_exists("res/cache/wut/".sha1($this->templateDir.$this->templateName).".cache")) {
				$this->loadCache();
			}
			else
			{
				$this->content = preg_replace('/\n+/', '<:line_n/>', trim(file_get_contents($this->templateDir.$this->templateName.".wut.html")));
				$this->content = preg_replace('/\n+/', '<:line_r/>', $this->content);
				if(count($this->errors) !=0) { $this->errors[] = "->loadTemplate() : Some errors have been detected, the command won't run properly"; }
				if(preg_match("#\<:(.*?)/\>#", $this->content)) {
				$this->content = $this->generate($this->content);
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

		function issetError() {
			if(count($this->errors) == 0) {
				return false;
			}
			else
			{
				return true;
			}
		}

		function showErrors() {
			foreach($this->errors as $text) {
				echo $text."<br />";
			}
		}

		function getErrorsArray() {
			return $this->errors;
		}

		function forbidVar($vName) {
			$this->forbiddenVars[] = $vName;
		}

		function allowVar($vName) {
			if(isset($this->forbiddenVars[$vName])) {
				unset($this->forbiddenVars[$vName]);
			}
		}

		function addVar($vName, $vValue) {
			$this->storedVars[$vName] = $vValue;
		}

		function addVars($vArray) {
			$this->storedVars = array_merge($this->storedVars, $vArray);
		}

		function add($v, $vv="") {
			if(is_array($v) AND $vv=="") {
				$this->addVars($v);
			}
			else
			{
				$this->addVar($v, $vv);
			}
		}

		function delVar($vName) {
			unset($this->storedVars[$vName]);
		}

		function del($vName) { $this->delVar($vName); }

	}
	/*
	class localEngineExample extends Engine{
		public function run() {
			$this->setOutput("Hello ");
			$this->setOutput("WORLD!");
		}
	}
	$localEngineObjectExample = new localEngineExample();*/
$GLOBALS['w'] = new WorkupTemplate;
}
