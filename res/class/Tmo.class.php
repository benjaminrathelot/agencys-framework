<?php

// Form class
if(!class_exists("Tmo")) {
class Tmo {

	protected function checkMail($val) {
		if(filter_var($val, FILTER_VALIDATE_EMAIL)){  
			return true;
		}
		else
		{
			return false;
		}
	}
	protected function checkNumber($val) {
		if(filter_var($val, FILTER_VALIDATE_INT)){  
			return true;
		}
		else
		{
			return false;
		}
	}	
	
	protected function id()
	{
		return $this->id;
	}

	protected function glist($l, $arg, $val="--------TMO_DEFAULT") {
		if(isset($this->$l)) {
			$x = json_decode($this->$l,1);
			if(isset($x[$arg])) {
				if($val=="--------TMO_DEFAULT") {
					return $x[$arg];
				}
				else
				{
					$x[$arg] = $val;
					$this->$l = json_encode($x);
					$this->update($l);
				}
			}
		}
	}
}

}