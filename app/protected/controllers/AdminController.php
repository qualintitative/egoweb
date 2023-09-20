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
use app\models\Alters;
use app\models\Study;
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
        $mFiles = scandir(__DIR__ . "/../../console/migrations/");
        $mFile = array_pop($mFiles);
        $dbConnect = \Yii::$app->get('db');
        $dFile = false;
        if (in_array("migration", $dbConnect->schema->getTableNames())) {
            $dCount = (new \yii\db\Query())
                ->select(['version'])
                ->from('migration')
                ->all();
            $dFile = $dCount[count($dCount) - 1]['version'] . ".php";
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
        $result = Yii::$app->user->identity->studies;
        $studyNames = [];
        $singleStudyNames = [];
        $allStudies = [];
        foreach ($result as $study) {
            $studyNames[$study->id] = $study->name;
            $allStudies[$study->name] = $study;
            if ($study->multiSessionEgoId) {
                $studyEgoIdQs[] = $study->multiSessionEgoId;
            } else {
                $singleStudyNames[] = $study->name;
                $studies[] = $study;
            }
        }


        $result = Question::findAll([
            "id" => $studyEgoIdQs,
        ]);

        $multiIdQs = [];
        foreach ($result as $q) {
            $multiIdQs[$studyNames[$q->studyId]] = $q->title;
        }
        foreach ($studyNames as $studyName) {
            if (!in_array($studyName, array_keys($multiIdQs)) && !in_array($studyName, $singleStudyNames)) {
                $studies[] = $allStudies[$studyName];
            }
        }
        ksort($multiIdQs, SORT_NATURAL | SORT_FLAG_CASE);
        asort($multiIdQs, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($multiIdQs as $multi => $title) {
            $multiStudies[] = $allStudies[$multi];
        }

        $result = Answer::findAll([
            "questionType" => "EGO_ID",
        ]);

        foreach ($result as $answer) {
            if ($answer->answerType == "RANDOM_NUMBER" || $answer->answerType == "STORED_VALUE")
                continue;
            if (!isset($egoid_answers[$answer->interviewId]))
                $egoid_answers[$answer->interviewId] = [];
            $egoid_answers[$answer->interviewId][] = $answer->value;
        }

        $result = Interview::find()->where(["<>", "completed", "-1"])->all();

        foreach ($result as $interview) {
            if (!isset($egoid_answers[$interview->id]))
                $egoid_answers[$interview->id] = ["error"];
            $egoIds[$interview->id] = implode("_", $egoid_answers[$interview->id]);
            if (!isset($interviews[$interview->studyId]))
                $interviews[$interview->studyId] = [];
            $interviews[$interview->studyId][] = $interview;
        }
        return $this->render('index', ["interviews" => $interviews, "egoIds" => $egoIds, "studies" => $studies, "multiStudies" => $multiStudies, "multiIdQs" => $multiIdQs]);
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
            $userA['link'] = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password/' . $user->password_reset_token]);
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
        foreach (User::roles() as $permission => $role) {
            $roles[$permission] = ["text" => $role, "value" => $permission];
        }
        return $this->render('user', [
            "users" => $users,
            "roles" => $roles,
        ]);
    }

    public function actionUserEdit()
    {
        if (isset($_POST['User']['id'])) {
            if (!is_numeric($_POST['User']['id'])) {
                throw new CHttpException(500, "Invalid userId specified " . $_GET['userId'] . " !");
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
        return $this->renderAjax("/layouts/ajax", ["json" => $text]);
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

    public function actionRemovedupes()
    {
        if (Yii::$app->user->identity->isSuperAdmin()) {

            $result = Answer::findAll([
                "questionType" => "EGO_ID",
            ]);

            $egoid_answers = [];
            $interviews = [];
            foreach ($result as $answer) {
                if ($answer->answerType == "RANDOM_NUMBER" || $answer->answerType == "STORED_VALUE")
                    continue;
                if (!isset($egoid_answers[$answer->interviewId]))
                    $egoid_answers[$answer->interviewId] = [];
                $egoid_answers[$answer->interviewId][] = $answer->value;
            }

            $result = Interview::find()->all();

            foreach ($result as $interview) {
                if (!isset($egoid_answers[$interview->id]))
                    $egoid_answers[$interview->id] = ["error"];
                $egoIds[$interview->id] = implode("_", $egoid_answers[$interview->id]);
                if (!isset($interviews[$interview->studyId]))
                    $interviews[$interview->studyId] = [];
                $interviews[$interview->studyId][] = $interview;
                $allInterviewIds[] = $interview->id;
            }
            $alterAnswers = [];
            $answerbyId = [];
            $studyIds = [12, 38, 76];
            $answerbyId1 = [];
            $answerbyId2 = [];

            foreach ($studyIds as $studyId) {

                $allAnswers =  Answer::findAll([
                    "questionType" => "ALTER",
                    "studyId" => $studyId,
                ]);
                foreach($allAnswers as $answer){
                    if(!isset($answerbyId1[$answer->alterId1]))
                    $answerbyId1[$answer->alterId1] = [];
                    $answerbyId1[$answer->alterId1][] = $answer->id;
                    if(!isset($answerbyId2[$answer->alterId2]))
                    $answerbyId2[$answer->alterId2] = [];
                    $answerbyId2[$answer->alterId2][] = $answer->id;
                }
                $allAnswers =  Answer::findAll([
                    "questionType" => "ALTER_PAIR",
                    "studyId" => $studyId,
                ]);
                foreach($allAnswers as $answer){
                    if(!isset($answerbyId1[$answer->alterId1]))
                    $answerbyId1[$answer->alterId1] = [];
                    $answerbyId1[$answer->alterId1][] = $answer->id;
                    if(!isset($answerbyId2[$answer->alterId2]))
                    $answerbyId2[$answer->alterId2] = [];
                    $answerbyId2[$answer->alterId2][] = $answer->id;
                }
              //  $allPairAnswers =  Answer::findAll([
              //      "questionType" => "ALTER_PAIR",
              //      "studyId" => $studyId,
              //  ]);

              //  foreach ($allAnswers as $answer) {
             //       $alterAnswers[] = $answer->alterId1;
             //   }
             //   foreach ($allPairAnswers as $answer) {
             //       $alterAnswers[] = $answer->alterId1;
             //       $alterAnswers[] = $answer->alterId2;
             //   }
            }
         //   $alterAnswers = array_unique($alterAnswers);

            $checkNames = ["Ann L", "Glenn P", "Glo", "Mike", "Alex F", "Ben", "Geoff", "Rita", "Scott", "CG", "HN", "IDW", "JD", "MH", "SS", "Debra", "Mark", "Raffi", "Steve", "Ted", "Bob Croft", "Jeanne Trudeau", "Joey Mix", "Rachael Mix", "Ronda Davis", "Sid Mix", "Troy Davis", "John", "Kiana", "Mike", "brother", "sister", "Betty ", "Linda W", "Brandie ", "Britani", "Denise", "Nichelle", "Rodney", "Willie", "Diane", "Heather", "ed", "Amy Z", "Devon", "Greg", "Jay", "Karen", "Leilani", "Marie Door", "Mark", "Michael", "Scott", "Stacey", "Steve", "Ellie R", "Jami W", "Kathy S", "Kevin A", "Marti G", "Sharon S", "Toneya S", "Ann", "Barbara ", "Jenny", "Mary", "Teresa", "BeBe C", "Brenda W", "Deloris F.", "Joe G", "Madalyn T", "Michelle M", "Stepahnie C", "Brad", "Brett", "Hazel", "Jessica ", "Kenneth ", "Lisa", "Preacher", "Ron", "Sister", "Angie J", "Carol B", "Dale E", "Elizabeth W", "Megan P", "Robin R", "Sandy L", "Daniel", "Susan", "nancy", "Abbe", "Aunt Olga", "Jane", "Marcia", "Maureen ", "Mom", "Pastoe", "Barb S", "Bill M", "Cathy C", "Charlie S", "Dawn L", "Jan K", "Janet M", "Kevin M", "Lindsay C", "Maziar Z", "Meredith M", "Sandy S", "Jewel H", "Krista D", "Michelle D", "Paul S", "Sheila B", "Florence", "Janet", "Michelle", "Theresa", "Brandy", "Delora", "Linda", "besabe", "brittany", "doris", "jay", "ronda", "vicky", "vida", "Cecelia", "Jen Harold", "Kathy", "Madeleine", "Matt H", "Nora", "Sarah", "Carol m", "Gary m", "Lynette o", "Stan p", "Jane", "Jim", "Kel", "Lisa", "Sharen", "Steve", "Susan", "Connie", "Gail T", "Kathy", "Linda P", "Richard", "Alexis", "ED", "Melissa", "Nic", "Sharon", "Steve", "Ann R.", "Elaine M.", "Ellie C.", "Jerry W.", "John C.", "Halcyon", "James", "Max", "Rita", "Beth", "John", "Kerry", "Marcia", "Mike", "Nick", "Rick", "Wife", "Ahron", "Chaykee", "Mindel", "Nechama", "Sara", "Carlotta C", "Carmela C", "Dorothy D", "Lenny A", "Lisa A", "Angie H", "Charles B", "Nancy M", "Phyllis B", "Vickie B", "Diane Z", "Shawn H", "Barb", "Buddy", "Cherry", "Doris", "Joyce", "andris", "graham", "kayla", "dora", "glady", "terry", "Dyson G", "Barry", "Betty", "Carolyn", "Catherine", "Claudia", "Damon", "Sharon", "Sherry", "Amanda B/", "Barbara B.", "Betty T.", "Dick F.", "Jason S", "Jay S.", "Jenny S.", "Muffie B.", "Jolie", "Kathy", "maria", "maria", "Anne N", "Ben A", "Caroline H", "Chad H", "Christiana S", "Devlin A", "Lacy H", "Lisa M", "Martha H", "Sarah M", "Tom H", "Jason ", "Al", "Boo", "Danny ", "Diane", "Ronda", "Tea", "Anthony", "Brian", "Dwayne", "Sherry", "Tanya", "Cathy M.", "Georgia T.", "Gloria", "Marjie B.", "Rich T.", "Steve", "Bob", "Bob", "Jerry", "Jerry", "Judy", "Judy", "Linda", "Linda", "Bill", "Carolyn", "Coleen", "Pam", "Barbara", "Barry", "eileen", "jim", "Ana", "Annie", "Charlene", "Donna", "Gail", "Hap", "Jan", "Joanne", "Lenny", "Linda", "Mary", "Rena", "Theresa", "Dave", "Josh", "Kathleen", "Katy", "Keith", "Kevin", "Mary", "Shanna", "Sherryl", "Theresa", "Denise S", "Diane H", "Chuck", "Geri", "Jim", "Larry", "Louann", "Rick", "Rose", "Amber B", "Megan a", "Sylvia N", "Dan", "Miriam", "Troy", "Ginger G", "John R", "Kathy E", "Robert R", "ashley w", "devin w", "katrina", "Carlos", "Greg", "KK", "Ken", "Resa", "Val", "Ellen", "Irene", "Jeremy", "Rita", "Susan", "David", "Joe", "Rod", "mother", "Joan w", "Bob A", "Karin M", "Malcolm M", "Matt M", "Pat R", "Rose H", "Kevin R", "Robin W", "Sara M", "brenda", "cindy", "curtis", "kenneth", "raenetha", "rakenya", "Daigham B", "Dan Z", "Kevin H", "Lauren T", "Lori W", "Rudy B", "Ana", "Chalo", "Dad", "David", "Gabe", "Isabel", "Jacob", "Janet", "Jolie", "Santiago", "E", "Mal", "Marc w", "Tom s", "Troy a", "hairdresser", "sister", "Ann", "Ed", "Nurse", "diane", "mom", "pat", "sara", "ja", "jw", "sr", "Daleen K", "Jason P", "Jeremy P", "Rick P", "Sue T", "ANDY P", "BRUCE R", "EDDIE C", "GITTEL B", "JANE F", "PAT H", "SHARON P", "SUZANNE C", "TOM G", "TZIPPORAH S", "Daniel P", "Richard ", "amy", "Betsy", "David", "Julie", "Bryce F.", "Dalton F.", "Krissy F", "Marissa T.", "Maya F.", "Wilma M.", "db", "ng", "Jenny S", "Julia W", "Pat", "jim", "sandi", "steve", "tammy", "Antonio C", "Matt S", "Patty C", "Valeria C", "Eric B", "John C", "Michael M", "Pamela M", "son", "Jenni D.", "Josh D.", "Mike H.", "Bree", "Janice", "John", "Mikelle", "Sara", "Tammy", "lo", "Lor", "Terry", "Gigi", "Jesse", "Lona", "Papa", "Rodney", "Rowena", "J C", "K D", "L D", "Debbie", "Eileen", "Elise", "Eric", "Greta", "Jean", "Peter", "Philip Jr.", "Rose", "Cath", "Kar", "Mi", "Reh", "Brandon", "LizMary", "brother1", "brother2", "daughter1", "daughter2", "daughter3", "husband", "son", "Amy", "Larry", "Mer", "Robbie", "Ruth", "judy", "Carla", "Carol", "Gina", "Jacob", "Stephanie", "Sue", "Tony", "Warren", "mo", "Clair W", "Dale S", "Joan P", "Joanne H", "Karen S", "Robert W", "Amy", "Betty", "Dawn", "Judy", "Matt", "Nana", "Robert", "Yvonne", "Buzz", "June", "Kim", "Tom", "Cindy", "Jonathan", "Katie", "Kelley", "Amanda", "Jana", "Jim", "Kate", "Kathy", "Rodney", "Kris w", "jack n", "jean l", "jenn g", "terry l", "Cheri P", "Velvet H", "Venus V", "Vernadette L", "daughter #1", "daughter #2", "grandpa", "Ashley ", "Chris", "Elena", "Ginny", "Jane", "Lucie", "Mike", "Joan", "Leroy", "Rick", "Debbie", "Robert", "Tricia", "Deann T", "Esther H", "Stacie V", "Steve S", "Trevor T", "Burt B", "Hilary W", "Kim M", "Trevor B", "Sue", "Terri", "Jason", "Lesia", "Brenda B", "Jo H", "Martha H", "Mary S", "Mary T", "Molly H", "AF", "BH", "BW", "CH", "DBH", "EW", "LL", "SWH", "Jen m.", "Mari M.", "Oscar M.", "Vin m.", "Angela M", "Jane O", "Joan G", "John B", "Martha M", "Allison L", "Amelia G", "Dale D", "Sara F", "Adam", "Jason", "LaURA", "Linda", "Toni", "LONG PLAYING", "Roy", "Jess W", "Linda M", "Monica K", "Sandy E", "Sherrie S", "Steve M", "Sue G", "Tim M", "Ari", "Brittney", "Cameron", "Csthy", "Eloise", "JESSICA", "Kesha", "Kim", "Marilyn", "Norman", "Pat ", "Shirley ", "Tim", "Becky", "Charity", "Cindy", "Deb", "Gerry", "Gideon", "Janice", "Jim", "Mary", "Vickie", "David", "Mom", "Paula", "Shelley", "Clifton", "Jerlene", "Lee", "Mack ", "Steve", "Audrey H.", "Beth H.", "Bryan H.", "David H.", "Dennis M.", "Jeff D.", "John C.", "Karla A.", "Laura H.", "Barbara", "Despina", "Karen", "Micaiah", "Panos", "Renee", "Jennifer", "Mike", "Nancy", "Bill", "Debbie", "Dotty", "Judy", "Shari", "Louvenia ", "Marlon", "Peggy", "Brian", "Brooke", "Emily ", "Jennifer", "Skip", "cody s", "jack g", "larry r", "marg r", "phil j", "Balinda", "Steven", "Toni", "Lanell D", "Valerie B", "Wendy B", "Dad", "George G", "Jim C", "June K", "Kerri W", "Nikki U", "Nina M", "Crystal ", "Daniel ", "Elena ", "Mary", "Micaela ", "Ramon", "Rick", "Tony", "Arlene O", "Charlotte J", "Chris G", "Cindy D", "Debbie M", "Georgia R", "Karla M", "Lucy M", "Mali G", "debra", "Brenda", "Craig", "Dwayne ", "Gary", "Mike", "Stacy ", "Troy", "Chanon", "Dylan", "Jenny", "Jim", "Luke", "Mary", "Mom", "Addison", "India", "Karen", "Bubbi", "Joe", "Kim Cheramie", "Lucy", "Rosanna", "Ann P", "Beth O", "Denise O", "Robin C", "Terri M", "Amy M.", "Jeni H.", "Brenda", "Carolyn", "Josh", "Martina", "Todd", "Abbie C", "Brandi W", "Brandon B", "Charles B", "Chip F", "Cyndi G", "Dottie V", "Edie H", "Mike H", "Sam B", "Bill", "Kathleen", "Larry", "MaryAnn", "Debbie ", "Kiersten ", "Larry", "Jeff", "Linsie", "Lois", "Lorri", "Steve", "Annie  B.", "Derell B.", "Hattie S.", "Janie T.", "Jinny J.", "Marge A.", "Mond", "Tammie B.", "Tom B", "Wilbert B.", "Yvonne C.", "fred", "Darlene", "Glen", "Tommy", "barb p", "cyndie r", "john l", "justin m", "kevin b", "mary w", "tom l", "Esteban", "Han", "JJ", "Jeff", "Suchu", "Ma", "MoInlaw", "Glenda", "Rody", "kandi", "kim", "Bea", "Billy", "Debbie", "Lawanna", "Bob", "Carole", "Deb", "Denise", "Jan", "Linda", "Pastor", "Beth", "Craig", "Doug", "Shelley", "Cathy", "Ellen", "Gary", "Kathy", "Sue", "Tim", "BranA", "BrentA", "BrianA", "JA", "JimP", "JE", "JS", "brandon", "Kimberly  J.", "Kristen H.", "Mike G.", "Beka", "Cindy", "Don", "Kristin", "Lisa", "Patty", "Ron", "Simone", "Terry", "Wendy", "Amy", "Laura", "Scott", "Shelly", "Tom", "Louis", "Sonia", "Heather", "GayAnn M.", "Joy S.", "Judy D.", "Justin S.", "Marian Y.", "barbara h", "carol n", "dick r", "gary s", "lowie r", "lynn r", "Allison", "Andrew", "Brandon", "Jenny", "Jill", "Jon", "Lindsie", "Pam", "Rick", "Robert", "Anita", "Anna", "Yasmine ", "Doris Z", "Fred T", "Jim S", "Lisa H", "Lorenzo R", "Louis J", "Mickie R", "Carol", "Kim", "Mickey", "Sharyn", "Dad", "Delaney", "Diana", "JC", "James", "Mom", "Trav", "Aimee l", "Dan v", "Jason t", "Karen p", "Linda k", "Mary c", "Mary s", "Stacie v", "Ed", "Isabel", "Jackie", "Luis", "Susie", "Cory", "Eric", "Fred", "Greg", "Jan", "Bri", "Christina", "Shahitta", "Snooks", "Tashika", "Cristina E", "Kylan", "Loyd H", "Matthew O", "Paige C", "Sotero E", "Tara O", "Zach W", "Lisa G", "Micki O", "Nancy C", "Nick M", "Bill D.", "Dave S.", "Dennis V.", "Karen T.", "Kim A.", "Debi", "Jason", "Julie", "Kim", "Phyllis", "bea g", "judy k", "lynne f", "ruth j", "Cam", "Cathy", "Pat", "Sherri", "WILL C", "Julie", "Nicole", "Whitney", "husband", "kecia", "mom", "Boyfriend ", "Cousin", "Carolyn ", "Darrell", "Darrell Jr. ", "Deanna ", "Dolly", "Dreama", "Marian ", "Ricky", "Steve ", "Daphne", "Jamesha", "Lady", "Estella Z", "Jan S", "Larry F", "Monte S", "Nick H", "Tony S", "tony e", "Mikey", "Angel", "Ashley", "Korrie", "Suzanne", "Dawn", "Dawn", "Jett", "Jett", "Michele", "Michele", "Amanda ", "Bob", "Donnell", "Holly", "Michelle", "Natalie", "Nathaniel", "Jim c", "Tim", "Tom Bro", "Heather", "Austin", "Mom", "Pat", "Peter", "Abby", "Aunts", "Boyfriend", "Choco", "Cousins", "Carolyn B", "Christa H", "Jesse", "Kem", "Rejohnnie ", "Shannon ", "Andrew", "Dana", "David", "Dereck", "Jason", "Johnny", "Laurie", "Leslie", "Nick", "Patrick", "Patty", "Rick", "Sandy", "Wayne", "Elizabeth", "Lance", "Lillian", "Saundra", "Sylvia", "Ted", "Victoria", "Cheryl Y.", "Jennifer K.", "Nathan P.", "Paige P.", "Samara R.", "Chioma J", "Donna C", "Jasmine J", "Juan J", "Kamali J", "Mariah C", "Sherry S", "Daneka", "Elisa", "Mattie", "Rosie", "Yvonne", "AS", "CB", "ET", "EWL", "JT", "MS", "YL", "Dan L", "Danelle M", "Gale R", "Jill C", "Kim F", "LuAnn B", "Annie", "Becky", "Dave", "Genny", "Karin", "Mike", "Sherilee", "Vern", "Wife", "Carol P", "Diane H", "Gayle W", "dick T", "Brother", "Father", "Mother", "Sister", "Chris W", "David", "Brother", "Dad", "Mom", "Sister", "Alyssa", "Jenna", "Jenna M", "JoAnna", "Joe", "Kim", "Mike Eddi", "Mom", "Nana", "Charlie w.", "Dober", "Geneva. S.", "Hattie W.", "Mary B.", "Monica", "Roy D.", "Teon w.", "Ann S", "Caroline F", "Isaac F", "Jacob F", "Jean B", "Jpsh F", "Sam F", "Daniel S", "Deanna S", "Gerald D", "Jeremy D", "John S", "Rosie N", "Anthony S", "Bobby H", "Jackie C", "Quin D", "Ralph B", "Andrew", "Charles", "Cynt", "Cynthia", "Eric", "Kevin", "Lisa", "Adriane", "James", "Maggie", "Pamela", "Quintel", "Tamera", "Toneyce", "Victor", "Vincent", "Zandra", "anne", "cindy", "lisa", "marry", "sandra", "julie", "Dan U", "Donna S", "Heather R", "Maggie W", "Savannah C", "Steph C", "Briana ", "Luz", "Tiffany ", "Mom", "Sal", "Jenny", "Stephanie", "Steve", "Vinny", "Alison P", "Ana C", "David B", "Diana P", "Eileen S", "Gerrit P", "Janice O", "Pam B", "CB", "EA", "GA", "MP", "Aiden", "Amanda ", "April", "Galen", "Jason", "Jeremy", "Terence ", "Tyler", "Cathy", "Cheryl", "Frances", "Leslie", "Marie", "Rick", "Scot", "Staten", "Tom", "Ashley", "Betsy", "Dana", "Esther", "Jayne", "Marcie", "Marty", "bill", "julie", "kathy", "kelly", "margaret", "rick", "stephanie", "Carla", "Holly", "Jason", "John", "Josh", "Kathryn", "Ken", "Maria", "Sarah Paige", "Freddie", "Pennie", "Raul", "ArleneW", "Doris G", "Joy B", "Luther S", "Marsha F", "Melody C", "Rex L", "Somer E", "brian", "sara jo", "sara lynn", "terry", "Debbie", "Kathy", "Linda", "Penny", "Beth", "Chad", "Connie", "Dave", "Debbie", "Janet ", "Justin", "Dawn M", "Len L", "Maggie C", "Ruby H", "Sonny N", "Brian A", "Chris b", "David A", "Larry T", "Nigel A", "Rowan A", "Russ A", "Susan A", "EllenP", "Frank P", "Joe C", "Marty M", "Rachel K", "Aunt", "Jacqueline", "Uncle", "Te", "To", "dad", "grandma", "mom", "Alison B", "Frances ", "Brenda W.", "Chase W.", "Mandi N", "Louise", "Martin", "Mary Lou", "Mom", "Bob R", "Chuck B", "Connor H", "Erin H", "Kathy H", "Katie B", "Mary R", "Meg H", "Molly H", "Brenda r", "Jimmy d", "Katie k", "Linda e", "Michelle w", "Mike k", "Cathy", "Chantelle", "Deb", "Emi", "Kathy", "Linda", "Mary", "Nancy", "J", "K", "Y", "SP", "Amy", "Amy K", "Brad", "Britta", "Debra", "Erica", "Fred", "Henry", "Jackson", "Jennie", "Jessica", "Raylene", "Ang", "Connie", "Diane", "Dish", "Tdb", "A", "Billy", "Carol", "Cecil", "D", "Mc", "Mel", "Mom", "Nan", "R", "Ralph", "Wink", "Z", "Bob A", "Brenda W", "Jeffery W", "Rachel W", "Randy W", "Christopher B.", "Erin B.", "John", "Nina", "Rena", "Abe W", "Isaiah W", "Mike W", "Tom W", "Judy", "Kathy", "betsy", "Daidee", "Rich D", "Rose Ann", "Sam H", "Sue Y", "Wendy S", "Alissa", "Gregg D", "Kevin Y", "Pam R", "Patty W", "Sandy O", "Steve Y", "Adrian", "Barbara", "Carol", "June", "Margaret", "Barbara B.", "Feliecia N.", "Jack R"];

            
           
            $origIds = [22581,20257,23161,23276,19167,16764,17435,21153,12832,11756,13073,13064];
            $dupeIds = [54682,52459,52395,52310,51374,51141,50813,49989,49954,49812,49772,49770];
        //  $origIds = [11074,11076,11795,11812,13701,13703,13705,13708,13721,22094,22095,22096,22098,22109,22110,16782,16784,16785,16786,16787,10840,10841,10842,10843,10844,10845,10848,14255,14256,14267,23370,23371,15383,15384,17399,17400,17401,17403,17409,17410,17435,17436,17437,23757,23759,23760,23761,23762,23763,23764,23767,23768,23775,23776,23794,11198,11205,11208,11209,11210,11223,11228,22677,22679,22681,22682,22683,20927,20929,20931,20933,20935,20938,20940,24132,24133,24134,24136,24137,24139,24141,24142,24151,13894,13895,13896,13900,13901,13904,13943,12930,12931,12938,23908,23909,23913,23914,23916,23922,23923,17587,17588,17589,17590,17591,17594,17606,17608,17610,17619,17634,17636,16742,16743,16744,16745,16746,11193,11195,11216,11219,13824,13825,13830,19796,19797,19801,19804,19806,19809,19813,23639,23640,23643,23644,23645,23647,23648,22335,22337,22338,22341,12935,12941,12946,12957,13008,13013,13017,20705,20708,20709,20712,20713,20328,20329,20330,20331,20335,20336,14030,14034,14035,14036,14044,11278,11279,11287,11289,23171,23172,23173,23174,23175,23176,23178,23179,19381,19382,19383,19384,19385,11863,11868,11870,11871,11893,11128,11140,11143,11147,11152,11378,11400,25051,25053,25054,25059,25061,24508,24512,24516,14956,14957,14965,21304,16715,16716,16717,16718,16719,16720,16722,16723,23150,23151,23154,23155,23157,23159,23160,23161,15920,15923,18256,13929,13930,13933,13935,13936,13940,13944,13949,13950,13951,13952,13581,22577,22578,22580,22581,22582,22587,20990,20992,20993,20996,20997,20448,20449,20451,20452,20457,20459,35330,35331,35332,35334,20512,20513,20514,20518,17485,17486,17487,17491,12311,12319,12336,12350,12352,12356,12358,12370,12372,12389,12390,12404,12409,10697,10698,10699,10700,10701,10702,10703,10704,10705,10706,17001,17002,12889,12893,12895,12904,12906,12910,12922,35835,35840,35843,22251,22263,22268,13133,13143,13146,13148,21373,21374,21375,14701,14702,14703,14704,14706,14709,18314,18315,18316,18323,18325,16759,16762,16763,16764,20703,15232,15240,15245,15283,15316,15367,12849,12851,12852,14579,14582,14586,14590,14598,14616,22835,22836,22837,22840,22842,22843,14451,14452,14458,14460,14474,14475,14477,14482,14486,14496,11685,11687,12437,12467,12483,14116,14139,16701,16704,16714,19098,19099,19101,19107,12855,12856,12859,15589,15591,15592,15603,15604,23536,23540,23541,23542,23543,23544,23545,23546,23549,23550,23565,23566,20257,13481,13484,13504,15886,15888,15889,15891,15892,15893,18237,18239,13977,13979,13981,12724,12736,12737,12765,20530,20531,20532,20533,21036,21037,21038,21040,17542,13603,13607,13616,16662,16663,16664,16665,16666,16668,22205,12663,12670,16151,16155,16156,16158,16159,16160,21841,21842,21843,17709,17710,17718,17723,17725,17728,17731,17732,17733,18718,18720,18726,18728,19900,19901,12338,12346,12349,12362,12377,12378,12385,21613,21614,21619,21622,21623,21624,13377,13378,13380,13381,13383,13387,13391,13401,17396,21997,21998,21999,22000,22001,22002,15107,15108,15109,15110,15113,15114,15119,15120,23164,23165,23166,23169,12059,12063,12064,12083,24598,24599,24600,24601,24603,24604,19390,19391,19392,19393,19394,19680,19682,19685,19687,17659,17663,17664,11343,11344,11345,11346,11347,11349,11351,22410,22415,22416,21031,21032,21033,13727,13728,13730,13731,13747,11756,11760,11771,11783,12391,12394,22360,22364,15828,15829,15830,15831,15832,15833,16520,16522,16525,16527,16528,16530,16536,16537,15953,15956,15957,15959,13535,13536,13538,13546,13549,17085,17086,17087,17092,19162,19163,19164,19166,19167,15598,15601,17129,17130,17132,17133,17134,17135,17143,17146,20736,20737,20738,20741,20742,20743,20745,20747,20748,20750,20752,20753,20755,23390,23391,23392,23393,23394,23395,23398,23399,23400,23401,13192,13195,13197,13201,13858,13860,13862,13863,13864,35931,35932,35933,35934,35935,35936,35937,35938,35939,21092,21093,21094,21096,21097,21098,19362,19364,19368,18337,18339,18340,18341,18342,10977,10986,10987,19282,19285,19286,19288,19298,21918,21919,21920,21925,21941,23041,23043,23044,23052,23054,23057,19025,21468,21469,21470,21471,21472,21473,19247,19248,19249,19250,19252,19259,19266,19268,22745,22746,22747,22748,22749,22750,22757,22759,22770,19264,13096,13105,13109,13110,13112,13114,13129,17442,17443,17444,17445,17446,17447,17448,18096,18098,18105,14757,14772,14777,14779,14788,35910,35911,35912,35913,35915,15103,15104,23103,23104,23105,23106,23108,11000,11001,11002,11004,11005,11007,11008,11009,11011,11012,17173,17174,17176,17182,11264,11267,11271,20188,20189,20190,20191,20192,23875,23876,23878,23885,23891,23892,23893,23894,23895,23897,23905,23351,12302,12304,12323,20604,20606,20607,20609,20614,20615,20616,18517,18518,18519,18520,18525,20822,20824,17468,17470,20127,20128,18655,18657,18658,18659,17471,17472,17473,17474,17475,17478,17480,24961,24962,24963,24964,12057,12058,12060,12061,12065,12072,15559,15560,15562,15563,15564,19764,19768,24131,22670,22672,22673,22529,22530,22531,22532,22533,22534,22535,22536,22537,22540,24047,24048,24049,24050,24051,18585,18587,22650,13203,13206,13209,13214,13239,13419,13422,13429,13440,13441,13447,15479,15480,15481,15482,15484,15485,15486,15487,15488,15497,14745,14749,14751,21901,21902,21903,21905,21907,21913,21916,16584,16586,16587,16596,20035,20036,20037,20038,20039,20040,20041,20216,20217,20218,20219,20221,20223,20225,20226,21408,21410,21411,21413,21414,18373,18374,18375,18376,18377,23329,23331,23332,23335,23336,15123,15124,15125,15126,15132,15134,15135,15137,20789,20790,20791,20792,20164,20165,20166,20167,20168,20878,20879,20880,20881,20882,10655,10656,10657,10661,16344,16348,16349,16350,16190,20352,20353,20354,20355,20356,20357,18626,18632,24165,24166,24167,24168,24169,24170,24171,24172,24174,24006,24019,24020,18968,18969,18970,18971,18972,18977,12861,12825,16193,16194,16197,16205,35474,35475,35476,21325,21326,21328,21329,21337,21348,21349,19953,19963,19966,22427,12606,12607,12608,12609,25078,25079,25081,25085,25086,15147,15148,14983,14984,14991,14995,11735,11736,11737,11738,11739,11740,11742,11743,11744,11747,11754,11764,11766,11767,19428,19429,19431,19432,19433,19434,19435,13324,13326,13328,13329,13336,12091,12093,12097,12104,12106,12222,12227,21791,21795,21797,21799,21800,14968,14969,14970,14971,14972,14973,14974,12257,12263,12265,12266,12269,12313,23817,23818,23819,23820,23821,23826,23829,23831,18975,22376,22378,22389,22392,12168,12170,12173,12178,19011,18900,21848,21849,21850,21851,13063,13064,13066,13067,13071,13073,13075,13078,13091,24273,24275,24276,24277,24278,24279,24280,24281,24101,24103,24104,24105,24106,24107,24108,19872,19873,19875,19876,19879,19881,24977,24978,24981,24982,24983,22164,22165,22166,22167,22168,22169,22170,20670,20671,20674,20676,20680,20683,20685,20686,20687,20689,14898,14901,14902,14906,14913,18666,15914,15916,15917,15918,15919,15921,16672,16673,16674,23274,23276,13386,13406,13409,13416,12827,12828,12829,12830,12832,12833,12838,12846,17536,17537,17539,17540,24441,24443,24444,24445,24446,24447,24448,24449,20571,20572,20573,20574,20580,20583,20584,20585,20586,23462,23463,23465,23466,23467,23473,23474,18702,18703,18704,18705,18706,18707,18715,13436,13437,13438,13439,13448,13449,13459,13462,13463,17833,17835,17837,14642,14643,14644,14649,14650,14652,14653,14654,35850,35851,35852,35853,13418,13421,13425,13431,10774,10775,10776,10777,10778,10782,10786,15743,15745,15746,15749,15758,21133,21134,21135,21136,21137,21142,21143,21144,21153,21156,21157,21158,21159,20306,20307,20310,18637,18639,24394,24395,24396,17034,17036,13472,13476,13492,15977,15978,15979,18541,19662,19665,19667,19670,19672,19673,19674,19675,19676,11372,11373,11375,11379,11393,11403,19447,19448,19451,19452,19455,19457,19473,19475,19593,19594,19595,13645,16859,16860,16862,16863,16864,16866,16867,16868,16874,16879,16880,16883,20007,20008,20009,20014,20015,24880,24881,24882,24883,24884,24885,24886,24887,24888,24889,24890,24891,24894,16110,16111,16112,16113,16117,18353,18354,13690,13697,13698,14137,14138,14146,14147,16161,16162,16163,13983,13986,13987,13990,13998,13999,14848,14849,14850,14854,14858,14862,14864,21257,21258,21259,21260,21261,12217,12218,12235,18183,18185,18188,18197,18756,18759,18761,18817];
        //  $dupeIds = [55537,55538,50459,50460,51227,51228,51229,51230,51231,51259,51260,51261,51262,51263,51264,50795,50796,50797,50798,50799,52805,52806,52807,52808,52809,52810,52811,50986,50987,50988,51889,51890,51568,51569,51110,51111,51112,51113,51114,51115,50820,50821,50822,54650,54651,54652,54653,54654,54655,54656,54657,54658,54659,54660,54661,49729,49730,49731,49732,49733,49734,49735,52755,52756,52757,52758,52759,51184,51185,51186,51187,51188,51189,51190,52186,52187,52188,52189,52190,52191,52192,52193,52194,50602,50603,50604,50605,50606,50607,50608,51252,51253,51254,49900,49901,49902,49903,49904,49905,49906,51349,51350,51351,51352,51353,51354,51355,51356,51357,51358,51359,51360,52086,52087,52088,52089,52090,53077,53078,53079,53080,51079,51080,51081,52625,52626,52627,52628,52629,52630,52631,52697,52698,52699,52700,52701,52702,52703,51631,51632,51633,51634,50045,50046,50047,50048,50049,50050,50051,52471,52472,52473,52474,52475,51161,51162,51164,51163,51165,51166,52844,52845,52846,52847,52848,51580,51581,51582,51583,52265,52266,52267,52268,52269,52270,52271,52272,51597,51598,51599,51600,51601,51761,51762,51763,51764,51765,49630,49631,49632,49633,49634,52183,52184,50578,50579,50580,50581,50582,50113,50114,50115,53524,53525,53526,51652,51808,51809,51810,51811,51812,51813,51814,51815,52405,52406,52407,52408,52410,52411,52412,52409,51329,51331,51330,49976,49977,49978,49979,49980,49981,49982,49983,49984,49985,49986,50027,54690,54691,54692,54693,54694,54695,53602,53603,53604,53605,53606,50996,50997,50998,50999,51000,51001,49723,49724,49725,49726,51995,51996,51997,51998,55406,55407,55408,55409,50710,50711,50712,50713,50714,50715,50716,50717,50718,50719,50720,50721,50722,50652,50653,50654,50655,50656,50657,50658,50659,50660,50661,50392,50393,51365,51366,51367,51368,51369,51370,51371,52736,52737,52738,50357,50358,50359,50809,50810,50811,50812,49695,49696,49697,54524,54525,54526,54527,54528,54529,51503,51504,51505,51506,51507,51143,51144,51145,51146,52770,50200,50201,50202,50203,50204,50205,52138,52139,52140,51540,51541,51542,51543,51544,51545,55255,55256,55257,55258,55259,55260,52207,52208,52209,52210,52211,52212,52213,52214,52215,52216,52496,52497,50731,50732,50733,50692,50693,52560,52561,52562,53445,53446,53447,53448,50182,50183,50184,52869,52870,52871,52872,52873,53486,53487,53488,53489,53490,53491,53492,53493,53494,53495,50442,50443,52464,52657,52658,52659,51920,51921,51922,51923,51924,51925,53027,53028,50060,50061,50062,50319,50320,50321,50322,52819,52820,52821,52822,50225,50226,50227,50228,55483,50635,50636,50637,51491,51492,51493,51494,51495,51496,50515,51773,51774,49913,49914,49915,49916,49917,49918,50284,50285,50286,51864,51865,51866,51867,51868,51869,51870,51871,51872,50647,50648,50649,50650,50471,50472,50611,50612,50613,50614,50615,50616,50617,52833,52834,52835,52836,52837,52838,52794,52795,52796,52797,52798,52799,52800,52801,53118,51712,51713,51714,51715,51716,51717,50696,50697,50698,50699,50700,50701,50702,50703,53456,53457,53458,53459,52330,52331,52332,52333,52784,52785,52786,52787,52788,52789,49786,49787,49788,49789,49790,51623,51624,51625,51626,52223,52224,52225,52593,52594,52595,52596,52597,52598,52599,50218,50219,50220,52571,52572,52573,51976,51977,51978,51979,51980,49821,49822,49823,49824,50952,50953,54764,54765,51681,51682,51683,51684,51685,51686,50620,50621,50622,50623,50624,50625,50626,50627,50096,50097,50098,50099,50908,50909,50910,50911,50912,50068,50069,50070,50071,51375,51376,51377,51378,51379,49642,49643,52029,52030,52031,52032,52033,52034,52035,52036,51309,51310,51311,51312,51313,51314,51315,51316,51317,51318,51319,51320,51321,52891,52892,52893,52894,52895,52896,52897,52898,52899,52900,52108,52109,52110,52111,53000,53001,53002,53003,53004,51432,51433,51434,51435,51436,51437,51438,51439,51440,51068,51069,51070,51071,51072,51073,51692,51693,51694,52117,52118,52119,52120,52121,50277,50278,50276,55555,55556,55557,55558,55559,52482,52483,52484,52485,52486,53176,53177,53178,49806,49807,49808,52286,54632,54633,54634,54635,54636,54637,50122,50123,50124,50125,50126,50127,50128,50129,50133,50134,50135,50136,50137,50138,50139,50140,50141,51862,52521,52522,52523,52524,52525,52526,52527,49844,49845,49846,49847,49848,49849,49850,52620,52621,52622,50014,50015,50016,50017,50018,52536,52537,52538,52539,52540,50780,50781,50557,50558,50559,50560,50561,51731,51732,51733,51734,51735,51736,51737,51738,51739,51740,51900,51901,51902,51903,50345,50346,50347,50881,50882,50883,50884,50885,51959,51960,51961,51962,51963,51964,51965,51966,51967,51968,51969,51388,52392,52393,52394,50898,50899,50900,50901,50902,50903,50904,55567,55568,55569,55570,55571,53041,53042,49757,49758,54704,54705,53086,53087,53088,53089,51448,51449,51450,51451,51452,51453,51454,52076,52077,52078,52079,52370,52371,52372,52373,52374,52375,52607,52608,52609,52610,52611,50855,50856,52172,52452,52453,52454,50330,50331,50332,50333,50334,50335,50336,50337,50338,50339,54667,54668,54669,54670,54671,54613,54614,51823,51482,51483,51484,51485,51486,51556,51557,51558,51559,51560,51561,49666,49667,49668,49669,49670,49671,49672,49673,49674,49675,49651,49652,49653,50189,50190,50191,50192,50193,50194,50195,52321,52322,52323,52324,52040,52041,52042,52043,52044,52045,52046,50669,50670,50671,50672,50673,50674,50675,50676,49859,49860,49861,49862,49863,52744,52745,52746,52747,52748,49764,49765,49766,49767,49768,51928,51929,51930,51931,51932,51933,51934,51935,53048,53049,53050,53051,51945,51946,51947,51948,51949,51026,51027,51028,51029,51030,51533,51534,51535,51536,50839,50840,50841,50842,52234,52254,52255,52256,52257,52258,52259,52986,52987,54338,54339,54340,54341,54342,54343,54344,54345,54346,53574,53575,53576,54724,54725,54726,54727,54728,54729,50037,51990,51609,51610,51611,51612,49832,49833,49834,51638,51639,51640,51641,51642,51643,51644,52097,52098,52099,50411,52879,52880,52881,52882,54676,54677,54678,54679,54680,50383,50384,51243,51244,51245,51246,50539,50540,50541,50542,50543,50544,50545,50546,50547,50548,50549,50550,50551,50552,50563,50564,50565,50566,50567,50568,50569,51092,51093,51094,51095,51096,53058,53059,53060,53061,53062,53063,53064,50263,50264,50265,50266,50267,52511,52512,52513,52514,52515,52516,52517,50891,50892,50893,50894,50895,50896,52542,52543,52544,52545,52546,52547,52548,52549,52641,51878,51879,51880,51881,52420,52421,52422,52423,52507,52206,52005,52006,52007,52008,49773,49774,49775,49776,49777,49778,49779,49780,49781,52242,52243,52244,52245,52246,52247,52248,52249,50739,50740,50741,50742,50743,50744,50745,50289,50290,50291,50292,50293,50294,50251,50252,50253,50254,50255,50587,50588,50589,50590,50591,50592,50593,53822,53823,53824,53825,53826,53827,53828,53829,53830,53831,52718,52719,52720,52721,52722,49929,51471,51472,51473,51474,51475,51476,50766,50767,50768,52313,52314,52665,52666,52667,52668,49955,49956,49957,49958,49959,49960,49961,49962,49934,49935,49936,49937,52437,52438,52439,52440,52441,52442,52443,52444,51828,51829,51830,51831,51832,51833,51834,51835,51836,49656,49657,49658,49659,49660,49661,49662,50500,50501,50502,50503,50504,50505,50506,50920,50921,50922,50923,50924,50925,50926,50927,50928,55194,55195,55196,51149,51150,51151,51152,51153,51154,51155,51156,52063,52064,52065,52066,51706,51707,51708,51709,55599,55600,55601,55602,55603,55604,55605,51722,51723,51724,51725,51726,51129,51130,51131,51132,51133,51134,51135,51136,49996,49997,49998,49999,50000,51281,51282,51283,50489,50490,53014,53015,53016,49841,49842,51211,51212,51213,50480,50481,50482,52930,52050,52051,52052,52053,52054,52055,52056,52057,52058,52580,52581,52582,52583,52584,52585,52912,52913,52914,52915,52916,52917,52918,52919,50863,50864,50865,52693,54391,54392,54393,54394,54395,54396,54397,54398,54399,54400,54401,54402,50238,50239,50240,50241,50242,50162,50163,50164,50165,50166,50167,50168,50169,50170,50171,50172,50173,50174,51268,51269,51270,51271,51272,54593,54594,50006,50007,50008,50400,50401,50402,50403,50449,50450,50451,52645,52646,52647,52648,52649,52650,51908,51909,51910,51911,51912,51913,51914,52291,52292,52293,52294,52295,49623,49624,49625,54805,54806,54807,54808,53514,53515,53516,53517];

            $alters = Alters::find()->all();
            $alterInt = [];
            foreach ($alters as $alter) {
                if(stristr(",", $alter->interviewId))
                $int_ids = explode(",",$alter->interviewId);
                else
                $int_ids = [$alter->interviewId];
                foreach($int_ids as $int_id){
                    if(!isset($alterInt[$alter->id]))
                        $alterInt[$alter->id] = [];
                    if($int_id != '' && !in_array($int_id, $alterInt[$alter->id])){
                        $alterInt[$alter->id][] = $int_id;
                    }
                }
            }
            foreach ($alters as $alter) {
                if(stristr(",", $alter->interviewId))
                $int_ids = explode(",",$alter->interviewId);
                else
                $int_ids = [$alter->interviewId];
                foreach($int_ids as $int_id){
                    if(in_array($alter->id, $origIds)){
                        $dupeKey = array_search($alter->id, $origIds);
                $dupeId = $dupeIds[$dupeKey];
                    if(!isset($alterInt[$dupeId]))
                        $alterInt[$dupeId] = [];
                    if($int_id != '' && !in_array($int_id, $alterInt[$dupeId])){
                        $alterInt[$dupeId][] = $int_id;
                    }
                    }
                }
            }
            $newAlterInt = [];
            foreach($alterInt as $index=>$aInt){
                $newAlterInt[$index] = implode(",", $aInt);
            }
            $alterInt = $newAlterInt;


            echo "DELETE from alters where id in (" . implode(",",$dupeIds) . ")";
            echo count($dupeIds)."<br>";
            echo count($origIds)."<br>";

            foreach($dupeIds as $index=>$dupeId){
                if(isset($answerbyId1[$dupeId] )){
                    $origId = $origIds[$index];
                   // echo "SELECT * FROM answer WHERE alterId1 = $dupeId and //id in (" .
                   // implode(",",$answerbyId1[$dupeId]) .
                   // ")<br>";
                    echo "UPDATE answer set alterId1 = $origId WHERE alterId1 = $dupeId and id in (" .
                     implode(",",$answerbyId1[$dupeId]) .
                     ");<br>";
                     echo "UPDATE alters set interviewId = '". $alterInt[$dupeId] ."' WHERE id = $origId;<br>";

                }
                if(isset($answerbyId2[$dupeId] )){
                    $origId = $origIds[$index];
                    echo "UPDATE answer set alterId2 = $origId WHERE alterId2 = $dupeId and id in (" .
                    implode(",",$answerbyId2[$dupeId]) .
                    ");<br>";
                    echo "UPDATE alters set interviewId = '". $alterInt[$dupeId] ."' WHERE id = $origId;<br>";

                }
            }
            die();

            $alters = Alters::find()->all();
            $count = 0;
            foreach ($alters as $alter) {
                $i_ids = explode(",", $alter->interviewId);
                foreach ($i_ids as $i_id) {
                    if($i_id == "")
                    continue;
                    if (!in_array($alter->id, $alterAnswers)) {
                        if (!isset($egoIds[$i_id])) {
                            echo "(". $alter->id  .") interview not found" . ":" . $i_id . ":" . $alter->name . "<br>";
                            $count++;
                        } elseif (in_array($egoIds[$i_id], $dupeIds)) {
                            if(in_array($alter->name, $checkNames)){
                                echo "(". $alter->id  .") <b>no answers found:" .  $egoIds[$i_id]. ":" . $alter->name . "</b><br>";
                                $count++;
                            }else{
                                echo "(". $alter->id  .") no answers found:" . ":" .  $egoIds[$i_id]. ":" . $alter->name . "<br>";
                                $count++;
                            }
                      
                        }
                    }
                }
            }
            echo "total:" . $count;
            die();


            return $this->render('removedupes', [
                "alterAnswers" => $alterAnswers,
                "egoIds" => $egoIds,
                "dupeIds" => $dupeIds,
                "interviews" => $interviews,
                "allInterviewIds" => $allInterviewIds
            ]);
        }
    }
}
