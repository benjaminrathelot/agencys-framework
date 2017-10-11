<?php
// Encryption config
$pwd_salt = "_AgencysFramework2$";

function encrypt($v) { return sha1(md5($v).$pwd_salt); }
