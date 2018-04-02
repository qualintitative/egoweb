<?php
class Plugin extends CWidget{
	public $method;
	public $id;
	public $event;

	public function actionAdminMenu(){
		//this function will add a button to the adminbar.
	}

	public function actionSettingsMenu(){
		//this function will add a button to the settings tab.
	}

	public function actionPostForm(){
		//this  function will add fields to the post form
	}

	public function actionIndex(){
		//this is the default action
	}

	public function run(){
		if($this->method){
			$method = 'action'. ucfirst($this->method);
			if(method_exists($this,$method)){
				$this->$method();
			}else{
				$this->actionIndex();
			}
		}else if ($this->event) {
      echo $this->event;
      die();
			$event = 'on' . ucfirst($this->event);
			if(method_exists($this,$event))
				$this->$event();
      else
  			$this->actionIndex();
		}else{
			$this->actionIndex();
		}

	}

	public function runWithParams(){
		$this->run();
	}

}
