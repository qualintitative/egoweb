<?php
class Classjs extends CBehavior {
    public $params=array();
    // more methods here
    public function setClassjs($classjs, $params=array()) {
	$this->params=$params;
	Yii::app()->params['classjs'] = $classjs;
    }
    // more methods here
    public function getClassjs() {
	if (isset(Yii::app()->params['classjs'])&&Yii::app()->params['classjs']) return Yii::app()->params['classjs'];
	else return 'MasterClass';
    }
    public function getClassjsParams() {
	return $this->params;
    }
}