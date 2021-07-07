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
        return $this->render('index');
    }

    public function actionUser()
    {
        $result = User::find()->all();
        $users = [];
        foreach($result as $user){
            $users[] = $user->toArray();
        }
        $user = new User;
        if ($user->load(Yii::$app->request->post()) && $user->validate()) {
            $user->generateAuthKey();
            $user->generatePasswordResetToken();
            $user->name = $_POST['User']['name'];
            $user->email = $_POST['User']['email'];
            $user->password = Yii::$app->security->generateRandomString();
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                return $this->response->redirect(Url::toRoute('/admin/user'));
            }else{
                Yii::$app->session->setFlash('error', 'Error creating user');
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

    public function actionMigrate()
    {
        echo $this->runMigrationTool()  . "<br>done!";
    }

    public function actionUpdate()
    {
        echo $this->runGitUpdate();
    }

    private function runMigrationTool()
    {
        echo "running migration tool...";
        $migration = new Controllers\MigrateController("migrate",Yii::$app);
        return $migration->run('migrate', ['migrationPath' => '@console/migrations/']);
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
