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
                'actions'=>array('resetpass', 'forgot'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'download', 'user', 'useredit', 'userdelete', 'getlink', 'migrate', 'update'),
                'users'=>array('@'),
            ),
      array('allow', // allow authenticated user to perform 'create' and 'update' actions
        'actions'=>array('reencrypt', 'redata'),
        'expression'=>'$user->isSuperAdmin',
      ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionReencrypt()
    {
        $alert = false;
        $encKey = "old_key1old_key1";
        $oldAlg = Yii::app()->securityManager->cryptAlgorithm;
        $oldKey = Yii::app()->securityManager->getEncryptionKey();
        $cmd = Yii::app()->db->createCommand("SELECT * FROM interview");
        $rows = $cmd->queryAll();
        $interviews = array();
        foreach ($rows as $row) {
            $interviews[] = $row['id'];
        }
        $this->render('reencrypt', array(
          "encKey" => $encKey,
          "oldKey" => $oldKey,
          "alert"=>$alert,
          "oldAlg" => $oldAlg,
          "interviews"=>json_encode($interviews),
        ));
    }

    public function actionRedata()
    {
        if (isset($_POST['newKey']) &&  in_array(strlen(($_POST['newKey'])), array(16,24,32))) {
            if ($_POST['interviewId'] == "0") {
                $time_start = microtime(true);
                $encKey = $_POST['newKey'];
                Yii::app()->db->createCommand("ALTER TABLE `alterList` CHANGE `name` `name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL")->execute();
                Yii::app()->db->createCommand("ALTER TABLE `alterList` CHANGE `email` `email` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL")->execute();
                $cmd = Yii::app()->db->createCommand("SELECT * FROM user");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    $changeArray = array();

                    if (strlen(trim($row["name"])) > 0) {
                        $changeArray['name'] = decrypt($row["name"]);
                    }

                    if (strlen(trim($row["email"])) > 0) {
                        $changeArray['email'] = decrypt($row["email"]);
                    }

                    if (count($changeArray) > 0) {
                        $update = Yii::app()->db->createCommand();
                        $update->update('user', $changeArray, 'id='.$row["id"]);
                    }
                }

                $cmd = Yii::app()->db->createCommand("SELECT * FROM alterList");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    $changeArray = array();

                    if (strlen(trim($row["name"])) > 0) {
                        $changeArray['name'] = decrypt($row["name"]);
                        if (false === mb_check_encoding ($changeArray['name'] , "UTF-8" ) ){
                          $changeArray['name'] = utf8_encode($changeArray['name']);
                        }
                    }
                    if (strlen(trim($row["email"])) > 0) {
                        $changeArray['email'] = decrypt($row["email"]);
                        if (false === mb_check_encoding ($changeArray['email'] , "UTF-8" ) ){
                          $changeArray['email'] = utf8_encode($changeArray['email']);
                        }
                    }

                    if (count($changeArray) > 0) {
                        $update = Yii::app()->db->createCommand();
                        $update->update('alterList', $changeArray, 'id='.$row["id"]);
                    }
                }

                $cmd = Yii::app()->db->createCommand("SELECT * FROM notes");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    if (strlen(trim($row["notes"])) > 0) {
                        $decrypted = decrypt($row["notes"]);
                        $update = Yii::app()->db->createCommand();
                        $update->update('notes', array( 'notes'=>$decrypted ), 'id='.$row["id"]);
                    }
                }

                Yii::app()->securityManager->cryptAlgorithm = "rijndael-128";
                Yii::app()->securityManager->setEncryptionKey($_POST['newKey']);

                $cmd = Yii::app()->db->createCommand("SELECT * FROM user");
                $rows = $cmd->queryAll();
                foreach ($rows as $row) {
                    $changeArray = array();

                    if (strlen(trim($row["name"])) > 0) {
                        $changeArray['name'] = encrypt($row["name"]);
                    }

                    if (strlen(trim($row["email"])) > 0) {
                        $changeArray['email'] = encrypt($row["email"]);
                    }

                    if (count($changeArray) > 0) {
                        $update = Yii::app()->db->createCommand();
                        $update->update('user', $changeArray, 'id='.$row["id"]);
                    }
                }

                $cmd = Yii::app()->db->createCommand("SELECT * FROM alterList");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    $changeArray = array();

                    if (strlen(trim($row["name"])) > 0) {
                        $changeArray['name'] = encrypt($row["name"]);
                    }

                    if (strlen(trim($row["email"])) > 0) {
                        $changeArray['email'] = encrypt($row["email"]);
                    }

                    if (count($changeArray) > 0) {
                        $update = Yii::app()->db->createCommand();
                        $update->update('alterList', $changeArray, 'id='.$row["id"]);
                    }
                }
                $cmd = Yii::app()->db->createCommand("SELECT * FROM notes");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    if (strlen(trim($row["notes"])) > 0) {
                        $encrypted = encrypt($row["notes"]);
                        $update = Yii::app()->db->createCommand();
                        $update->update('notes', array( 'notes'=>$encrypted ), 'id='.$row["id"]);
                    }
                }
                $time_end = microtime(true);

                //dividing with 60 will give the execution time in minutes otherwise seconds
                $execution_time = ($time_end - $time_start);
                echo "success";
            } else {
                $cmd = Yii::app()->db->createCommand("SELECT * FROM answer WHERE interviewId = " . $_POST['interviewId']);
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    $changeArray = array();

                    if (strlen(trim($row["value"])) > 0) {
                        $changeArray['value'] = decrypt($row["value"]);
                    }

                    if (strlen(trim($row["otherSpecifyText"])) > 0) {
                        $changeArray['otherSpecifyText'] = decrypt($row["otherSpecifyText"]);
                    }

                    if (count($changeArray) > 0) {
                        $update = Yii::app()->db->createCommand();
                        $update->update('answer', $changeArray, 'id='.$row["id"]);
                    }
                }

                $cmd = Yii::app()->db->createCommand("SELECT * FROM alters WHERE FIND_IN_SET(" . $_POST["interviewId"] . ", interviewId)");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    if (strlen(trim($row["name"])) > 0) {
                        $decrypted = decrypt($row["name"]);
                        $update = Yii::app()->db->createCommand();
                        $update->update('alters', array( 'name'=>$decrypted ), 'id='.$row["id"]);
                    }
                }

                Yii::app()->securityManager->cryptAlgorithm = "rijndael-128";
                Yii::app()->securityManager->setEncryptionKey($_POST['newKey']);

                $cmd = Yii::app()->db->createCommand("SELECT * FROM answer WHERE interviewId = " . $_POST['interviewId']);
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    $changeArray = array();

                    if (strlen(trim($row["value"])) > 0) {
                        $changeArray['value'] = encrypt($row["value"]);
                    }

                    if (strlen(trim($row["otherSpecifyText"])) > 0) {
                        $changeArray['otherSpecifyText'] = encrypt($row["otherSpecifyText"]);
                    }

                    if (count($changeArray) > 0) {
                        $update = Yii::app()->db->createCommand();
                        $update->update('answer', $changeArray, 'id='.$row["id"]);
                    }
                }
                $cmd = Yii::app()->db->createCommand("SELECT * FROM alters WHERE FIND_IN_SET(" . $_POST["interviewId"] . ", interviewId)");
                $rows = $cmd->queryAll();

                foreach ($rows as $row) {
                    if (strlen(trim($row["name"])) > 0) {
                        $encrypted = encrypt($row["name"]);
                        $update = Yii::app()->db->createCommand();
                        $update->update('alters', array( 'name'=>$encrypted ), 'id='.$row["id"]);
                    }
                }
                echo "success";
            }
        }
    }

    public function actionIndex()
    {
        $count = 0;
        $fi = new FilesystemIterator(Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR, FilesystemIterator::SKIP_DOTS);
        foreach ($fi as $file) {
            if (substr($file->getFilename(), -4) == ".php") {
                $count++;
            }
        }
        $dbCount = Yii::app()->db->createCommand()
    ->select('count(version)')
    ->from('tbl_migration')
    ->queryScalar();
        $alert = false;
        if ($count > $dbCount) {
            $alert = $this->runMigrationTool() . "<br>";
        }
        if (Yii::app()->user->isSuperAdmin && extension_loaded('openssl') && Yii::app()->securityManager->cryptAlgorithm != "rijndael-128") {
            $alert = "The encryption on your server is outdated and will not run on Php 7.2 or above.  If you have access to the config file (main.php), please consider running the re-encryption tool.  <a href='/admin/reencrypt'>Click here to run the re-encryption tool</a>";
        }
        $this->render('index', array(
      "alert"=>$alert,
    ));
    }

    public function actionUser()
    {
        if (isset($_POST['User'])) {
            if ($_POST['User']['id']) {
                $model =  User::model()->findByPk((int)$_POST['User']['id']);
                $model->attributes=$_POST['User'];
            } else {
                $model =  new User;
                $model->attributes=$_POST['User'];
                $salt = User::generateSalt();
                $model->password = "rand";
                $model->password=User::hashPassword($model->password, $salt).':'.$salt;
            }
            $model->confirm=$model->password;

            if ($model->validate()) {
                if ($model->save()) {
                    $subject='=?UTF-8?B?'.base64_encode(Yii::app()->name." - Reset Password").'?=';
                    $headers="From: ".Yii::app()->params['adminEmail']."\r\n".
                        "Reply-To: ".Yii::app()->params['adminEmail']."\r\n".
                        "MIME-Version: 1.0\r\n".
                        "Content-type: text/html; charset=UTF-8\r\n";
                    $message = 'To reset your password, click on the link below:<br><br>'.
                        Yii::app()->getBaseUrl(true).$this->createUrl('admin/resetpass').'/'.$model->id.':'.
                        User::model()->hashPassword($model->password, 'miranda');
                    mail($model->email, $subject, $message, $headers);
                    $this->redirect($this->createUrl('admin/user'));
                } else {
                    $error = Yii::app()->errorHandler->error;
                    print_r($error);
                    die();
                }
            } else {
                $error=$model->getErrors();
                print_r($error);
                die();
            }
        }

        $dataProvider=new CActiveDataProvider('User', array(
            //'criteria'=>$criteria,
            'pagination'=>false,
        ));

        $this->render('user', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * actionUserEdit function.
     *
     * @access public
     * @return void
     */
    public function actionUserEdit()
    {
        if (isset($_GET['userId'])) {
            if (!is_numeric($_GET['userId'])) {
                throw new CHttpException(500, "Invalid userId specified ".$_GET['userId']." !");
            }
            $model = User::model()->findByPk((int)$_GET['userId']);
        } else {
            $model = new User;
        }
        $this->renderPartial('_form_user', array('user'=>$model, 'ajax'=>true), false, false);
    }

    public function actionUserDelete()
    {
        if (isset($_GET['userId'])) {
            $model = User::model()->findByPk((int)$_GET['userId']);
            $model->delete();
        }
        $dataProvider=new CActiveDataProvider('User', array(
            //'criteria'=>$criteria,
            'pagination'=>false,
        ));
        $this->renderPartial('_view_user', array('dataProvider'=>$dataProvider, 'ajax'=>true), false, false);
    }

    public function actionDownload()
    {
        $this->render('download');
    }

    public function actionGetlink()
    {
        if (Yii::app()->user->isSuperAdmin) {
            if (isset($_GET['email'])) {
                $users = User::model()->findAll();
                foreach ($users as $user) {
                    if ($user->email == $_GET['email']) {
                        echo "Password Reset Link for ". $user->name . "<br>" . Yii::app()->getBaseUrl(true).$this->createUrl('admin/resetpass').'/'.$user->id.':'.
                        User::model()->hashPassword($user->password, 'miranda');
                    }
                }
            }
        }
    }

    public function actionForgot()
    {
        if (isset($_POST['email'])&&$_POST['email']!='') {
            $email = $_POST['email'];
            $users = User::model()->findAll();
            foreach ($users as $user) {
                if ($user->email == $email) {
                    $model = $user;
                }
            }
            if ($model) {
                $subject='=?UTF-8?B?'.base64_encode(Yii::app()->name." - Reset Password").'?=';
                $headers="From: ".Yii::app()->params['adminEmail']."\r\n".
                    "Reply-To: ".Yii::app()->params['adminEmail']."\r\n".
                    "MIME-Version: 1.0\r\n".
                    "Content-type: text/html; charset=UTF-8\r\n";
                $message = 'To reset your password, click on the link below:<br><br>'.
                    Yii::app()->getBaseUrl(true).$this->createUrl('admin/resetpass').'/'.$model->id.':'.
                    User::model()->hashPassword($model->password, 'miranda');
                mail($email, $subject, $message, $headers);

                Yii::app()->user->setFlash('success', 'We have sent you an email with instructions on how to reset your password.  Good luck');
            } else {
                Yii::app()->user->setFlash('error', 'Error!  Email not found!');
            }
            $this->refresh();
        }
        // display the forgot form
        $this->render('forgot');
    }

    public function actionResetpass()
    {
        if (!isset($_GET['id'])) {
            $this->redirect($this->createUrl('user/forgot'));
        }
        list($id, $hash)=preg_split('/:/', $_GET['id']);
        $model = User::model()->findByPk($id);
        if ($model && User::model()->hashPassword($model->password, 'miranda')==$hash) {
            $model->password='';
            if (isset($_POST['User'])) {
                $model->attributes=$_POST['User'];
                $salt=User::model()->generateSalt();
                $password=$model->password;
                $model->password=User::model()->hashPassword($model->password, $salt).':'.$salt;
                $model->confirm=$model->password;
                if ($model->save()) {
                    $model->email = decrypt($model->email);
                    $login=new LoginForm;
                    $login->username=$model->email;
                    $login->password=$password;
                    if ($login->validate() && $login->login()) {
                        $this->redirect($this->createUrl('/admin'));
                    }
                } else {
                    throw new CHttpException(500, print_r($model->errors));
                }
            }
            $this->render('reset', array(
                'model'=>$model,
            ));
        } else {
            $this->redirect($this->createUrl('forgot'));
        }
    }

    public function actionMigrate()
    {
        $this->runMigrationTool();
    }

    public function actionUpdate()
    {
        echo $this->runGitUpdate();
    }

    private function runMigrationTool()
    {
        $commandPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
        $runner = new CConsoleCommandRunner();
        $runner->addCommands($commandPath);
        $commandPath = Yii::getFrameworkPath() . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'commands';
        $runner->addCommands($commandPath);
        $args = array('yiic', 'migrate', '--interactive=0');
        ob_start();
        $runner->run($args);
        return htmlentities(ob_get_clean(), null, Yii::app()->charset);
    }

    private function runGitUpdate()
    {
        $commandPath = Yii::app()->getBasePath() . "/../../";
        $runner = new CConsoleCommandRunner();
        $runner->addCommands($commandPath);

        $commandPath = Yii::getFrameworkPath() . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'commands';
        $runner->addCommands($commandPath);
        $args = array('git', 'pull', '');
        ob_start();
        $runner->run($args);
        echo htmlentities(ob_get_clean(), null, Yii::app()->charset);
    }
}
