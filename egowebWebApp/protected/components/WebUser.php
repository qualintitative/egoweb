<?php
class WebUser extends CWebUser{
	private $_user;

	// is the user a superadmin
	function getIsSuperAdmin(){
		if(!isset($this->user->permissions))
			return false;
		return ($this->user && $this->user->permissions>=11);
	}


	// get the logged user
	function getUser(){
		if($this->_user===null){
			$this->_user=User::model()->findByPk($this->id);
		}
		return $this->_user;
 	}
}
?>