<?php

/**
 * This is the model class for table "interview".
 *
 * The followings are the available columns in table 'interview':
 * @property integer $id
 * @property integer $random_key
 * @property integer $active
 * @property integer $studyId
 * @property integer $completed
 */
class Interview extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Interview the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'interview';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('studyId', 'required'),
			array('id, active, studyId, completed', 'length', 'max'=>255),
			array('id, active, studyId', 'numerical', 'integerOnly'=>true),
			array('completed', 'default', 'value'=>0),
			array('start_date', 'default',
				'value'=>time(),
				'setOnEmpty'=>true, 'on'=>'insert'
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, active, studyId, completed', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'random_key' => 'Random Key',
			'active' => 'Active',
			'studyId' => 'Study',
			'completed' => 'Completed',
		);
	}

	public static function getInterviewFromEmail($studyId, $email)
	{
		#OK FOR SQL INJECTION
		$interviewId = q("SELECT interviewId FROM answer WHERE value='$email' AND questionType = 'EGO_ID' AND studyId = $studyId")->queryScalar();
		if ($interviewId)
			return Interview::model()->findByPk($interviewId);
		else
			return false;
	}

	/**
	 * retrieves interview (or create new one) from MMIC prime key
	 * @param $studyId
	 * @param $primekey
	 * @param $prefill (Ego ID Prefill)
	 * @param $question (Ego Questions Prefill)
	 * @return array|bool|CActiveRecord|Interview|mixed|null
	 */
	public static function getInterviewFromPrimekey( $studyId, $primekey, $prefill, $questions = array())
	{
		$answers = Answer::model()->findAllByAttributes( array( 'questionType' => 'EGO_ID',
				'studyId' => $studyId ) );

		foreach ( $answers as $answer )
		{
			if ( $answer->value == $primekey )
			{
				return Interview::model()->findByPk( $answer->interviewId );
			}
		}

		$criteria=new CDbCriteria;
		$criteria->condition = ("studyId = $studyId and subjectType = 'EGO_ID' AND answerType != 'RANDOM_NUMBER'");
		$egoQs = Question::model()->findAll($criteria);
		$study = Study::model()->findByPk($studyId);

		if (count($egoQs) == 0){
			return false;
		}

		$interview = new Interview;
		$interview->studyId = $studyId;
		$interview->completed = 0;
		$interview->save();

		$prefill['prime_key'] = $primekey;

		foreach ($egoQs as $egoQ)
		{
			$egoIdQ = new Answer;
			$egoIdQ->interviewId = $interview->id;
			$egoIdQ->studyId = $studyId;
			$egoIdQ->questionType = "EGO_ID";
			$egoIdQ->answerType = $egoQ->answerType;
			$egoIdQ->questionId = $egoQ->id;
			$egoIdQ->skipReason = "NONE";
			if (isset($prefill[$egoQ->title]))
			{
				$egoIdQ->value = $prefill[$egoQ->title];
			}else
			{
				$egoIdQ->skipReason = "DONT_KNOW";
				$egoIdQ->value = $study->valueDontKnow;
			}
			$egoIdQ->save();
		}

		$randoms = Question::model()->findAllByAttributes(array("answerType"=>"RANDOM_NUMBER", "studyId"=>$studyId));
		foreach($randoms as $q){
			$a = $q->id;
			$answer = new Answer;
			$answer->interviewId = $interview->id;
			$answer->studyId = $studyId;
			$answer->questionType = "EGO_ID";
			$answer->answerType = "RANDOM_NUMBER";
			$answer->questionId = $q->id;
			$answer->skipReason = "NONE";
			$answer->value = mt_rand ($q->minLiteral , $q->maxLiteral);
			$answer->save();
		}

		if(count($questions) > 0)
			$interview->fillQs($questions, $interview->id, $studyId);

		return $interview;
	}

	public static function fillQs($qs, $interviewId, $studyId)
	{
		foreach ($qs as $title=>$value)
		{
			$question = Question::model()->findByAttributes(array("title"=>$title, "studyId"=>$studyId));
			$answer = Answer::model()->findByAttributes(array("interviewId"=>$interviewId, "questionId"=>$question->id));
			if(!$answer)
				continue;
			$answer = new Answer;
			$answer->interviewId = $interviewId;
			$answer->studyId = $studyId;
			$answer->questionType = $question->subjectType;
			$answer->answerType = $question->answerType;
			$answer->questionId = $question->id;
			$answer->skipReason = "NONE";
			if ($value)
			{
				$answer->value = $value;
			}else
			{
				$answer->skipReason = "DONT_KNOW";
				$study = Study::model()->findByPk($studyId);
				$answer->value = $study->valueDontKnow;
			}
			$answer->save();
		}
	}

	public static function countAlters($id)
	{
		$criteria=array(
			'condition'=>"FIND_IN_SET(" . $id .", interviewId)",
		);
		$models = Alters::model()->findAll($criteria);
		return count($models);
	}

	public static function getRespondant($id)
	{
		#OK FOR SQL INJECTION
		$studyId = q("SELECT studyId FROM answer WHERE interviewId = $id")->queryScalar();

		if (!$studyId)
			return 'error';
		#OK FOR SQL INJECTION
		$firstId = q("SELECT id from question WHERE studyId = $studyId and subjectType = 'EGO_ID' ORDER by ordering")->queryScalar();

		if (!$firstId)
			return '';
		$egoIdAnswer = Answer::model()->find(array(
				'condition'=>"interviewId=:interviewId AND questionId = $firstId AND value != ''",
				'params'=>array(':interviewId'=>$id),
			));

		if (isset($egoIdAnswer->value) && stristr($egoIdAnswer->value, '@'))
			#OK FOR SQL INJECTION
			return q("SELECT name FROM alterList WHERE email = '" .$egoIdAnswer->value . "'")->queryScalar();
		else if (isset($egoIdAnswer->value))
				return $egoIdAnswer->value;
			else
				return '';
	}

	public static function getEgoId($id)
	{
		#OK FOR SQL INJECTION
		$params = new stdClass();
		$params->name = ':id';
		$params->value = $id;
		$params->dataType = PDO::PARAM_INT;

		$interview = q("SELECT * FROM interview where id = :id", array($params))->queryRow();
		$ego_id_questions = q("SELECT * FROM question WHERE subjectType = 'EGO_ID' AND studyId = " . $interview['studyId'] . " AND answerType NOT IN ('STORED_VALUE', 'RANDOM_NUMBER') ORDER BY ordering")->queryAll();
		$egoId = "";
		foreach ($ego_id_questions as $question)
		{
			$headers[] = $question['title'];
		}
		$ego_ids = array();
		foreach ($ego_id_questions as $question)
		{
			if ($question['answerType'] == "MULTIPLE_SELECTION")
			{
				#OK FOR SQL INJECTION
				$optionId = decrypt(q("SELECT value FROM answer WHERE interviewId = " . $interview['id']  . " AND questionId = " . $question['id'])->queryScalar());

				if ($optionId && is_numeric($optionId))
				{
					//$optionId = decrypt($optionId);
					#OK FOR SQL INJECTION
					$ego_ids[] = q("SELECT name FROM questionOption WHERE id = " . $optionId)->queryScalar();
				}
			}else
			{
				$id_response = Answer::model()->findByAttributes(array("interviewId" => $interview['id'], "questionId"=>$question['id']));
				if ($id_response)
					$ego_ids[] = $id_response->value;
			}
		}

		if (isset($ego_ids))
			$egoId = implode("_", $ego_ids);

		return $egoId;
	}

	public static function multiInterviewIds($interviewId = null, $study = null)
	{
		#OK FOR SQL INJECTION
		$interview = Interview::model()->findByPk((int)$interviewId);
		if ($interview && $study && $study->multiSessionEgoId)
		{
			#OK FOR SQL INJECTION
			$egoValue = decrypt(q("SELECT value FROM answer WHERE interviewId = " . $interview->id . " AND questionId = " . $study->multiSessionEgoId)->queryScalar());
			#OK FOR SQL INJECTION
			$multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")")->queryColumn();
			if ($multiIds)
			{
				$answers = Answer::model()->findAllByAttributes(array('questionId'=>$multiIds));
				$interviewIds = array();
				foreach ($answers as $answer)
				{
					if ($answer->value == $egoValue)
					{
						$interviewIds[] = $answer->interviewId;
					}
				}
				#OK FOR SQL INJECTION
				$interviewIds = array_unique($interviewIds);
				return $interviewIds;
			}
		}
		return $interviewId;
	}

	// CORE FUNCTION
	public static function interpretTags($string, $interviewId = null, $alterId1 = null, $alterId2 = null)
	{

		if (!$interviewId)
			return $string;

		#OK FOR SQL INJECTION
		$params = new stdClass();
		$params->name = ':interviewId';
		$params->value = $interviewId;
		$params->dataType = PDO::PARAM_INT;

		$studyId = q("SELECT studyId FROM interview WHERE id = :interviewId", array($params))->queryScalar();
		#OK FOR SQL INJECTION
		$study = Study::model()->findByPk((int)$studyId);

		$interviewId = Interview::multiInterviewIds($interviewId, $study);

		if (is_array($interviewId))
			$interviewId = implode(",", $interviewId);

		// parse out and replace variables
		preg_match_all('#<VAR (.+?) />#ims', $string, $vars);
		foreach ($vars[1] as $var)
		{
			if (preg_match('/:/', $var))
			{
				list($sS, $sQ) = explode(":", $var);

				#OK FOR SQL INJECTION
				$sId = q("SELECT id FROM study WHERE name = '".$sS ."'")->queryScalar();
				$question = Question::model()->findByAttributes(array('title'=>$sQ, 'studyId'=>$sId));
			}else
			{
				$question = Question::model()->findByAttributes(array('title'=>$var, 'studyId'=>$studyId));
			}

			if ($question)
			{
				if ($interviewId != null)
				{
					$end = " AND interviewId in (". $interviewId .")";
				}else
				{
					$end = "";
				}
				$criteria=new CDbCriteria;
				$criteria=array(
					'condition'=>"questionId = " . $question->id . $end,
					'order'=>'id DESC',
				);
				$lastAnswer = Answer::model()->find($criteria);
			}
			if (isset($lastAnswer))
			{
				if ($question->answerType == "MULTIPLE_SELECTION")
				{
					$optionIds = explode(",", $lastAnswer->value);
					$lastAnswer->value = "";
					$answerArray = array();
					foreach  ($optionIds as $optionId)
					{
						$option = QuestionOption::model()->findbyPk($optionId);
						if ($option)
						{
							$criteria=new CDbCriteria;
							$criteria=array(
								'condition'=>"optionId = " . $option->id . " AND interviewId in ($interviewId)",
							);
							$otherSpecify = OtherSpecify::model()->find($criteria);
							if ($otherSpecify)
								$answerArray[] = $option->name . " (\"" . $otherSpecify->value . "\")";
							else
								$answerArray[] = $option->name;
						}
					}
					$lastAnswer->value = implode("; ", $answerArray);
				}
				$string =  preg_replace('#<VAR '.$var.' />#', $lastAnswer->value, $string);
			}else
			{
				$string =  preg_replace('#<VAR '.$var.' />#', '', $string);
			}
		}

		// performs calculations on questions
		preg_match_all('#<CALC (.+?) />#ims', $string, $calcs);
		foreach ($calcs[1] as $calc)
		{
			preg_match('/(\w+)/', $calc, $vars);
			foreach ($vars as $var)
			{
				if (preg_match('/:/', $var))
				{
					list($sS, $sQ) = explode(":", $var);
					#OK FOR SQL INJECTION
					$sId = q("SELECT id FROM study WHERE name = '".$sS ."'")->queryScalar();
					$question = Question::model()->findByAttributes(array('title'=>$sQ, 'studyId'=>$sId));
				} else
				{
					$question = Question::model()->findByAttributes(array('title'=>$var, 'studyId'=>$studyId));
				}
				if ($question)
				{
					if ($interviewId != null)
					{
						$end = " AND interviewId in (". $interviewId . ")";
					}else
					{
						$end = "";
					}
					$criteria=new CDbCriteria;
					$criteria=array(
						'condition'=>"questionId = " . $question->id . $end,
						'order'=>'id DESC',
					);
					$lastAnswer = Answer::model()->find($criteria);
				}
				if (isset($lastAnswer))
					$logic =  preg_replace('#'.$var.'#', $lastAnswer->value, $calc);
				else
					$logic =  preg_replace('#'.$var.'#', '', $calc);
			}
			$logic = 'return ' . $logic . ';';

			$calculation = eval($logic);
			$string =  str_replace("<CALC ".$calc." />", $calculation, $string);
		}

		// counts numbers of times question is answered with string
		preg_match_all('#<COUNT (.+?) />#ims', $string, $counts);
		foreach ($counts[1] as $count)
		{
			list($qTitle, $answer) = preg_split('/\s/', $count);
			$answer = str_replace('"', '', $answer);
			if (preg_match('/:/', $qTitle))
			{
				list($sS, $sQ) = explode(":", $qTitle);
				#OK FOR SQL INJECTION
				$sId = q("SELECT id FROM study WHERE name = '".$sS ."'")->queryScalar();
				$question = Question::model()->findByAttributes(array('title'=>$sQ, 'studyId'=>$sId));
			}else
			{
				$question = Question::model()->findByAttributes(array('title'=>$qTitle, 'studyId'=>$studyId));
			}
			$criteria=new CDbCriteria;
			if (!$question)
				continue;

			$theAnswer = array();
			if ($question->answerType == "MULTIPLE_SELECTION")
			{
				$option = QuestionOption::model()->findbyAttributes(array('name'=>$answer, 'questionId'=>$question->id));
				if (!$option)
					continue;
				if ($interviewId != null)
				{
					$end = " AND interviewId in (". $interviewId. ")";
				}else
				{
					$end = "";
				}
				$criteria=array(
					'condition'=>'questionId = '. $question->id . $end,
				);
				$answers = Answer::model()->findAll($criteria);
				foreach ($answers as $a)
				{
					if (in_array($option->id, explode(",", $a->value)))
						$theAnswer[] = $a;
				}
			}else
			{
				$criteria=array(
					'condition'=>'1 = 1' . $end,
				);
				$answers = Answer::model()->findAll($criteria);
				foreach ($answers as $a)
				{
					if ($a->value == $answer)
						$theAnswer[] = $a;
				}
			}
			$string =  str_replace("<COUNT ".$count." />", count($theAnswer), $string);
		}

		// date interpretter
		preg_match_all('#<DATE (.+?) />#ims', $string, $dates);
		foreach ($dates[1] as $date)
		{
			list($qTitle, $amount, $period) = preg_split('/\s/', $date);
			if (preg_match('/:/', $qTitle))
			{
				list($sS, $sQ) = explode(":", $qTitle);
				$study = Study::model()->findByAttributes(array("name"=>$sS));
				$question = Question::model()->findByAttributes(array('title'=>$sQ, 'studyId'=>$study->id));
			}else
			{
				$question = Question::model()->findByAttributes(array('title'=>$qTitle, 'studyId'=>$studyId));
			}
			if(strtolower($qTitle) == "now")
			{
				$answer = new Answer;
				$answer->value = "now";
				$timeFormat = "F jS, Y";
			} else
			{
				if (!$question || $question->answerType != "DATE")
					continue;
				$criteria=new CDbCriteria;
				if ($interviewId != null)
					$end = " AND interviewId in (". $interviewId. ")";
				else
					$end = "";
				$criteria=array(
					'condition'=>'questionId = '. $question->id . $end,
				);
				$answer = Answer::model()->find($criteria);
				$timeArray = Question::timeBits($question->timeUnits);
				$timeFormat = "";
				if (in_array("BIT_MONTH", $timeArray))
					$timeFormat = "F ";
				if (in_array("BIT_DAY", $timeArray))
					$timeFormat .= "jS ";
				if (in_array("BIT_YEAR", $timeArray))
					$timeFormat .= ", Y";
				if (in_array("BIT_HOUR", $timeArray))
					$timeFormat .= "h:i A";
			}
			$newDate = date($timeFormat, strtotime($answer->value . " " .$amount . " " . $period));
			$string =  str_replace("<DATE ".$date." />", $newDate, $string);
		}

		// same as count, but limited to specific alter / alter pair questions
		preg_match_all('#<CONTAINS (.+?) />#ims', $string, $containers);
		foreach ($containers[1] as $contains)
		{
			list($qTitle, $answer) = preg_split('/\s/', $contains);
			$answer = str_replace('"', '', $answer);
			if (preg_match('/:/', $qTitle))
			{
				list($sS, $sQ) = explode(":", $qTitle);
				#OK FOR SQL INJECTION
				$sId = q("SELECT id FROM study WHERE name = '".$sS ."'")->queryScalar();
				$question = Question::model()->findByAttributes(array('title'=>$sQ, 'studyId'=>$sId));
			}else
			{
				$question = Question::model()->findByAttributes(array('title'=>$qTitle, 'studyId'=>$studyId));
			}
			$criteria=new CDbCriteria;
			if (!$question)
				continue;
			if ($interviewId != null)
			{
				$end = " AND interviewId in (". $interviewId . ")";
				if (is_numeric($alterId1))
					$end .= " AND alterId1 = " . $alterId1;
				if (is_numeric($alterId2))
					$end .= " AND alterId2 = " . $alterId2;
			}else
			{
				$end = "";
			}
			$theAnswer = array();
			if ($question->answerType == "MULTIPLE_SELECTION")
			{
				$option = QuestionOption::model()->findbyAttributes(array('name'=>$answer, 'questionId'=>$question->id));
				if (!$option)
					continue;
				$criteria=array(
					'condition'=>'questionId = '. $question->id . $end,
				);
				$answers = Answer::model()->findAll($criteria);
				foreach ($answers as $a)
				{
					if (in_array($option->id, explode(",", $a->value)))
						$theAnswer[] = $a->value;
				}
			}else
			{
				$criteria=array(
					'condition'=>"1 = 1" . $end,
				);
				$answers = Answer::model()->findAll($criteria);
				foreach ($answers as $a)
				{
					if ($a->value == $answer)
						$theAnswer[] = $a->value;
				}
			}
			$string =  str_replace("<CONTAINS ".$contains." />", count($theAnswer), $string);
		}

		// parse out and show logics
		preg_match_all('#<IF (.+?) />#ims', $string, $showlogics);
		foreach ($showlogics[1] as $showlogic)
		{
			preg_match('/(.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\"/ims', $showlogic, $exp);
			if (count($exp) > 1)
			{
				for ($i = 1; $i < 3; $i++)
				{
					if ($i == 2 || is_numeric($exp[$i]))
						continue;
					if (preg_match("#/>#", $exp[$i]))
					{
						$exp[$i] = Interview::interpretTags($exp[$i]);
					}else
					{
						if (preg_match('/:/', $exp[$i]))
						{
							list($sS, $sQ) = explode(":", $exp[$i]);
							#OK FOR SQL INJECTION
							$sId = q("SELECT id FROM study WHERE name = '".$sS ."'")->queryScalar();
							$question = Question::model()->findByAttributes(array('title'=>$sQ, 'studyId'=>$sId));
						} else
						{
							$question = Question::model()->findByAttributes(array('title'=>$exp[$i], 'studyId'=>$studyId));
						}

						if (!$question)
						{
							$exp[$i] = "";
							continue;
						}

						if ($interviewId != null)
						{
							$end = " AND interviewId in (". $interviewId .")";
						} else
						{
							$end = "";
						}

						$criteria=new CDbCriteria;
						$criteria=array(
							'condition'=>"questionId = " . $question->id . $end,
							'order'=>'id DESC',
						);
						$lastAnswer = Answer::model()->find($criteria);
						$exp[$i] = $lastAnswer->value;
					}
				}
				$logic = 'return ' . $exp[1] . ' ' . $exp[2] . ' ' . $exp[3] . ';';
				//echo $logic;
				if ($exp[1] && $exp[2] && $exp[3])
					$show = eval($logic);
				else
					$show = false;
				if ($show)
				{
					$string =  str_replace("<IF ".$showlogic." />", $exp[4], $string);
				}else
				{
					$string =  str_replace("<IF ".$showlogic." />", "", $string);
				}
			}
		}
		return nl2br($string);
	}

	public function exportEgoAlterData($file)
	{
		$ego_id_questions = q("SELECT * FROM question WHERE subjectType = 'EGO_ID' AND studyId = " . $this->studyId . " ORDER BY ordering")->queryAll();
		#OK FOR SQL INJECTION
		$ego_questions = q("SELECT * FROM question WHERE subjectType = 'EGO' AND studyId = " . $this->studyId . " ORDER BY ordering")->queryAll();
		#OK FOR SQL INJECTION
		$alter_questions = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND studyId = " . $this->studyId . " ORDER BY ordering")->queryAll();

		$criteria=new CDbCriteria;
		$criteria->condition = ("studyId = $this->studyId and subjectType = 'NETWORK'");
		$criteria->order = "ordering";
		$network_questions = Question::model()->findAll($criteria);

		$alters = Alters::model()->findAll(array('order'=>'id', 'condition'=>'FIND_IN_SET(:x, interviewId)', 'params'=>array(':x'=>$this->id)));

		if (!$alters)
		{
			$alters = array('0'=>array('id'=>null));
		} else
		{
			if (isset($_POST['expressionId']) && $_POST['expressionId'])
			{
				$stats = new Statistics;
				$stats->initComponents($this->id, $_POST['expressionId']);
			}
		}

		$text = "";
		$count = 1;

		foreach ($alters as $alter)
		{
			$answers = array();
			$answers[] = $this->id;
			$ego_ids = array();
			$ego_id_string = array();
			$study = Study::model()->findByPk($this->studyId);
			$optionsRaw = q("SELECT * FROM questionOption WHERE studyId = " . $study->id)->queryAll();

			// create an array with option ID as key
			$options = array();
			$optionLabels = array();
			foreach ($optionsRaw as $option)
			{
				$options[$option['id']] = $option['value'];
				$optionLabels[$option['id']] = $option['name'];
			}
			foreach ($ego_id_questions as $question)
			{
				#OK FOR SQL INJECTION
				$result = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question['id']));
				$answer = $result->value;
				if ($question['answerType'] == "MULTIPLE_SELECTION")
				{
					$optionIds = explode(',', $answer);
					$list = array();
					foreach ($optionIds as $optionId)
					{
						if (isset($options[$optionId]))
							$ego_ids[] = $options[$optionId];
							$ego_id_string[] = $optionLabels[$optionId];
					}
				} elseif ($question['answerType'] == "RANDOM_NUMBER"){
					$ego_ids[] = str_replace(',', '', $answer);
					//skip appending the random number to the EgoID column
				} else
				{
					$ego_ids[] = str_replace(',', '', $answer);
					$ego_id_string[] = str_replace(',', '', $answer);
				}
			}
			$answers[] = implode("_", $ego_id_string);
			$answers[] = date("Y-m-d h:i:s", $this->start_date);
			$answers[] = date("Y-m-d h:i:s", $this->complete_date);
			foreach ($ego_ids as $eid)
			{
				$answers[] = $eid;
			}
			foreach ($ego_questions as $question)
			{
				$answer = Answer::model()->findByAttributes(array("interviewId"=>$this->id, "questionId"=>$question['id']));
				if(!$answer){
					$answers[] = $study->valueNotYetAnswered;
					continue;
				}

				if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip)
				{
					if ($question['answerType'] == "SELECTION")
					{
						if (isset($options[$answer->value]))
							$answers[] = $options[$answer->value];
						else
							$answers[] = "";
					} else if ($question['answerType'] == "MULTIPLE_SELECTION")
					{
						$optionIds = explode(',', $answer->value);
						$list = array();
						foreach ($optionIds as $optionId)
						{
							if (isset($options[$optionId]))
								$list[] = $options[$optionId];
						}
						$answers[] = implode('; ', $list);
					} else
					{
						$answers[] = $answer->value;
					}
				} else if ($answer->skipReason == "DONT_KNOW"){
						$answers[] = $study->valueDontKnow;
				} else if ($answer->skipReason == "REFUSE"){
						$answers[] = $study->valueRefusal;
				} else if($answer->value == $study->valueLogicalSkip)
				{
					$answers[] = $study->valueLogicalSkip;
				} else {
					$answers[] = "";
				}
			}

			foreach ($network_questions as $question)
			{
				$answer = Answer::model()->findByAttributes(array("interviewId"=>$this->id, "questionId"=>$question->id));
				if(!$answer){
					$answers[] = $study->valueNotYetAnswered;
					continue;
				}
				if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip)
				{
					if ($question->answerType == "SELECTION")
					{
						if (isset($options[$answer]))
							$answers[] = $options[$answer];
						else
							$answers[] = "";
					} else if ($question->answerType == "MULTIPLE_SELECTION")
					{
						$optionIds = explode(',', $answer->value);
						$list = array();
						foreach ($optionIds as $optionId)
						{
							if (isset($options[$optionId]))
								$list[] = $options[$optionId];
						}
						$answers[] = implode('; ', $list);
					} else
					{
						$answers[] = $answer->value;
					}
				} else if ($answer->skipReason == "DONT_KNOW")
				{
					$answers[] = $study->valueDontKnow;
				} else if ($answer->skipReason == "REFUSE")
				{
					$answers[] = $study->valueRefusal;
				}  else if($answer->value == $study->valueLogicalSkip)
				{
					$answers[] = $study->valueLogicalSkip;
				} else {
					$answers[] = "";
				}
			}

			if (isset($stats))
			{
				$answers[] = $stats->getDensity();
				$answers[] = $stats->maxDegree();
				$answers[] = $stats->maxBetweenness();
				$answers[] = $stats->maxEigenvector();
				$answers[] = $stats->degreeCentralization();
				$answers[] = $stats->betweennessCentralization();
				$answers[] = count($stats->components);
				$answers[] = count($stats->dyads);
				$answers[] = count($stats->isolates);
			}

			if (isset($alter->id))
			{
				$answers[] = $count;
				$answers[] = $alter->name;
				foreach ($alter_questions as $question)
				{
					$answer = Answer::model()->findByAttributes(array("interviewId"=>$this->id, "questionId"=>$question['id'], "alterId1"=>$alter->id));
					if(!$answer){
						$answers[] = $study->valueNotYetAnswered;
						continue;
					}
					if ($answer->value != "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip)
					{
						if ($question['answerType'] == "SELECTION")
						{
							$answers[] = $options[$answer->value];
						} else if ($question['answerType'] == "MULTIPLE_SELECTION")
						{
							$optionIds = explode(',', $answer->value);
							$list = array();
							foreach ($optionIds as $optionId)
							{
								if (isset($options[$optionId]))
									$list[] = $options[$optionId];
							}
							if (count($list) == 0)
								$answers[] = $study->valueNotYetAnswered;
							else
								$answers[] = implode('; ', $list);
						} else
						{
							$answers[] = $answer->value;
						}
					} else if ($answer->skipReason == "DONT_KNOW"){
							$answers[] = $study->valueDontKnow;
					} else if ($answer->skipReason == "REFUSE"){
							$answers[] = $study->valueRefusal;
					} else if($answer->value == $study->valueLogicalSkip)
					{
						$answers[] = $study->valueLogicalSkip;
					} else {
						$answers[] = "";
					}
				}
			}else{
				$answers[] = 0;
				$answers[] = "";
				foreach ($alter_questions as $question)
				{
					$answers[] = $study->valueNotYetAnswered;
				}
			}

			if (isset($stats))
			{
				$answers[] = $stats->getDegree($alter->id);
				$answers[] = $stats->getBetweenness($alter->id);
				$answers[] = $stats->eigenvectorCentrality($alter->id);
			}
			fputcsv($file, $answers);
			//$text .= implode(',', $answers) . "\n";
			$count++;
		}
		fclose($file);
		//return $text;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('active', $this->active);
		$criteria->compare('studyId', $this->studyId);
		$criteria->compare('completed', $this->completed);

		return new CActiveDataProvider($this, array(
				'criteria'=>$criteria,
			));
	}
}
