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
    public static function model($className = __CLASS__)
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
            array('id, active, studyId, completed', 'length', 'max' => 255),
            array('id, active, studyId', 'numerical', 'integerOnly' => true),
            array('completed', 'default', 'value' => 0),
            array(
                'start_date', 'default',
                'value' => time(),
                'setOnEmpty' => true, 'on' => 'insert'
            ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, active, studyId, completed', 'safe', 'on' => 'search'),
        );
    }

    public function getHasMatches()
    {
        $criteria = array(
            'condition' => "interviewId1 = $this->id OR interviewId2 = $this->id",
        );
        $matches = MatchedAlters::model()->findAll($criteria);
        foreach ($matches as $match) {
            if ($match->notes != "")
                return 2;
        }
        if (count($matches) > 0)
            return 1;
        return false;
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
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
            'studyId' => 'Interview Study ID',
            'completed' => 'Completed',
        );
    }

    public static function getInterviewFromEmail($studyId, $email)
    {
        $answers = Answer::model()->findAllByAttributes(array('questionType' => "EGO_ID", "studyId" => $studyId));
        $interview = false;
        foreach ($answers as $answer) {
            if ($answer->value == $email)
                $interview = Interview::model()->findByPK($answer->interviewId);
        }
        return $interview;
    }

    /**
     * retrieves interview (or create new one) from MMIC prime key
     * @param $studyId
     * @param $primekey
     * @param $prefill (Ego ID Prefill)
     * @param $question (Ego Questions Prefill)
     * @return array|bool|CActiveRecord|Interview|mixed|null
     */
    public static function getInterviewFromPrimekey($studyId, $primekey, $prefill, $questions = array())
    {
        $answers = Answer::model()->findAllByAttributes(array(
            'questionType' => 'EGO_ID',
            'studyId' => $studyId
        ));

        foreach ($answers as $answer) {
            if ($answer->value == $primekey) {
                return Interview::model()->findByPk($answer->interviewId);
            }
        }

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $studyId and subjectType = 'EGO_ID' AND answerType != 'RANDOM_NUMBER'");
        $egoQs = Question::model()->findAll($criteria);
        $study = Study::model()->findByPk($studyId);

        if (count($egoQs) == 0) {
            return false;
        }

        $interview = new Interview;
        $interview->studyId = $studyId;
        $interview->completed = 0;
        $interview->save();

        $prefill['prime_key'] = $primekey;

        foreach ($egoQs as $egoQ) {
            $egoIdQ = new Answer;
            $egoIdQ->interviewId = $interview->id;
            $egoIdQ->studyId = $studyId;
            $egoIdQ->questionType = "EGO_ID";
            $egoIdQ->answerType = $egoQ->answerType;
            $egoIdQ->questionId = $egoQ->id;
            $egoIdQ->skipReason = "NONE";
            if (isset($prefill[$egoQ->title])) {
                $egoIdQ->value = $prefill[$egoQ->title];
            } else {
                $egoIdQ->skipReason = "DONT_KNOW";
                $egoIdQ->value = $study->valueDontKnow;
            }
            $egoIdQ->save();
        }

        $randoms = Question::model()->findAllByAttributes(array("answerType" => "RANDOM_NUMBER", "studyId" => $studyId));
        foreach ($randoms as $q) {
            $a = $q->id;
            $answer = new Answer;
            $answer->interviewId = $interview->id;
            $answer->studyId = $studyId;
            $answer->questionType = "EGO_ID";
            $answer->answerType = "RANDOM_NUMBER";
            $answer->questionId = $q->id;
            $answer->skipReason = "NONE";
            $answer->value = mt_rand($q->minLiteral, $q->maxLiteral);
            $answer->save();
        }

        if (count($questions) > 0)
            $interview->fillQs($questions, $interview->id, $studyId);

        return $interview;
    }

    public static function fillQs($qs, $interviewId, $studyId)
    {
        foreach ($qs as $title => $value) {
            $question = Question::model()->findByAttributes(array("title" => $title, "studyId" => $studyId));
            $answer = Answer::model()->findByAttributes(array("interviewId" => $interviewId, "questionId" => $question->id));
            if ($answer)
                continue;
            $answer = new Answer;
            $answer->interviewId = $interviewId;
            $answer->studyId = $studyId;
            $answer->questionType = $question->subjectType;
            $answer->answerType = $question->answerType;
            $answer->questionId = $question->id;
            $answer->skipReason = "NONE";
            if ($value) {
                $answer->value = $value;
            } else {
                $answer->skipReason = "DONT_KNOW";
                $study = Study::model()->findByPk($studyId);
                $answer->value = $study->valueDontKnow;
            }
            $answer->save();
        }
    }

    public static function countAlters($id)
    {
        $criteria = array(
            'condition' => "FIND_IN_SET(" . $id . ", interviewId)",
        );
        $models = Alters::model()->findAll($criteria);
        return count($models);
    }

    public static function getRespondant($id)
    {
        $interview = Interview::model()->findByPK($id);
        $studyId = $interview->studyId;

        if (!$studyId)
            return 'error';

        $egoIdAnswer = Answer::model()->find(array(
            'condition' => "interviewId=:interviewId AND subjectType = 'EGO_ID' AND value != ''",
            'params' => array(':interviewId' => $id),
            "order" => "ordering",
        ));

        if (isset($egoIdAnswer->value))
            return $egoIdAnswer->value;
        else
            return '';
    }

    public static function getEgoId($id)
    {
        $interview = Interview::model()->findByPk($id);
        if(!$interview)
            return "IDERROR";
        $criteria = array(
            "condition" => "subjectType = 'EGO_ID' AND studyId = " . $interview->studyId . " AND answerType NOT IN ('STORED_VALUE', 'RANDOM_NUMBER')",
            "order" => "ordering",
        );
        $ego_id_questions = Question::model()->findAll($criteria);
        $egoId = "";
        foreach ($ego_id_questions as $question) {
            $headers[] = $question->title;
        }
        $ego_ids = array();
        foreach ($ego_id_questions as $question) {
            if ($question->answerType == "MULTIPLE_SELECTION") {
                $id_response = Answer::model()->findByAttributes(array("interviewId" => $interview->id, "questionId" => $question->id));
                $option = QuestionOption::model()->findByAttributes(array("id" => $id_response->value));
                if ($option) {
                    $ego_ids[] = $option->name;
                }
            } else {
                $id_response = Answer::model()->findByAttributes(array("interviewId" => $interview->id, "questionId" => $question->id));
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
        $interview = Interview::model()->findByPk((int)$interviewId);
        $egoAnswer = Answer::model()->findByAttributes(array("interviewId" => $interview->id, "questionId" => $study->multiSessionEgoId));
        $interviewIds = array();
        if ($interview && $study && $study->multiSessionEgoId) {
            foreach ($study->multiIdQs() as $q) {
                $newAnswers = Answer::model()->findAllByAttributes(array("studyId" => $q->studyId, "questionId" => $q->id));
                foreach ($newAnswers as $a) {
                    if ($a->value == $egoAnswer->value)
                        $interviewIds[] = $a->interviewId;
                }
            }
        } else {
            $interviewIds = $interview->id;
        }
        return $interviewIds;
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

        $interview = Interview::model()->findByPk($interviewId);
        $study = Study::model()->findByPk((int)$interview->studyId);

        $interviewId = Interview::multiInterviewIds($interviewId, $study);

        if (is_array($interviewId))
            $interviewId = implode(",", $interviewId);

        // parse out and replace variables
        preg_match_all('#<VAR (.+?) />#ims', $string, $vars);
        foreach ($vars[1] as $var) {
            if (preg_match('/:/', $var)) {
                list($sS, $sQ) = explode(":", $var);
                $s = Study::model()->findByAttributes(array("name" => $sS));
                $question = Question::model()->findByAttributes(array('title' => $sQ, 'studyId' => $s->id));
            } else {
                $question = Question::model()->findByAttributes(array('title' => $var, 'studyId' => $studyId));
            }

            if ($question) {
                if ($interviewId != null) {
                    $end = " AND interviewId in (" . $interviewId . ")";
                } else {
                    $end = "";
                }
                $criteria = new CDbCriteria;
                $criteria = array(
                    'condition' => "questionId = " . $question->id . $end,
                    'order' => 'id DESC',
                );
                $lastAnswer = Answer::model()->find($criteria);
            }
            if (isset($lastAnswer)) {
                if ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(",", $lastAnswer->value);
                    $lastAnswer->value = "";
                    $answerArray = array();
                    foreach ($optionIds as $optionId) {
                        $option = QuestionOption::model()->findbyPk($optionId);
                        if ($option) {
                            $criteria = new CDbCriteria;
                            $criteria = array(
                                'condition' => "optionId = " . $option->id . " AND interviewId in ($interviewId)",
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
                $string =  preg_replace('#<VAR ' . $var . ' />#', $lastAnswer->value, $string);
            } else {
                $string =  preg_replace('#<VAR ' . $var . ' />#', '', $string);
            }
        }

        // performs calculations on questions
        preg_match_all('#<CALC (.+?) />#ims', $string, $calcs);
        foreach ($calcs[1] as $calc) {
            preg_match('/(\w+)/', $calc, $vars);
            foreach ($vars as $var) {
                if (preg_match('/:/', $var)) {
                    list($sS, $sQ) = explode(":", $var);
                    $s = Study::model()->findByAttributes(array("name" => $sS));
                    $question = Question::model()->findByAttributes(array('title' => $sQ, 'studyId' => $s->id));
                } else {
                    $question = Question::model()->findByAttributes(array('title' => $var, 'studyId' => $studyId));
                }
                if ($question) {
                    if ($interviewId != null) {
                        $end = " AND interviewId in (" . $interviewId . ")";
                    } else {
                        $end = "";
                    }
                    $criteria = new CDbCriteria;
                    $criteria = array(
                        'condition' => "questionId = " . $question->id . $end,
                        'order' => 'id DESC',
                    );
                    $lastAnswer = Answer::model()->find($criteria);
                }
                if (isset($lastAnswer))
                    $logic =  preg_replace('#' . $var . '#', $lastAnswer->value, $calc);
                else
                    $logic =  preg_replace('#' . $var . '#', '', $calc);
            }
            $logic = 'return ' . $logic . ';';

            $calculation = eval($logic);
            $string =  str_replace("<CALC " . $calc . " />", $calculation, $string);
        }

        // counts numbers of times question is answered with string
        preg_match_all('#<COUNT (.+?) />#ims', $string, $counts);
        foreach ($counts[1] as $count) {
            list($qTitle, $answer) = preg_split('/\s/', $count);
            $answer = str_replace('"', '', $answer);
            if (preg_match('/:/', $qTitle)) {
                list($sS, $sQ) = explode(":", $qTitle);
                $s = Study::model()->findByAttributes(array("name" => $sS));
                $question = Question::model()->findByAttributes(array('title' => $sQ, 'studyId' => $s->id));
            } else {
                $question = Question::model()->findByAttributes(array('title' => $qTitle, 'studyId' => $studyId));
            }
            $criteria = new CDbCriteria;
            if (!$question)
                continue;

            $theAnswer = array();
            if ($question->answerType == "MULTIPLE_SELECTION") {
                $option = QuestionOption::model()->findbyAttributes(array('name' => $answer, 'questionId' => $question->id));
                if (!$option)
                    continue;
                if ($interviewId != null) {
                    $end = " AND interviewId in (" . $interviewId . ")";
                } else {
                    $end = "";
                }
                $criteria = array(
                    'condition' => 'questionId = ' . $question->id . $end,
                );
                $answers = Answer::model()->findAll($criteria);
                foreach ($answers as $a) {
                    if (in_array($option->id, explode(",", $a->value)))
                        $theAnswer[] = $a;
                }
            } else {
                $criteria = array(
                    'condition' => '1 = 1' . $end,
                );
                $answers = Answer::model()->findAll($criteria);
                foreach ($answers as $a) {
                    if ($a->value == $answer)
                        $theAnswer[] = $a;
                }
            }
            $string =  str_replace("<COUNT " . $count . " />", count($theAnswer), $string);
        }

        // date interpretter
        preg_match_all('#<DATE (.+?) />#ims', $string, $dates);
        foreach ($dates[1] as $date) {
            list($qTitle, $amount, $period) = preg_split('/\s/', $date);
            if (preg_match('/:/', $qTitle)) {
                list($sS, $sQ) = explode(":", $qTitle);
                $study = Study::model()->findByAttributes(array("name" => $sS));
                $question = Question::model()->findByAttributes(array('title' => $sQ, 'studyId' => $study->id));
            } else {
                $question = Question::model()->findByAttributes(array('title' => $qTitle, 'studyId' => $studyId));
            }
            if (strtolower($qTitle) == "now") {
                $answer = new Answer;
                $answer->value = "now";
                $timeFormat = "F jS, Y";
            } else {
                if (!$question || $question->answerType != "DATE")
                    continue;
                $criteria = new CDbCriteria;
                if ($interviewId != null)
                    $end = " AND interviewId in (" . $interviewId . ")";
                else
                    $end = "";
                $criteria = array(
                    'condition' => 'questionId = ' . $question->id . $end,
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
            $newDate = date($timeFormat, strtotime($answer->value . " " . $amount . " " . $period));
            $string =  str_replace("<DATE " . $date . " />", $newDate, $string);
        }

        // same as count, but limited to specific alter / alter pair questions
        preg_match_all('#<CONTAINS (.+?) />#ims', $string, $containers);
        foreach ($containers[1] as $contains) {
            list($qTitle, $answer) = preg_split('/\s/', $contains);
            $answer = str_replace('"', '', $answer);
            if (preg_match('/:/', $qTitle)) {
                list($sS, $sQ) = explode(":", $qTitle);
                $s = Study::model()->findByAttributes(array("name" => $sS));
                $question = Question::model()->findByAttributes(array('title' => $sQ, 'studyId' => $s->id));
            } else {
                $question = Question::model()->findByAttributes(array('title' => $qTitle, 'studyId' => $studyId));
            }
            $criteria = new CDbCriteria;
            if (!$question)
                continue;
            if ($interviewId != null) {
                $end = " AND interviewId in (" . $interviewId . ")";
                if (is_numeric($alterId1))
                    $end .= " AND alterId1 = " . $alterId1;
                if (is_numeric($alterId2))
                    $end .= " AND alterId2 = " . $alterId2;
            } else {
                $end = "";
            }
            $theAnswer = array();
            if ($question->answerType == "MULTIPLE_SELECTION") {
                $option = QuestionOption::model()->findbyAttributes(array('name' => $answer, 'questionId' => $question->id));
                if (!$option)
                    continue;
                $criteria = array(
                    'condition' => 'questionId = ' . $question->id . $end,
                );
                $answers = Answer::model()->findAll($criteria);
                foreach ($answers as $a) {
                    if (in_array($option->id, explode(",", $a->value)))
                        $theAnswer[] = $a->value;
                }
            } else {
                $criteria = array(
                    'condition' => "1 = 1" . $end,
                );
                $answers = Answer::model()->findAll($criteria);
                foreach ($answers as $a) {
                    if ($a->value == $answer)
                        $theAnswer[] = $a->value;
                }
            }
            $string =  str_replace("<CONTAINS " . $contains . " />", count($theAnswer), $string);
        }

        // parse out and show logics
        preg_match_all('#<IF (.+?) />#ims', $string, $showlogics);
        foreach ($showlogics[1] as $showlogic) {
            preg_match('/(.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\"/ims', $showlogic, $exp);
            if (count($exp) > 1) {
                for ($i = 1; $i < 3; $i++) {
                    if ($i == 2 || is_numeric($exp[$i]))
                        continue;
                    if (preg_match("#/>#", $exp[$i])) {
                        $exp[$i] = Interview::interpretTags($exp[$i]);
                    } else {
                        if (preg_match('/:/', $exp[$i])) {
                            list($sS, $sQ) = explode(":", $exp[$i]);
                            $s = Study::model()->findByAttributes(array("name" => $sS));
                            $question = Question::model()->findByAttributes(array('title' => $sQ, 'studyId' => $s->id));
                        } else {
                            $question = Question::model()->findByAttributes(array('title' => $exp[$i], 'studyId' => $studyId));
                        }

                        if (!$question) {
                            $exp[$i] = "";
                            continue;
                        }

                        if ($interviewId != null) {
                            $end = " AND interviewId in (" . $interviewId . ")";
                        } else {
                            $end = "";
                        }

                        $criteria = new CDbCriteria;
                        $criteria = array(
                            'condition' => "questionId = " . $question->id . $end,
                            'order' => 'id DESC',
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
                if ($show) {
                    $string =  str_replace("<IF " . $showlogic . " />", $exp[4], $string);
                } else {
                    $string =  str_replace("<IF " . $showlogic . " />", "", $string);
                }
            }
        }
        return nl2br($string);
    }

    public function exportEgoAlterData($file, $withAlters = false)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'EGO_ID'");
        $criteria->order = "ordering";
        $ego_id_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'EGO'");
        $criteria->order = "ordering";
        $ego_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'ALTER'");
        $criteria->order = "ordering";
        $alter_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'NETWORK'");
        $criteria->order = "ordering";
        $network_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'NAME_GENERATOR'");
        $criteria->order = "ordering";
        $name_gen_questions = Question::model()->findAll($criteria);

        $alters = Alters::model()->findAll(array('order' => 'id', 'condition' => 'FIND_IN_SET(:x, interviewId)', 'params' => array(':x' => $this->id)));

        if (!$alters) {
            $alters = array('0' => array('id' => null));
        } else {
            if (isset($_POST['expressionId']) && $_POST['expressionId']) {
                $stats = new Statistics;
                $stats->initComponents($this->id, $_POST['expressionId']);
            }
        }

        $text = "";
        $count = 1;

        $matchIntId = "";
        $matchUser = "";
        $criteria = array(
            'condition' => "studyId = $this->studyId",
        );
        $matchAtAll = MatchedAlters::model()->find($criteria);
        if ($matchAtAll) {
            $criteria = array(
                'condition' => "interviewId1 = $this->id OR interviewId2 = $this->id",
            );
            $match = MatchedAlters::model()->find($criteria);
            if ($match) {
                if ($this->id == $match->interviewId1)
                    $matchInt = Interview::model()->findByPk($match->interviewId2);
                else
                    $matchInt = Interview::model()->findByPk($match->interviewId1);
                $matchIntId = $match->getMatchId();
                $matchUser = User::getName($match->userId);
            }
        }
        foreach ($alters as $alter) {
            $answers = array();
            $answers[] = $this->id;
            $ego_ids = array();
            $ego_id_string = array();
            $study = Study::model()->findByPk($this->studyId);
            $optionsRaw = QuestionOption::model()->findAllByAttributes(array("studyId" => $study->id));

            // create an array with option ID as key
            $options = array();
            $optionLabels = array();
            foreach ($optionsRaw as $option) {
                $options[$option->id] = $option->value;
                $optionLabels[$option->id] = $option->name;
            }
            foreach ($ego_id_questions as $question) {

                #OK FOR SQL INJECTION
                $result = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id));
                $answer = $result->value;

                if ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer);
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId])) {
                            $ego_ids[] = $options[$optionId];
                            if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                                $ego_id_string[] = $optionLabels[$optionId];
                        } else {
                            $ego_ids[] = "MISSING_OPTION ($optionId)";
                            if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                                $ego_id_string[] = "MISSING_OPTION ($optionId)";
                        }
                    }
                    if (!$optionIds) {
                        $ego_ids[] = "";
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                            $ego_id_string[] = "";
                    }
                } else {
                    $ego_ids[] = str_replace(',', '', $answer);
                    if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                        $ego_id_string[] = str_replace(',', '', $answer);
                }
            }
            $answers[] = implode("_", $ego_id_string);
            $answers[] = date("Y-m-d h:i:s", $this->start_date);
            $answers[] = date("Y-m-d h:i:s", $this->complete_date);
            foreach ($ego_ids as $eid) {
                $answers[] = $eid;
            }
            foreach ($ego_questions as $question) {
                $answer = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id));
                if (!$answer) {
                    $answers[] = $study->valueNotYetAnswered;
                    continue;
                }

                if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                    if ($question->answerType == "SELECTION") {
                        if (isset($options[$answer->value]))
                            $answers[] = $options[$answer->value];
                        else
                            $answers[] = "";
                    } else if ($question->answerType == "MULTIPLE_SELECTION") {
                        $optionIds = explode(',', $answer->value);
                        $list = array();
                        foreach ($optionIds as $optionId) {
                            if (isset($options[$optionId]))
                                $list[] = $options[$optionId];
                        }
                        $answers[] = implode('; ', $list);
                    } else if ($question->answerType == "TIME_SPAN") {
                        if (!strstr($answer->value, ";")) {
                            $times = array();
                            if (preg_match("/(\d*)\sYEARS/i", $answer->value, $test))
                                $times[] = $test[0];
                            if (preg_match("/(\d*)\sMONTHS/i", $answer->value, $test))
                                $times[] = $test[0];
                            if (preg_match("/(\d*)\sWEEKS/i", $answer->value, $test))
                                $times[] = $test[0];
                            if (preg_match("/(\d*)\sDAYS/i", $answer->value, $test))
                                $times[] = $test[0];
                            if (preg_match("/(\d*)\sHOURS/i", $answer->value, $test))
                                $times[] = $test[0];
                            if (preg_match("/(\d*)\sMINUTES/i", $answer->value, $test))
                                $times[] = $test[0];
                            $answer->value = implode("; ", $times);
                        }
                        $answers[] = $answer->value;
                    } else {
                        $answers[] = $answer->value;
                    }
                } else if ($answer->skipReason == "DONT_KNOW") {
                    $answers[] = $study->valueDontKnow;
                } else if ($answer->skipReason == "REFUSE") {
                    $answers[] = $study->valueRefusal;
                } else if ($answer->value == $study->valueLogicalSkip) {
                    $answers[] = $study->valueLogicalSkip;
                } else {
                    $answers[] = "";
                }
            }

            foreach ($network_questions as $question) {
                $answer = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id));
                if (!$answer) {
                    $answers[] = $study->valueNotYetAnswered;
                    continue;
                }
                if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                    if ($question->answerType == "SELECTION") {
                        if (isset($options[$answer]))
                            $answers[] = $options[$answer];
                        else
                            $answers[] = "";
                    } else if ($question->answerType == "MULTIPLE_SELECTION") {
                        $optionIds = explode(',', $answer->value);
                        $list = array();
                        foreach ($optionIds as $optionId) {
                            if (isset($options[$optionId]))
                                $list[] = $options[$optionId];
                        }
                        $answers[] = implode('; ', $list);
                    } else {
                        $answers[] = $answer->value;
                    }
                } else if ($answer->skipReason == "DONT_KNOW") {
                    $answers[] = $study->valueDontKnow;
                } else if ($answer->skipReason == "REFUSE") {
                    $answers[] = $study->valueRefusal;
                } else if ($answer->value == $study->valueLogicalSkip) {
                    $answers[] = $study->valueLogicalSkip;
                } else {
                    $answers[] = "";
                }
            }

            if (isset($stats)) {
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

            if (isset($alter->id)) {
                if ($matchAtAll) {
                    $matchId = "";
                    $matchName = "";
                    $criteria = array(
                        'condition' => "alterId1 = $alter->id OR alterId2 = $alter->id",
                    );
                    $match = MatchedAlters::model()->find($criteria);
                    if ($match) {
                        $matchId = $match->id;
                        $matchName = $match->matchedName;
                    }
                    $answers[] = $matchIntId;
                    $answers[] = $matchUser;
                    $answers[] = $count;
                    if ($withAlters) {
                        $answers[] = $alter->name;
                        $answers[] = $matchName;
                    }
                    $answers[] = $matchId;
                } else {
                    $answers[] = $count;
                    if ($withAlters)
                        $answers[] = $alter->name;
                }
                foreach ($name_gen_questions as $question) {
                    if (count($name_gen_questions) == 1) {
                        $answers[]  = 1;
                        continue;
                    }
                    $nameGenQIds = explode(",", $alter->nameGenQIds);
                    if (in_array($question->id, $nameGenQIds)) {
                        $answers[]  = 1;
                    } else {
                        $answers[]  = 0;
                    }
                }
                foreach ($alter_questions as $question) {
                    $answer = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id, "alterId1" => $alter->id));
                    if (!$answer) {
                        $answers[] = $study->valueNotYetAnswered;
                        continue;
                    }
                    if ($answer->value != "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                        if ($question->answerType == "SELECTION") {
                            $answers[] = $options[$answer->value];
                        } else if ($question->answerType == "MULTIPLE_SELECTION") {
                            $optionIds = explode(',', $answer->value);
                            $list = array();
                            foreach ($optionIds as $optionId) {
                                if (isset($options[$optionId]))
                                    $list[] = $options[$optionId];
                            }
                            if (count($list) == 0)
                                $answers[] = $study->valueNotYetAnswered;
                            else
                                $answers[] = implode('; ', $list);
                        } else {
                            $answers[] = $answer->value;
                        }
                    } else if ($answer->skipReason == "DONT_KNOW") {
                        $answers[] = $study->valueDontKnow;
                    } else if ($answer->skipReason == "REFUSE") {
                        $answers[] = $study->valueRefusal;
                    } else if ($answer->value == $study->valueLogicalSkip) {
                        $answers[] = $study->valueLogicalSkip;
                    } else {
                        $answers[] = "";
                    }
                }
            } else {
                $answers[] = 0;
                $answers[] = "";
                foreach ($alter_questions as $question) {
                    $answers[] = $study->valueNotYetAnswered;
                }
            }

            if (isset($stats)) {
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


    public function exportEgoStudy($file)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'EGO_ID'");
        $criteria->order = "ordering";
        $ego_id_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'EGO'");
        $criteria->order = "ordering";
        $ego_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'ALTER'");
        $criteria->order = "ordering";
        $alter_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'NETWORK'");
        $criteria->order = "ordering";
        $network_questions = Question::model()->findAll($criteria);

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $this->studyId and subjectType = 'NAME_GENERATOR'");
        $criteria->order = "ordering";
        $name_gen_questions = Question::model()->findAll($criteria);

        $text = "";
        $count = 1;

        $matchIntId = "";
        $matchUser = "";
        $criteria = array(
            'condition' => "studyId = $this->studyId",
        );

        $answers = array();
        $answers[] = $this->id;
        $ego_ids = array();
        $ego_id_string = array();
        $study = Study::model()->findByPk($this->studyId);
        $optionsRaw = QuestionOption::model()->findAllByAttributes(array("studyId" => $study->id));

        // create an array with option ID as key
        $options = array();
        $optionLabels = array();
        foreach ($optionsRaw as $option) {
            $options[$option->id] = $option->value;
            $optionLabels[$option->id] = $option->name;
        }
        $alters = Alters::model()->findAll(array('order' => 'id', 'condition' => 'FIND_IN_SET(:x, interviewId)', 'params' => array(':x' => $this->id)));

        foreach ($ego_id_questions as $question) {

            $result = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id));
            $answer = $result->value;

            if ($question->answerType == "MULTIPLE_SELECTION") {
                $optionIds = explode(',', $answer);
                foreach ($optionIds as $optionId) {
                    if (isset($options[$optionId])) {
                        $ego_ids[] = $options[$optionId];
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                            $ego_id_string[] = $optionLabels[$optionId];
                    } else {
                        $ego_ids[] = "MISSING_OPTION ($optionId)";
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                            $ego_id_string[] = "MISSING_OPTION ($optionId)";
                    }
                }
                if (!$optionIds) {
                    $ego_ids[] = "";
                    if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                        $ego_id_string[] = "";
                }
            } else {
                $ego_ids[] = str_replace(',', '', $answer);
                if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER")
                    $ego_id_string[] = str_replace(',', '', $answer);
            }
        }
        $answers[] = implode("_", $ego_id_string);
        $answers[] = date("Y-m-d h:i:s", $this->start_date);
        $answers[] = date("Y-m-d h:i:s", $this->complete_date);
        foreach ($ego_ids as $eid) {
            $answers[] = $eid;
        }
        foreach ($ego_questions as $question) {
            $answer = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id));
            if (!$answer) {
                $answers[] = $study->valueNotYetAnswered;
                continue;
            }

            if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                if ($question->answerType == "SELECTION") {
                    if (isset($options[$answer->value]))
                        $answers[] = $options[$answer->value];
                    else
                        $answers[] = "";
                } else if ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer->value);
                    $list = array();
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId]))
                            $list[] = $options[$optionId];
                    }
                    $answers[] = implode('; ', $list);
                } else if ($question->answerType == "TIME_SPAN") {
                    if (!strstr($answer->value, ";")) {
                        $times = array();
                        if (preg_match("/(\d*)\sYEARS/i", $answer->value, $test))
                            $times[] = $test[0];
                        if (preg_match("/(\d*)\sMONTHS/i", $answer->value, $test))
                            $times[] = $test[0];
                        if (preg_match("/(\d*)\sWEEKS/i", $answer->value, $test))
                            $times[] = $test[0];
                        if (preg_match("/(\d*)\sDAYS/i", $answer->value, $test))
                            $times[] = $test[0];
                        if (preg_match("/(\d*)\sHOURS/i", $answer->value, $test))
                            $times[] = $test[0];
                        if (preg_match("/(\d*)\sMINUTES/i", $answer->value, $test))
                            $times[] = $test[0];
                        $answer->value = implode("; ", $times);
                    }
                    $answers[] = $answer->value;
                } else {
                    $answers[] = $answer->value;
                }
            } else if ($answer->skipReason == "DONT_KNOW") {
                $answers[] = $study->valueDontKnow;
            } else if ($answer->skipReason == "REFUSE") {
                $answers[] = $study->valueRefusal;
            } else if ($answer->value == $study->valueLogicalSkip) {
                $answers[] = $study->valueLogicalSkip;
            } else {
                $answers[] = "";
            }
        }

        foreach ($network_questions as $question) {
            $answer = Answer::model()->findByAttributes(array("interviewId" => $this->id, "questionId" => $question->id));
            if (!$answer) {
                $answers[] = $study->valueNotYetAnswered;
                continue;
            }
            if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                if ($question->answerType == "SELECTION") {
                    if (isset($options[$answer]))
                        $answers[] = $options[$answer];
                    else
                        $answers[] = "";
                } else if ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer->value);
                    $list = array();
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId]))
                            $list[] = $options[$optionId];
                    }
                    $answers[] = implode('; ', $list);
                } else {
                    $answers[] = $answer->value;
                }
            } else if ($answer->skipReason == "DONT_KNOW") {
                $answers[] = $study->valueDontKnow;
            } else if ($answer->skipReason == "REFUSE") {
                $answers[] = $study->valueRefusal;
            } else if ($answer->value == $study->valueLogicalSkip) {
                $answers[] = $study->valueLogicalSkip;
            } else {
                $answers[] = "";
            }
        }
