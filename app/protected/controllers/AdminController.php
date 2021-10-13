<?php
namespace app\controllers;

use app\models\ResendVerificationEmailForm;
use app\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\helpers\Tools;
use app\models\User;
use app\models\SignUpForm;
use yii\helpers\Url;

/**
 * Site controller
 */
class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $this->view->title = "EgoWeb 2.0";
        return $this->render('index');
    }

    public function actionUser()
    {
        $this->view->title = "EgoWeb 2.0";
        $result = User::find()->all();
        $users = [];
        $userExists = false;
        foreach($result as $user){
            $user->generatePasswordResetToken();
            $userA = $user->toArray();
            $user->save();
            $userA['link'] = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
            $users[] = $userA;
            if(isset($_POST['User']) && $_POST['User']['email'] == $user['email'])
                $userExists = true;
        }
        $user = new User;
        if ($user->load(Yii::$app->request->post()) && $user->validate()) {
            if ($userExists == false) {
                $user->generateAuthKey();
                $user->generatePasswordResetToken();
                $user->name = $_POST['User']['name'];
                $user->email = $_POST['User']['email'];
                $user->permissions = $_POST['User']['permissions'];
                $user->password = Yii::$app->security->generateRandomString();
                if ($user->save()) {
                    Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                    return $this->response->redirect(Url::toRoute('/admin/user'));
                } else {
                    Yii::$app->session->setFlash('error', 'Error creating user');
                }
            }else{
                Yii::$app->session->setFlash('error', 'Error creating user');
                return $this->response->redirect(Url::toRoute('/admin/user'));
            }
        }
        $roles = [];
        foreach(User::roles() as $permission=>$role){
            $roles[] = ["text"=>$role, "value"=>$permission];
        }
        return $this->render('user',[
            "users"=>$users,
            "roles"=>$roles,
        ]);
    }

    public function actionUserEdit()
    {
        if (isset($_POST['User']['id'])) {
            if (!is_numeric($_POST['User']['id'])) {
                throw new CHttpException(500, "Invalid userId specified ".$_GET['userId']." !");
            }
            $user = User::findOne($_POST['User']['id']);
            $user->name = $_POST['User']['name'];
            $user->email = $_POST['User']['email'];
            $user->permissions = $_POST['User']['permissions'];
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                return $this->response->redirect(Url::toRoute('/admin/user'));
            }else{
                Yii::$app->session->setFlash('error', 'Error creating user');
            }
        } else {
            $model = new User;
        }
    }

    public function actionUserdelete()
    {
        if (isset($_POST['User']['id']) && Yii::$app->user->identity->id != $_POST['User']['id']) {
            $model = User::findOne($_POST['User']['id']);
            $model->delete();
        }
        return $this->response->redirect(Url::toRoute('/admin/user'));
    }

    public function actionErrors()
    {
        $text = "";
        $myfile = fopen(getcwd() . "/protected/runtime/logs/app.log", "r") or die("Unable to open file!");
        while(!feof($myfile)) {
            $text .= fgets($myfile) . "<br>";
        }
        fclose($myfile);
        return $this->renderAjax("/layouts/ajax", ["json"=>$text]);
    }

    public function actionMigrate()
    {
        $oldApp = \Yii::$app;
        new \yii\console\Application([
            'id'            => 'Command runner',
            'basePath'      => '@app',
            'components'    => [
                'db' => $oldApp->db,
            ],
        ]);
        \Yii::$app->runAction('migrate/up', ['migrationPath' => '@console/migrations/', 'interactive' => false]);
        \Yii::$app = $oldApp;
        echo  "<br>done!";
    }

    public function actionUpdate()
    {
        $path =  realpath("../");
        $op = shell_exec('cd ' .   $path . ' && /usr/bin/git pull 2>&1');
        \yii\helpers\VarDumper::dump($op, 10, 1);
    }

}
