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
        $studies = Yii::$app->user->identity->studies;
        $this->render('index', array(
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
          $interview1 = Interview::model()->findByPK($interviewIds[0]);
          $interview2 = Interview::model()->findByPK($interviewIds[1]);

          if($interview1->studyId != $interview2->studyId){
            $questions1 = Question::model()->findAllByAttributes(array("studyId"=>$interview1->studyId));
            foreach($questions1 as $question){
              $questionIds[$question->title] = $question->id;
            }
            $questions2 = Question::model()->findAllByAttributes(array("studyId"=>$interview2->studyId));
            foreach($questions2 as $question){
              if(isset( $questionIds[$question->title] )){
                $questionIds1[$question->id] = $questionIds[$question->title];
              }
            }
          }
          $study = Study::model()->findByPk($interview1->studyId);
  		$criteria = array(
  			'condition'=>"FIND_IN_SET(" . $interview1->id . ", interviewId)",
  		);
  		$result = Alters::model()->findAll($criteria);
  		foreach($result as $alter){
      		$alters1[$alter->id] = $alter->name;
  		}
  		$criteria = array(
  			'condition'=>"FIND_IN_SET(" . $interview2->id . ", interviewId)",
  		);
  		$result = Alters::model()->findAll($criteria);


  		foreach($result as $alter){
      		$alters2[$alter->id] = $alter->name;
  		}
          $criteria = array(
  			'condition'=>"questionType = 'ALTER' AND interviewId in (" . $interview1->id . ", " . $interview2->id  . ")",
  		);
          $result = Answer::model()->findAll($criteria);
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
                          if(!$optionId)
                              continue;
                          $option = QuestionOption::model()->findbyPk($optionId);
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
              $answers[$questionIds1[$answer->questionId]][$answer->alterId1] = $answer->value;
            }
  		}

          $result = Question::model()->findAllByAttributes(array("subjectType"=>"ALTER", "studyId"=>$interview1->studyId));
          foreach($result as $question){
            if(isset($questionIds1[$question->id]) || $interview1->studyId == $interview1->studyId){
              $questions[$question->id] = $question->title;
              $prompts[$question->id] = $question->prompt;
            }
          }
          
        foreach($alters1 as $aid1=>$a1){
            $match1 = MatchedAlters::model()->findByAttributes(array("alterId1"=>$aid1));
            $match2 = MatchedAlters::model()->findByAttributes(array("alterId2"=>$aid1));
            if(!$match1 && $match2){
                //flip the arrays
                $interviewNew = $interview2;
                $interview2 = $interview1;
                $interview1 = $interviewNew;
                $altersNew = $alters2;
                $alters2 = $alters1;
                $alters1 = $altersNew;
                break;
            }
        }
        
  		$this->render('matching', array(
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

        public function actionStudy($id)
        {
            $criteria = new CDbCriteria;
            $criteria = array(
                'condition'=>"studyId = $id and useAlterListField in ('name','email','id')",
            );
            $egoIdQ = Question::model()->find($criteria);
    		$restrictions = "";
    		if($egoIdQ){
                $criteria = new CDbCriteria;
                $criteria = array(
                    'condition'=>"interviewerId = " . Yii::app()->user->id,
                );
    			$participantList =  AlterList::model()->findAll($criteria);
                $participants = array();
                foreach($participantList as $p){
        			if($egoIdQ->useAlterListField == "email")
                        $participants[] = $p->email;
                    elseif($egoIdQ->useAlterListField == "name")
                        $participants[] = $p->name;
    			}
    			if($participants){
            		$criteria = array(
            			'condition'=>"questionId = " .$egoIdQ->id,
            		);
                    $answers = Answer::model()->findAll($criteria);
                    foreach($answers as $answer){
                        if(in_array($answer->value, $participants))
                            $interviewIds[] = $answer->interviewId;
                    }
    				if($interviewIds)
    					$restrictions = ' and id in (' . implode(",", $interviewIds) . ')';
    				else
    					$restrictions = ' and id = -1';
    			}
    		}
            if(Yii::app()->user->isSuperAdmin)
                $restrictions = "";
    		$criteria=array(
    			'condition'=>'studyId = '.$id . $restrictions,
    			'order'=>'id DESC',
    		);

            $interviews = Interview::model()->findAll($criteria);
            $study = Study::model()->findByPk((int)$id);
            $questionIds = array();
            $questions = Question::model()->findAllByAttributes(array("subjectType"=>"ALTER_PAIR", "studyId"=>$id));
    		foreach($questions as $question)
    			$questionIds[] = $question->id;
            $expressions = array();
            if(count($questionIds) > 0){
                $questionIds = implode(",", $questionIds);
                $criteria = array(
                    'condition'=>"studyId = " . $study->id ." AND questionId in ($questionIds)",
                );
                $expressions = CHtml::listData(
                    Expression::model()->findAll($criteria),
                    'id',
                    function($post) {return CHtml::encode(substr($post->name,0,40));}
                );
            }
            $this->render('study', array(
                'study'=>$study,
                'interviews'=>$interviews,
                'expressions'=>$expressions,
            ));
        }

        public function actionAjaxInterviews($id)
      	{
      		$study = Study::model()->findByPk($id);
      		$interviews = Interview::model()->findAllByAttributes(array('studyId'=>$id));
      		$this->renderPartial('_interviews',
      			array(
      				'study'=>$study,
      				'interviews'=>$interviews,
      			), false, false
      		);
      	}

        public function actionUnmatch()
          {
              if(isset($_POST)){
                  $match = MatchedAlters::model()->findByAttributes(array("alterId1"=>$_POST['alterId1'], "alterId2"=>$_POST['alterId2']));
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
                    $match = MatchedAlters::model()->findByPk($_POST['id']);
                  }else{
                     $match = new MatchedAlters;
                  }
                  $match->attributes = $_POST;
                  $match->userId = Yii::app()->user->id;
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
