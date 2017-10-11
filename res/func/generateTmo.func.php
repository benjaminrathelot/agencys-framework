<?php
// GenerateTmo : Table Managed By Object ---- (c) 2014 Benjamin Rathelot — V2.0
if(!function_exists("generateTmo")) {
function generateTmo($form, $class=1, $formstyle=1) {
	
	$file = file_get_contents("res/form/".$form.".newtmo");
	$lines = preg_split('/\r?\n/', $file);
	$field = Array();
	$i = 0;
	$first = 0;
	$pm_key = "";
	
	//Fields registration
	foreach($lines as $content) {
		$data = explode(":", $content);
		$info = explode(" ", $data[0]);
		$params = explode(" ", $data[1]);
		
		
		if(preg_match("#varchar.#", $info[0])) {
			$sql_type = "varchar";
			$vl = explode(".", $info[0]);
			$sql_length = $vl[1];
		}
		elseif($info[0]=="int")
		{
			$sql_type = "int";
			$sql_length = "11";
		}
		elseif($info[0]=="list")
		{
			$sql_type = "text";
			$sql_length = "";
		}
		else
		{
			$vl = explode(".", $info[0]);
			$sql_type = $vl[0];
			if(isset($vl[1])) { $sql_length = $vl[1]; } else { $sql_length = ""; }
		}
		$name = $info[1];
		if(in_array("sql", $params)) { $sql = "yes"; } else { $sql = "no"; }
		if(in_array("autoincrement", $params)) { $ai = "auto_increment"; } else { $ai = ""; }
		if(in_array("primary", $params)) { $pr = "yes"; } else { $pr = "no"; }
		if(preg_match("#\{(.+)\}#", $data[1])) {
			$st1 = explode("{", $data[1]);
			$st2 = explode("}", $st1[1]);
			$css = str_replace("=",":", $st2[0]);
			$css = str_replace("_"," ", $css);
			$field[$i]['css'] = $css;
		}
		else
		{
			$field[$i]['css']="";
		}
		if(preg_match("#\[(.+)\]#", $data[1])) {
			$st1 = explode("[", $data[1]);
			$st2 = explode("]", $st1[1]);
			$vl = str_replace("_"," ", $st2[0]);
			$vl2 = explode("/", $vl);
			$field[$i]['value'] = $vl2[1];
			$field[$i]['label'] = $vl2[0];
		}
		else
		{
			$field[$i]['value'] = "";
			$field[$i]['label'] = "";
		}
		$field[$i]['type'] = $info[0];
		$field[$i]['name'] = $name;
		$field[$i]['sql'] = $sql;
		$field[$i]['auto_increment'] = $ai;
		$field[$i]['primary_key'] = $pr;
		$field[$i]['sql_type'] = strtoupper($sql_type);
		$field[$i]['sql_length'] = $sql_length;
		
		$i++;
	}
	
	//Generate sql request
	$request = "CREATE TABLE IF NOT EXISTS `$form` (";
	foreach($field as $col) {
		if($col['sql'] == "yes") {
			if($first==0) { $first=1; $ins =""; } else { $ins =","; }
			$request.=$ins." `".$col['name']."` ".$col['sql_type'];
			if($col['sql_length']!="") { $request.="(".$col['sql_length'].")"; }
			$request.=" NOT NULL ".$col['auto_increment'];
			if($col['primary_key'] == "yes") { $pm_key = $col['name']; }
		}
	}
	if($pm_key != "") {
		$request.= ", PRIMARY KEY (`$pm_key`)"; 
	}
	$request.= ") ENGINE=MyISAM DEFAULT CHARSET=latin1";
	
	//Create table
	include("res/config/sql.config.php");
	$connec=mysqli_connect($m_host,$m_user,$m_pwd);
	mysqli_select_db($connec, $m_db);
	mysqli_query($connec, $request) or die(mysqli_error($connec));
	mysqli_close($connec);
        if($class) {
	//Create class
	$class ='<?php
//TMO class : '.$form.'
// (c) Benjamin Rathelot — Agencys 2014
if(!class_exists(\''.ucfirst($form).'Tmo\')) {
include("res/class/Tmo.class.php");
include("res/func/secure.func.php");
class '.ucfirst($form).'Tmo extends Tmo {

    protected $__form_class___empty;
	protected $__form_class___from;
	protected $__form_class___from_type="AND";
';
	foreach($field as $col) {
		$class.= "    protected \$".$col['name'].";
";
	}
	$class.="

";
	$class.='	function __construct() {
		$this->__form_class___empty = true;
	}';
	$class.="

";
	foreach($field as $col) {
	if($col['type']=="list"){
				$class.= "    	function get".ucfirst($col['name'])."() { return json_decode(\$this->".$col['name'].",1); } 
    	function set".ucfirst($col['name'])."(\$val) { \$this->".$col['name']." = json_encode(\$val); if(\$this->__form_class___empty != true) { \$this->update('".$col['name']."');} } 

";
	}
	else
	{ 
		$class.= "    	function get".ucfirst($col['name'])."() { return \$this->".$col['name']."; } 
    	function set".ucfirst($col['name'])."(\$val) { \$this->".$col['name']." = \$val; if(\$this->__form_class___empty != true) { \$this->update('".$col['name']."');} } 

";
	}
	}
	$class.="
";
	$class.="    function getTmoName() { return '$form'; }
";
	$class.="    function getFieldNames() { return '";
	foreach($field as $col)
		$class.=$col['name'].";";
	$class.="'; }
";
	$class.='    function update($arg, $opt="") { 
		if($this->__form_class___empty != true) {
                    if($this->__form_class___from!="") {
                            $and = "";
                            $from_add="";
                            foreach($this->__form_class___from as $k=>$v) {
                            	$from_add.=" ".$and." $k=\'$v\'";
                            	$and = $this->__form_class___from_type;
                            }
                            $request = "UPDATE ".$this->getTmoName()." SET $arg=\'".$this->$arg."\' WHERE $from_add AND id=\'".$this->getId()."\' $opt;";
			}
                        else
                        {
                            $request = "UPDATE ".$this->getTmoName()." SET $arg=\'".$this->$arg."\' WHERE id=\'".$this->getId()."\' $opt;";
                        }
                        include("res/config/sql.config.php");
			$connec=mysqli_connect($m_host,$m_user,$m_pwd);
			mysqli_select_db($connec, $m_db);
			mysqli_query($connec, $request) or die(mysqli_error());
			mysqli_close($connec);
				
		}}
	function delete() {
		include("res/config/sql.config.php");
		$connec=mysqli_connect($m_host,$m_user,$m_pwd);
		mysqli_select_db($connec, $m_db);
		mysqli_query($connec, "DELETE FROM '.$form.' WHERE id=\'".$this->getId()."\';") or die(mysqli_error());
		mysqli_close($connec);
	}
    function updateAll() {
';
	foreach($field as $col) 
		$class.='	$this->update("'.$col['name'].'");'."
";
	$class.='    }
	function changeEmpty($val) { $this->__form_class___empty = $val; }
	function from($val, $type="AND") { $this->__form_class___from = $val;$this->__form_class___from_type = $type; }
	function get($opt="") {
                if($this->__form_class___from!="") {
                            $and = "";
                            $from_add="";
                            foreach($this->__form_class___from as $k=>$v) {
                            	$from_add.=" ".$and." $k=\'$v\'";
                            	$and = $this->__form_class___from_type;
                            }
                    $request = "SELECT * FROM ".$this->getTmoName()." WHERE $from_add $opt;";
                }
                else
                {
                    $request = "SELECT * FROM ".$this->getTmoName()." $opt;";
                }
		include("res/config/sql.config.php");
		$connec=mysqli_connect($m_host,$m_user,$m_pwd);
		mysqli_select_db($connec, $m_db);
		$rq = mysqli_query($connec, $request) or die(mysqli_error());
		if(mysqli_num_rows($rq)>0){
		$array = mysqli_fetch_array($rq);
		mysqli_close($connec);
		$this->__form_class___empty = false;
		
	';
	foreach($field as $col) 
		$class.='	$this->'.$col['name'].' = $array[\''.$col['name'].'\'];
	';
	$class.="
 return true; } else { return false; }}";
	$class.='
	function insert() {
		$request = "INSERT INTO ".$this->getTmoName()." VALUES(';
		$start='';
		foreach($field as $col) {
			if($col['sql'] == "yes") {
				$class.=$start."'\".\$this->".$col['name'].".\"'";
				$start=',';
			}
		}
	$class.=');";	
		include("res/config/sql.config.php");
		$connec=mysqli_connect($m_host,$m_user,$m_pwd);
		mysqli_select_db($connec, $m_db);
		mysqli_query($connec, $request) or die(mysqli_error());
		mysqli_close($connec); }';
	$class.='
	function set($arg) {
		foreach($arg as $k=>$v) {
			if(isset($this->$k)) {
				$this->$k=$v;
			}
		}
	}
	';
	$class.="

}

\$class['includes']['".$form."Form']=1;
}";
	file_put_contents("res/class/".ucfirst($form).".tmo.php",$class);
	$file = fopen("res/class/class.all.php", "a");
	fputs($file, "include(\"res/class/".ucfirst($form).".tmo.php\");
");
	fclose($file);
        }
}

}

