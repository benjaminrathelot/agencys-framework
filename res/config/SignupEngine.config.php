<?php
// SignupEngine config file

//--- Signup fields
$SignupEngineRequiredFields[] = "email";
$SignupEngineRequiredFields[] = "username";
$SignupEngineRequiredFields[] = "password";
// Confirm fields
$SignupEngineConfirmFields['password'] = "password_confirm";

//--- Error pages
$SignupEngineUsernameOrEmailTakenURL = "Signup?uoet";
$SignupEnginePwdDontMatchURL = "Signup?pwderr";
$SignupEngineAlreadyOnlineURL = "";
//--- Main pages
$SignupEngineFormURL = "Signup";
$SignupEngineOkURL = "Signup?ok";
