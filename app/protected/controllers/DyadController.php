<?php

class DyadController extends Controller
{

	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			//'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(Yii::app()->controller->action->id),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{

		$condition = "id != 0";
		if(!Yii::app()->user->isSuperAdmin){
            $studies = array();
            $criteria = new CDbCriteria;
            $criteria->condition = "active = 1 AND interviewerId = " . Yii::app()->user->id;
            $interviewers = Interviewer::model()->findAll($criteria);
            foreach($interviewers as $interviewer){
                $studies[] = $interviewer->studyId;
            }
            if($studies)
				$condition = "id IN (" . implode(",", $studies) . ") AND active = 0";
			else
				$condition = "id = -1 AND active = 0";
		}


		$criteria = array(
			'condition'=>$condition . " AND multiSessionEgoId = 0 AND active = 0",
			'order'=>'id DESC',
		);

		$single = Study::model()->findAll($criteria);

		$criteria = array(
			'condition'=>$condition . " AND multiSessionEgoId <> 0 AND active = 0",
			'order'=>'multiSessionEgoId DESC',
		);

		$multi = Study::model()->findAll($criteria);

 		$this->render('index',array(
			'model'=>$model,
			'single'=>$single,
			'multi'=>$multi,

		));
	}

	public function actionDelete($id)
	{
		$study = Study::model()->findByPk((int)$id);
		$study->delete();
		Yii::app()->request->redirect("/archive");
	}

	public function actionRestore($id)
	{
		$study = Study::model()->findByPk((int)$id);
		$study->active = 1;
		$study->save();
		Yii::app()->request->redirect("/authoring/edit/" . $study->id);
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}
