<?php
// Signup Engine - (c) 2014 Benjamin Rathelot
if(!class_exists("LoginEngine")) {
	include("sh/b");
	class SignupEngine extends Engine {

		protected function additionalActions() {
			// Use this function to send a mail after the signup or something like that
			/*
			Here is how to send a mail
			include(p("engine:SendMail"));
			$_engine->setFrom(...);
			$_engine->setTo(...);
			...
			$_engine->run();
			*/
		}

		public function run() {
			include(p("cf:encrypt"));
			include(p("cf:SignupEngine"));
			include(p("t:User"));
			include(p("cf:site"));
			if(isset($this->session['user_id']) AND $this->session['user_id']!=0) {
				header("location:".$SiteDomain.$SignupEngineAlreadyOnlineURL);
				exit;
			}
			$isset_error = 0;
			$fields = array();
			foreach($SignupEngineRequiredFields as $n) {
				if(!isset($this->post[$n])) {
					$isset_error++;
				}
				else
				{
					$fields[$n] = secure($this->post[$n]);
				}
			}
			foreach($SignupEngineConfirmFields as $n) {
				if(!isset($this->post[$n])) {
					$isset_error++;
				}
			}
			if($isset_error!=0) {
				echo "Missing var.";exit;
			}
			else
			{
				$isset_member_test = array();
				if(in_array("username", $SignupEngineRequiredFields)) {
					$isset_member_test['username'] = secure($this->post['username']);
				}
				if(in_array("email", $SignupEngineRequiredFields)) {
					$isset_member_test['email'] = secure($this->post['email']);
				}
				$user_test = new UserTmo;
				$user_test->from($isset_member_test, "OR");
				if($user_test->get()) {
					header("location:".$SiteDomain.$SignupEngineUsernameOrEmailTakenURL);
					exit;
				}
				else
				{
					if(in_array("password", $SignupEngineRequiredFields)) {
						$enc_pass = encrypt($this->post['password'].$pwd_salt);
						$err = 0;
						if(isset($SignupEngineConfirmFields['password'])) {
							if($enc_pass != encrypt($this->post[$SignupEngineConfirmFields['password']].$pwd_salt)) {
								$err = 1;
							}
						}
						if(!$err) {
							$u = new UserTmo;
							$fields['password'] = $enc_pass;
							foreach($fields as $ke=>$va) {
								$func_name = "set".ucfirst($ke);
								$u->$func_name($va);
							}
							$u->insert();
							$this->additionalActions();
							header("location:".$SiteDomain.$SignupEngineOkURL);
							exit;
						}
						else
						{
							header("location:".$SiteDomain.$SignupEnginePwdDontMatchURL);
							exit;
						}
					}
				}
			}
		}	
	}
	$_engine = new SignupEngine;
}