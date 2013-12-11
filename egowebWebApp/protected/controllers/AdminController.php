<?php

class AdminController extends Controller
{

	/**
	 * @return array action filters
	 */
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
				'actions'=>array('index', 'download', 'user'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionIndex()
	{
		$this->render('index');
	}

	public function actionUser()
	{
		$dataProvider=new CActiveDataProvider('User',array(
            //'criteria'=>$criteria,
            'pagination'=>false,
        ));

		if(isset($_POST['User']))
		{
			// store the pic

			$model->attributes=$_POST['User'];
			if($model->validate())
			{
				// hash the password
				$salt=User::generateSalt();
				$password=$model->password;
				$model->password=User::hashPassword($model->password,$salt).':'.$salt;
				$model->confirm=$model->password;

				if($model->save()){
					$this->redirect($this->createUrl('user/profile/'.Yii::app()->user->getId()));
				}else{
					$error = Yii::app()->errorHandler->error;
					print_r($error);
				}
			}else{
				$error=Yii::app()->errorHandler->error;
				print_r($error);
			}

		}
		$this->render('user', array(
			'dataProvider' => $dataProvider,
		));
	}

	public function actionDownload()
	{
		$this->render('download');
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