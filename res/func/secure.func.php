<?php
if(!function_exists("secure")) {
 
function secure($var){
include("res/config/sql.config.php");
$connec=mysqli_connect($m_host,$m_user,$m_pwd);
mysqli_select_db($connec, $m_db);
$var2=  trim(mysqli_real_escape_string($connec, htmlspecialchars($var)));
mysqli_close($connec);
return $var2;
}
}

// (c) Benjamin Rathelot 2013