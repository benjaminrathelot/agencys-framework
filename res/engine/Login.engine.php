<?php
// Login Engine - (c) 2014 Benjamin Rathelot
if(!class_exists("LoginEngine")) {
	include("sh/b");
	class LoginEngine extends Engine {

		public function run() {
			include(p("cf:encrypt"));
			include(p("cf:LoginEngine"));
			include(p("t:User"));
			include(p("cf:site"));
			if(isset($this->session['user_id']) AND $this->session['user_id']!=0) {
				header("location:".$SiteDomain.$LoginEngineAlreadyOnlineURL);
				exit;
			}
			if(isset($this->post['ident'], $this->post['password']) AND !empty($this->post['ident']) AND !empty($this->post['password'])) {
				$u = new UserTmo;
				switch($loginEngineAuthType) {
					case "email":
						$u->from(array("email"=>secure($this->post['ident'])));
					break;
					case "username":
						$u->from(array("username"=>secure($this->post['ident'])));
					break;
					case "both":
						$u->from(array("email"=>secure($this->post['ident']), "username"=>secure($this->post['ident'])), "OR");
					break;
					case "id":
						$u->from(array("id"=>secure($this->post['ident'])));
					break;
					default:
						$u->from(array("username"=>secure($this->post['ident'])));
					break;
				}
				if($u->get()) {
					if(encrypt($this->post['password'].$pwd_salt) == $u->getPassword()) {
						@session_start();
						$_SESSION['user_id'] = $u->getId();
						header("location:".$SiteDomain.$LoginEngineAuthOkURL);
						exit;
					}
					else
					{
						header("location:".$SiteDomain.$LoginEngineInvalidDataURL);
					}
				}
				else
				{
					header("location:".$SiteDomain.$LoginEngineInvalidDataURL);
				}
			}
			else
			{
				header("location:".$SiteDomain.$LoginEngineLoginFormURL);
				exit;
			}
		}
	}
	$_engine = new LoginEngine;
}