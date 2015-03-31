<?php

class SiteController extends Controller
{
		public $counter;

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		if(count(User::model()->findAll()) == 0){
			$model = new User;
			if(isset($_POST['User'])){
				$model->attributes = $_POST['User'];
				$model->permissions = 11;
				$salt = User::generateSalt();
				$password = $model->password;
				$model->password = User::hashPassword($model->password,$salt).':'.$salt;
				$model->confirm = $model->password;
				if($model->save()){
    				$model = User::model()->findByPk($model->id);
                    $login = new LoginForm;
                    $login->username = $model->email;
                    $login->password = $password;
                    // validate user input and redirect to the previous page if valid
        			if($login->validate() && $login->login()){
                        $this->redirect(array('/admin'));
        			}
				}
			}
			$this->render('create', array(
				'model'=>$model,
			));
		}else{
			$this->render('index');
		}
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model = $this->captchaRequired()? new LoginForm('captchaRequired') : new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->createUrl("admin"));
			else
			{
				// Otherwise, increment the login attempt counter for captcha
				$this->counter = Yii::app()->session->itemAt('captchaRequired') + 1;
				Yii::app()->session->add('captchaRequired',$this->counter);
			}
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

		private function captchaRequired()
		{
			// Captcha is required if the login attempt count is equal to or greater than the maximum allowed.
			return Yii::app()->session->itemAt('captchaRequired') >= Yii::app()->params['maxLoginAttempts'];
		}

}