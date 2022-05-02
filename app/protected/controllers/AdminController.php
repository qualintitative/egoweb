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
 * Admin controller
 * home page for logged in user
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
        $mFiles = scandir(__DIR__."/../../console/migrations/");
        $mFile = array_pop($mFiles);
        $dCount = (new \yii\db\Query())
        ->select(['version'])
        ->from('migration')
        ->all();
        $dFile = $dCount[count($dCount)-1]['version'].".php";
        // check if migrations are up to date;
        if ($mFile != $dFile) {
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
            Yii::$app->session->setFlash('success', 'Migrated database');
            return $this->response->redirect(Url::toRoute('/admin'));
        }
        $this->view->title = "EgoWeb 2.0";
        return $this->render('index');
    }

    public function actionUser()
    {
        $this->view->title = "EgoWeb 2.0";
        $result = User::find()->all();
        $users = [];
        $userExists = false;
        foreach ($result as $user) {
            $user->generatePasswordResetToken();
            $userA = $user->toArray();
            $user->save();
            $userA['link'] = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
            $users[] = $userA;
            if (Yii::$app->request->isPost) {
                if (trim($_POST['User']['email']) == $userA['email']) {
                    $userExists = true;
                }
            }
        }
        if (Yii::$app->request->isPost) {
            $user = new User;
            if ($user->load(Yii::$app->request->post()) && $user->validate()) {
                if ($userExists == false) {
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $user->name = $_POST['User']['name'];
                    $user->email = trim($_POST['User']['email']);
                    $user->permissions = $_POST['User']['permissions'];
                    $user->password = Yii::$app->security->generateRandomString();
                    if ($user->save()) {
                        Yii::$app->session->setFlash('success', 'Created new user');
                        return $this->response->redirect(Url::toRoute('/admin/user'));
                    } else {
                        Yii::$app->session->setFlash('error', 'Error creating user');
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'User email already exists: ' . trim($_POST['User']['email']));
                    return $this->response->redirect(Url::toRoute('/admin/user'));
                }
            }
        }
        $roles = [];
        foreach (User::roles() as $permission=>$role) {
            $roles[$permission] = ["text"=>$role, "value"=>$permission];
        }
        return $this->render('user', [
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
            if ($user->email != trim($_POST['User']['email'])) {
                if (User::findByUsername($_POST['User']['email']) != null) {
                    Yii::$app->session->setFlash('error', 'User email already exists: ' .  $_POST['User']['email']);
                    return $this->response->redirect(Url::toRoute('/admin/user'));
                }
            }
            $user->name = $_POST['User']['name'];
            $user->email = trim($_POST['User']['email']);
            $user->permissions = $_POST['User']['permissions'];
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Updated user ' .  $_POST['User']['email']);
                return $this->response->redirect(Url::toRoute('/admin/user'));
            } else {
                Yii::$app->session->setFlash('error', 'Error creating user');
            }
        }
    }

    public function actionUserdelete()
    {
        if (isset($_POST['User']['id']) && Yii::$app->user->identity->id != $_POST['User']['id']) {
            $user = User::findOne($_POST['User']['id']);
            $email = $user->email;
            $user->delete();
            Yii::$app->session->setFlash('success', 'Deleted user ' . $email);
        } else {
            Yii::$app->session->setFlash('error', 'Cannot your own account');
        }
        return $this->response->redirect(Url::toRoute('/admin/user'));
    }

    public function actionErrors()
    {
        $text = "";
        $myfile = fopen(getcwd() . "/protected/runtime/logs/app.log", "r") or die("Unable to open file!");
        while (!feof($myfile)) {
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
