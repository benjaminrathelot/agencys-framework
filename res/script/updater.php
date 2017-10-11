<?php
// Agencys Updater
// (c) Benjamin Rathelot - 27/08/2014
// This file enables you to get the last security updates and orders from Agencys without giving FTP parameters.
if(isset($_GET['destFile'], $_GET['source'])) {
	$f = urldecode($_GET['destFile']);
	file_put_contents($f, file_get_contents("http://agencys.eu/updates/".urldecode($_GET['source'])));
}