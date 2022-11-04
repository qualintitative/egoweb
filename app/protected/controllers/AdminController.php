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
use app\models\Answer;
use app\models\Interview;
use app\models\Question;
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
        $dbConnect = \Yii::$app->get('db');
        $dFile = false;
        if(in_array("migration", $dbConnect->schema->getTableNames())){
        $dCount = (new \yii\db\Query())
        ->select(['version'])
        ->from('migration')
        ->all();
        $dFile = $dCount[count($dCount)-1]['version'].".php";
        }
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
        $interviews = [];
        $egoid_answers = [];
        $egoIds = [];

        $studyEgoIdQs = [];
        $studies = [];
        $multiStudies = [];
        $studyByName = [];
        $result = Yii::$app->user->identity->studies;
        $studyNames = [];
        foreach($result as $study){
            $studyNames[$study->id] = $study->name;
            if($study->multiSessionEgoId){
                $studyByName[$study->name] = $study;
                $studyEgoIdQs[] = $study->multiSessionEgoId;
            }else{
                $studies[] = $study;
            }
        }

        $result = Question::findAll([
            "id"=>$studyEgoIdQs,
        ]);

        $multiIdQs = [];
        foreach($result as $q){
            $multiIdQs[$studyNames[$q->studyId]] = $q->title;
        }
        ksort($multiIdQs, SORT_NATURAL | SORT_FLAG_CASE);
        asort($multiIdQs, SORT_NATURAL | SORT_FLAG_CASE);
        foreach($multiIdQs as $multi=>$title){
            $multiStudies[] = $studyByName[$multi];
        }

        $result = Answer::findAll([
            "questionType"=>"EGO_ID",
        ]);

        foreach ($result as $answer) {
            if($answer->answerType == "RANDOM_NUMBER" || $answer->answerType == "STORED_VALUE")
                continue;
            if(!isset($egoid_answers[$answer->interviewId]))
                $egoid_answers[$answer->interviewId] = [];
            $egoid_answers[$answer->interviewId][] = $answer->value;
        }

        $result = Interview::find()->where(["<>", "completed", "-1"])->all();

        foreach($result as $interview){
            if(!isset($egoid_answers[$interview->id]))
                $egoid_answers[$interview->id] = ["error"];
            $egoIds[$interview->id] = implode("_", $egoid_answers[$interview->id]);
            if(!isset($interviews[$interview->studyId]))
                $interviews[$interview->studyId] = [];
            $interviews[$interview->studyId][] = $interview;
        }
        return $this->render('index', ["interviews" => $interviews, "egoIds" => $egoIds, "studies"=>$studies, "multiStudies"=>$multiStudies, "multiIdQs"=>$multiIdQs]);
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
