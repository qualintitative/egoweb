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
use yii\helpers\Url;
use app\models\Study;
use app\models\Question;
use app\models\QuestionOption;
use app\models\Expression;
use yii\helpers\ArrayHelper;
use app\models\Interview;
use app\models\Answer;
use app\models\Alters;
use app\models\MatchedAlters;
/**
 * Site controller
 */
class DyadController extends Controller
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


    public function actionIndex()
    {
        $this->view->title = "EgoWeb 2.0";
        $studies = Yii::$app->user->identity->studies;
        return $this->render('index', array(
            'studies'=>$studies,
        ));
    }


    public function actionMatching()
      {
          if(count($_POST['export']) < 2)
              die("You must select at least 2 interviews");

          foreach($_POST['export'] as $key=>$value){
              $interviewIds[] = $key;
          }
          arsort($interviewIds);
          $interview1 = Interview::findOne($interviewIds[0]);
          $interview2 = Interview::findOne($interviewIds[1]);

          if($interview1->studyId != $interview2->studyId){
            $questions1 = Question::findAll(array("studyId"=>$interview1->studyId));
            foreach($questions1 as $question){
              $questionIds[$question->title] = $question->id;
            }
            $questions2 = Question::findAll(array("studyId"=>$interview2->studyId));
            foreach($questions2 as $question){
              if(isset( $questionIds[$question->title] )){
                $questionIds1[$question->id] = $questionIds[$question->title];
              }
            }
          }
          $study = Study::findOne($interview1->studyId);
          $result = Alters::find()
          ->where(new \yii\db\Expression("FIND_IN_SET(" . $interview1->id .", interviewId)"))
          ->orderBy(['ordering'=>'ASC'])
          ->all();
  		foreach($result as $alter){
      		$alters1[$alter->id] = $alter->name;
  		}
 
        $result = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $interview2->id .", interviewId)"))
        ->orderBy(['ordering'=>'ASC'])
        ->all();

  		foreach($result as $alter){
      		$alters2[$alter->id] = $alter->name;
  		}
        $result = Answer::findAll(["questionType"=>"ALTER", "interviewId"=>[$interview1->id,$interview2->id]]);
  		foreach($result as $answer){
      		if($answer->answerType == "MULTIPLE_SELECTION"){
                      $optionIds = explode(",", $answer->value);
                      //$answer->value = "";
                      $answerArray = array();
                      $otherSpecifies = array();
                      $response = $answer->otherSpecifyText;
                      foreach(preg_split('/;;/', $response) as $otherSpecify){
                          if(strstr($otherSpecify, ':')){
                              list($optionId, $val) = preg_split('/:/', $otherSpecify);
                              $otherSpecifies[$optionId] = $val;
                          }
                      }
                      $optionIds = explode(",", $answer->value);
                      foreach  ($optionIds as $optionId)
                      {

                          $option = QuestionOption::findOne($optionId);
                          if(!$option)
                              continue;
                          if (isset($otherSpecifies[$optionId])){
                              //if(count($optionIds) == 1 && preg_match("/OTHER \(*SPECIFY\)*/i", $other_options[$optionId]->name))
                                  $answerArray[] = $otherSpecifies[$optionId];
                              //else
                              //    $answerArray[] = $otherSpecifies[$optionId];
                          }else{
                              $answerArray[] = $option->name;
                          }
                      }
                    
                      $answer->value = implode("; ", $answerArray);

      		}
          if($interview1->id == $answer->interviewId || $interview1->studyId == $interview2->studyId){
              $answers[$answer->questionId][$answer->alterId1] = $answer->value;
            }elseif($interview2->id == $answer->interviewId){
                if(isset($questionIds1[$answer->questionId]))
                    $answers[$questionIds1[$answer->questionId]][$answer->alterId1] = $answer->value;
            }
  		}

          $result = Question::findAll(array("subjectType"=>"ALTER", "studyId"=>$interview1->studyId));
          foreach($result as $question){
            if(isset($questionIds1[$question->id]) || $interview1->studyId == $interview1->studyId){
              $questions[$question->id] = $question->title;
              $prompts[$question->id] = $question->prompt;
            }
          }
          
        foreach($alters1 as $aid1=>$a1){
            $match1 = MatchedAlters::findOne(array("alterId1"=>$aid1));
            $match2 = MatchedAlters::findOne(array("alterId2"=>$aid1));
            if(!$match1 && $match2){
                $interviewNew = $interview2;
                $interview2 = $interview1;
                $interview1 = $interviewNew;
                $altersNew = $alters2;
                $alters2 = $alters1;
                $alters1 = $altersNew;
                break;
            }
        }
        
  		return $this->render('matching', array(
  			'interview1'=>$interview1,
  			'alters1'=>$alters1,
  			'interview2'=>$interview2,
  			'alters2'=>$alters2,
  			'answers'=>$answers,
  			'questions'=>$questions,
  			'prompts'=>$prompts,
  			'study'=>$study
  		));
    }

    public function actionAjaxinterviews($id)
    {
        $study = Study::findOne($id);
        $interviews = Interview::findAll(array('studyId'=>$id));
        $u = User::findAll([
            'status' => User::STATUS_ACTIVE,
        ]);
        $users = [];
        foreach ($u as $user) {
            $users[$user->id] = $user;
        }
        return $this->renderAjax('_interviews',
            array(
                'study'=>$study,
                'interviews'=>$interviews,
                'users'=>$users,
            )
        );
    }

    public function actionUnmatch()
    {
        if(isset($_POST)){
            $match = MatchedAlters::findOne(array("alterId1"=>$_POST['alterId1'], "alterId2"=>$_POST['alterId2']));
            if($match)
                $match->delete();
            else
                die("not found");
        }
    }

    public function actionSavematch()
    {
        if(isset($_POST)){
            if(isset($_POST['id']) && $_POST['id'] != 0){
            $match = MatchedAlters::findOne($_POST['id']);
            }else{
                $match = new MatchedAlters;
            }
            $match->attributes = $_POST;
            $match->userId = Yii::$app->user->identity->id;
            if($match->matchedName == ""){
                $match->matchedName = "marked";
            }
            $mark = "Unmatch";
            if($_POST['alterId1'] == 0)
                $mark = "Remove Mark";
            if($match->save()){
                $data = array(
                    "studyId"=>$_POST['studyId'],
                    "alterId1"=>$_POST['alterId1'],
                    "alterId2"=>$_POST['alterId2'],
                    "matchId"=>$match->id,
                    "mark"=>$mark,
                );
                echo json_encode($data);
            }else{
                print_r($match->errors);
            }

        }
    }

}
