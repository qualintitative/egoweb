<?php
class WebUser extends CWebUser{
	private $_user;

	// get the logged user
	function getUser(){
		if($this->_user===null){
			$this->_user=User::model()->findByPk($this->id);
		}
		return $this->_user;
 	}
}
?>