/*
        if (isset($stats)) {
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
        foreach ($name_gen_questions as $question) {
            if (count($name_gen_questions) == 1) {
                $answers[]  = 1;
                continue;
            }
            $nameGenQIds = explode(",", $alter->nameGenQIds);
            if (in_array($question->id, $nameGenQIds)) {
                $answers[]  = 1;
            } else {
                $answers[]  = 0;
            }
        }
        foreach ($alter_questions as $question) {
            if ($question->answerType == "NUMERICAL") {
                $vals = array();
                foreach ($alters as $alter) {
                    $val = Answer::model()->findByAttributes(array("alterId1" => $alter->id, "questionId" => $question->id));
                    $vals[] = $val->value;
                    // find the median
                }
                rsort($vals);
                $middle = (count($vals) / 2);
                $total = $vals[$middle - 1];
                $answers[] = $total;
            } else if ($question->answerType == "MULTIPLE_SELECTION") {
                foreach ($alters as $alter) {
                    $val = Answer::model()->findByAttributes(array("alterId1" => $alter->id, "questionId" => $question->id));
                    $optionIds = explode(',', $val->value);
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId])) {
                            $vals[] = $options[$optionId];
                        }
                    }
                }
                $answers[] = implode(";", $vals);
            }
        }
        if (isset($stats)) {
            $answers[] = $stats->getDegree($alter->id);
            $answers[] = $stats->getBetweenness($alter->id);
            $answers[] = $stats->eigenvectorCentrality($alter->id);
        }
        */
        fputcsv($file, $answers);
        //$text .= implode(',', $answers) . "\n";
        fclose($file);
    }

    public function exportAlterPairData($file, $study, $withAlters = false)
    {
        $alters = Alters::model()->findAll(array('order' => 'id', 'condition' => 'FIND_IN_SET(:x, interviewId)', 'params' => array(':x' => $this->id)));
        //$alterNames = AlterList::model()->findAllByAttributes(array('interviewId'=>$interview->id));

        $i = 1;
        $alterNum = array();
        foreach ($alters as $alter) {
            $alterNum[$alter->id] = $i;
            $i++;
        }
        $alters2 = $alters;

        $criteria = new CDbCriteria;
        $criteria->condition = ("studyId = $study->id and subjectType = 'ALTER_PAIR'");
        $criteria->order = "ordering";
        $alter_pair_questions = Question::model()->findAll($criteria);

        $optionsRaw = QuestionOption::model()->findAllByAttributes(array('studyId' => $study->id));
        // create an array with option ID as key
        $options = array();
        foreach ($optionsRaw as $option) {
            $options[$option->id] = $option->value;
        }

        foreach ($alters as $alter) {
            array_shift($alters2);
            foreach ($alters2 as $alter2) {
                $answers = array();
                $answers[] = $this->id;
                $answers[] = Interview::getEgoId($this->id);
                $answers[] = $alterNum[$alter->id];
                if ($withAlters)
                    $answers[] = str_replace(",", ";", $alter->name);
                $answers[] = $alterNum[$alter2->id];
                if ($withAlters)
                    $answers[] = $alter2->name;
                foreach ($alter_pair_questions as $question) {
                    $criteria = array(
                        "condition" => "interviewId = " . $this->id . " AND questionId = " . $question['id'] . " AND alterId1 = " . $alter->id . " AND alterId2 = " . $alter2->id,
                    );
                    $result = Answer::model()->find($criteria);
                    $answer = $result->value;
                    $skipReason = $result->skipReason;
                    if ($answer != "" && $skipReason == "NONE") {
                        if ($question['answerType'] == "SELECTION") {
                            $answers[] = $options[$answer];
                        } else if ($question['answerType'] == "MULTIPLE_SELECTION") {
                            $optionIds = explode(',', $answer);
                            $list = array();
                            foreach ($optionIds as $optionId) {
                                if (isset($options[$optionId]))
                                    $list[] = $options[$optionId];
                            }
                            if (count($list) == 0)
                                $answers[] = $study->valueNotYetAnswered;
                            else
                                $answers[] = implode('; ', $list);
                        } else {
                            if (!$answer)
                                $answer = $study->valueNotYetAnswered;
                            $answers[] = $answer;
                        }
                    } else if (!$answer && ($skipReason == "DONT_KNOW" || $skipReason == "REFUSE")) {
                        if ($skipReason == "DONT_KNOW")
                            $answers[] = $study->valueDontKnow;
                        else
                            $answers[] = $study->valueRefusal;
                    }
                }
                fputcsv($file, $answers);
            }
        }
    }

    public function exportStudyInterview($filePath, $columns)
    {
        $exclude = array("studyId", "active");
        $interview = $this;
        $answer = Answer::model()->findAllByAttributes(array("interviewId" => $interview->id));
        $answers[$interview->id] = $answer;
        $criteria = array(
            'condition' => "FIND_IN_SET(" . $interview->id . ", interviewId)",
        );
        $alter = Alters::model()->findAll($criteria);
        $alters[$interview->id] = $alter;
        $graph = Graph::model()->findAllByAttributes(array("interviewId" => $interview->id));
        $graphs[$interview->id] = $graph;
        $note = Note::model()->findAllByAttributes(array("interviewId" => $interview->id));
        $notes[$interview->id] = $note;
        $other = array();
        //$other = OtherSpecify::model()->findAllByAttributes(array("interviewId"=>$result->id));
        $others[$interview->id] = $other;
        $x = new XMLWriter();
        $x->openMemory();
        $x->setIndent(true);
        $x->startElement('interview');
        foreach ($columns['interview'] as $attr) {
            if (!in_array($attr, $exclude))
                $x->writeAttribute($attr, $interview->$attr);
        }
        if (isset($answers[$interview->id])) {
            $x->startElement('answers');
            foreach ($answers[$interview->id] as $answer) {
                $x->startElement('answer');
                foreach ($columns['answer'] as $attr) {
                    if (!in_array($attr, $exclude))
                        $x->writeAttribute($attr, $answer->$attr);
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (isset($alters[$interview->id])) {
            $x->startElement('alters');
            foreach ($alters[$interview->id] as $alter) {
                $x->startElement('alter');
                foreach ($columns['alters'] as $attr) {
                    if (!in_array($attr, $exclude))
                        $x->writeAttribute($attr, $alter->$attr);
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (isset($graphs[$interview->id])) {
            $x->startElement('graphs');
            foreach ($graphs[$interview->id] as $graph) {
                $x->startElement('graph');
                foreach ($columns['graphs'] as $attr) {
                    if (!in_array($attr, $exclude))
                        $x->writeAttribute($attr, $graph->$attr);
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (isset($notes[$interview->id])) {
            $x->startElement('notes');
            foreach ($notes[$interview->id] as $note) {
                $x->startElement('note');
                foreach ($columns['notes'] as $attr) {
                    if (!in_array($attr, $exclude))
                        $x->writeAttribute($attr, $note->$attr);
                }
                $x->endElement();
            }
            $x->endElement();
        }
        $x->endElement();
        $output = $x->outputMemory();
        file_put_contents($filePath, $output);
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('active', $this->active);
        $criteria->compare('studyId', $this->studyId);
        $criteria->compare('completed', $this->completed);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
