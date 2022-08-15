<?php

namespace app\models;

use Yii;
use app\helpers\Statistics;
use app\helpers\Tools;

/**
 * This is the model class for table "interview".
 *
 * @property int $id
 * @property int|null $active
 * @property int|null $studyId
 * @property int|null $completed
 * @property int|null $start_date
 * @property int|null $complete_date
 */
class Interview extends \yii\db\ActiveRecord
{
    private $_multiInterviewIds = false;
    private $_answers = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'interview';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'studyId', 'completed', 'start_date', 'complete_date'], 'integer'],
            ['start_date','default', 'value'=>time()],
        ];
    }

    public function multiInterviewIds($interviewId = null)
    {
        if(!$this->_multiInterviewIds){
            $study = Study::findOne($this->studyId);
            $egoAnswer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $study->multiSessionEgoId));
            $interviewIds = array();
            $multiIdQs = $study->multiIdQs();
            if ($study && $study->multiSessionEgoId && $egoAnswer) {
                foreach ($multiIdQs as $q) {
                    $newAnswers = Answer::findAll(array("studyId" => $q->studyId, "questionId" => $q->id));
                    foreach ($newAnswers as $a) {
                        if ($a->value == $egoAnswer->value) {
                            $interviewIds[] = $a->interviewId;
                        }
                    }
                }
            } else {
                $interviewIds = [$this->id];
            }
            $this->_multiInterviewIds = $interviewIds;
        }
        return $this->_multiInterviewIds;
    }

    public function getAnswers($asArray = false)
    {
        if(!$this->_answers || $asArray){
            $interviewIds = $this->multiInterviewIds();
            $results = Answer::findAll(array('interviewId'=>$interviewIds));
            $this->_answers = [];
            foreach ($results as $answer) {
                if ($answer->alterId1 && $answer->alterId2) {
                    $array_id = $answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2;
                } elseif ($answer->alterId1 && ! $answer->alterId2) {
                    $array_id = $answer->questionId . "-" . $answer->alterId1;
                } else {
                    $array_id = $answer->questionId;
                }
                $this->_answers[$array_id] = $answer;
                $answersArray[$array_id] = Tools::mToA($answer);
            }
        }
        if($asArray)
            return $answersArray;
        return $this->_answers;
    }

    public static function getInterviewFromEmail($studyId, $email)
    {
        $answers = Answer::findAll(array('questionType' => "EGO_ID", "studyId" => $studyId));
        $interview = false;
        foreach ($answers as $answer) {
            if ($answer->value == $email) {
                $interview = Interview::findOne($answer->interviewId);
            }
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
        $answers = Answer::findAll(array(
            'questionType' => 'EGO_ID',
            'studyId' => $studyId
        ));

        foreach ($answers as $answer) {
            if ($answer->value == $primekey) {
                return Interview::findOne($answer->interviewId);
            }
        }

        $egoQs = Question::find()
        ->where(new \yii\db\Expression("studyId = $studyId and subjectType = 'EGO_ID' AND answerType != 'RANDOM_NUMBER'"))
        ->orderBy(["ordering"=>"ASC"])->all();
        $study = Study::findOne($studyId);

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
                $egoIdQ->value = strval($prefill[$egoQ->title]);
            } else {
                $egoIdQ->skipReason = "DONT_KNOW";
                $egoIdQ->value = $study->valueDontKnow;
            }
            if (!$egoIdQ->save()) {
                echo $egoQ->title.':'.$egoIdQ->value;
                print_r($egoIdQ->errors);
                die();
            }
        }

        $randoms = Question::findAll(array("answerType" => "RANDOM_NUMBER", "studyId" => $studyId));
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

        if (count($questions) > 0) {
            $interview->fillQs($questions, $interview->id, $studyId);
        }

        return $interview;
    }

    public static function fillQs($qs, $interviewId, $studyId)
    {
        foreach ($qs as $title => $value) {
            $question = Question::findOne(array("title" => $title, "studyId" => $studyId));
            $answer = Answer::findOne(array("interviewId" => $interviewId, "questionId" => $question->id));
            if ($answer) {
                continue;
            }
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
                $study = Study::findOne($studyId);
                $answer->value = $study->valueDontKnow;
            }
            $answer->save();
        }
    }

    public function getEgoId()
    {
        $egoIdString = [];
        $questions = Question::find()->where(array('studyId'=>$this->studyId,'subjectType' => "EGO_ID"))->orderBy(["ordering"=>"ASC"])->all();
        $ego_id_questions = [];
        $options = [];
        foreach ($questions as $question) {
            if($question->answerType == "STORED_VALUE" || $question->answerType == "RANDOM_NUMBER")
                continue;
            $ego_id_questions[$question->id] = $question;
            if ($question->answerType == "MULTIPLE_SELECTION") {
                $result = QuestionOption::find()->where(["questionId"=>$question->id])->orderBy(["ordering"=>"ASC"])->all();
                foreach ($result as $option) {
                    $options[$option->id] = $option->name;
                }
            }
        }
        $answers = Answer::find()->where(array("interviewId" => $this->id, 'questionType' => "EGO_ID"))->andWhere(['!=', 'answerType', 'STORED_VALUE'])->andWhere(['!=', 'answerType', 'RANDOM_NUMBER'])->all();
        foreach ($answers as $answer) {
            if (isset($ego_id_questions[$answer->questionId]) && $ego_id_questions[$answer->questionId]->answerType == "MULTIPLE_SELECTION" && isset($options[$answer->value])) {
                $egoIdString[] = $options[$answer->value];
            } else {
                $egoIdString[] = $answer->value;
            }
        }
        return implode("_", $egoIdString);
    }

    public function exportEgoAlterData($file = null, $withAlters = false, $multiSession = true)
    {
        $all_questions = Question::find()->where(["studyId"=>$this->studyId])->orderBy(["ordering"=>"ASC"])->all();
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach ($all_questions as $question) {
            if ($question->subjectType == "EGO_ID") {
                $ego_id_questions[] = $question;
            }
            if ($question->subjectType == "EGO") {
                $ego_questions[] = $question;
            }
            if ($question->subjectType == "ALTER") {
                $alter_questions[] = $question;
            }
            if ($question->subjectType == "NETWORK") {
                $network_questions[] = $question;
            }
            if ($question->subjectType == "NAME_GENERATOR") {
                $name_gen_questions[] = $question;
            }
            if ($question->subjectType == "PREVIOUS_ALTER") {
                $previous_questions[] = $question;
            }
        }

        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $this->id .", interviewId)"))
        ->all();

        if (!$alters) {
            $alters = array('0' => new Alters);
        } else {
            if (isset($_POST['expressionId']) && $_POST['expressionId']) {
                $stats = new Statistics;
                $stats->initComponents($this->id, $_POST['expressionId']);
            }
        }

        $study = Study::findOne($this->studyId);
        $multiQs = false;
        if (isset($study->multiSessionEgoId) && $study->multiSessionEgoId) {
            $multiQs = $study->multiIdQs();
        }

        if ($multiSession && $multiQs) {
            $interviewIds = $this->multiInterviewIds();
            $prevIds = array();
            if (is_array($interviewIds)) {
                $prevIds = array_diff($interviewIds, array($this->id));
            }
            foreach ($prevIds as $i_id) {
                $results = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $i_id .", interviewId)"))
                ->all();
                foreach ($results as $result) {
                    $aInts = explode(",", $result->interviewId);
                    if (!in_array($this->id, $aInts)) {
                        $alters[] = $result;
                    }
                }
            }
        }

        $text = "";
        $count = 1;

        $matchIntId = "";
        $matchUser = "";
        $matchAtAll = MatchedAlters::findOne(["studyId"=>$this->studyId]);
        if ($matchAtAll) {
            $match = MatchedAlters::find()
            ->where(new \yii\db\Expression("interviewId1 = $this->id OR interviewId2 = $this->id"))
            ->one();
            if ($match) {
                if ($this->id == $match->interviewId1) {
                    $matchInt = Interview::findOne($match->interviewId2);
                } else {
                    $matchInt = Interview::findOne($match->interviewId1);
                }
                $matchIntId = $match->getMatchId();
                
                $matchU = User::findOne($match->userId);
                $matchUser = $matchU->name;
            }
        }
        $study = Study::findOne($this->studyId);
        $optionsRaw = QuestionOption::findAll(array("studyId" => $study->id));

        // create an array with option ID as key
        $options = array();
        $optionLabels = array();
        foreach ($optionsRaw as $option) {
            $options[$option->id] = $option->value;
            $optionLabels[$option->id] = $option->name;
        }
        foreach ($alters as $alter) {
            $answers = array();
            $answers[] = $this->id;
            $ego_ids = array();
            $ego_id_string = array();

            foreach ($ego_id_questions as $question) {

                #OK FOR SQL INJECTION
                $result = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
                if (!$result) {
                    $answer = $study->valueNotYetAnswered;
                } else {
                    $answer = $result->value;
                }

                if ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer);
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId])) {
                            $ego_ids[] = $options[$optionId];
                            if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                                $ego_id_string[] = $optionLabels[$optionId];
                            }
                        } else {
                            $ego_ids[] = "MISSING_OPTION ($optionId)";
                            if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                                $ego_id_string[] = "MISSING_OPTION ($optionId)";
                            }
                        }
                    }
                    if (!$optionIds) {
                        $ego_ids[] = "";
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                            $ego_id_string[] = "";
                        }
                    }
                } else {
                    $ego_ids[] = str_replace(',', '', $answer);
                    if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                        $ego_id_string[] = str_replace(',', '', $answer);
                    }
                }
            }
            $answers[] = implode("_", $ego_id_string);
            $answers[] = date("Y-m-d H:i:s", $this->start_date);
            if($this->completed == -1)
                $answers[] = date("Y-m-d H:i:s", $this->complete_date);
            else
                $answers[] = "";
            foreach ($ego_ids as $eid) {
                $answers[] = $eid;
            }
            foreach ($ego_questions as $question) {
                $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
                if (!$answer) {
                    $answers[] = $study->valueNotYetAnswered;
                    continue;
                }

                if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                    if ($question->answerType == "SELECTION") {
                        if (isset($options[$answer->value])) {
                            $answers[] = $options[$answer->value];
                        } else {
                            $answers[] = "";
                        }
                    } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                        $optionIds = explode(',', $answer->value);
                        $list = array();
                        foreach ($optionIds as $optionId) {
                            if (isset($options[$optionId])) {
                                $list[] = $options[$optionId];
                            }
                        }
                        $answers[] = implode('; ', $list);
                    } elseif ($question->answerType == "TIME_SPAN") {
                        if (!strstr($answer->value, ";")) {
                            $times = array();
                            if (preg_match("/(\d*)\sYEARS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sMONTHS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sWEEKS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sDAYS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sHOURS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sMINUTES/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            $answer->value = implode("; ", $times);
                        }
                        $answers[] = $answer->value;
                    } else {
                        $answer->value = preg_replace('/amp;/', "", $answer->value);
                        $answers[] = htmlspecialchars_decode($answer->value, ENT_QUOTES);
                    }
                } elseif ($answer->skipReason == "DONT_KNOW") {
                    $answers[] = $study->valueDontKnow;
                } elseif ($answer->skipReason == "REFUSE") {
                    $answers[] = $study->valueRefusal;
                } elseif ($answer->value == $study->valueLogicalSkip) {
                    $answers[] = $study->valueLogicalSkip;
                } else {
                    $answers[] = "";
                }
            }

            foreach ($network_questions as $question) {
                $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
                if (!$answer) {
                    $answers[] = $study->valueNotYetAnswered;
                    continue;
                }
                if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                    if ($question->answerType == "SELECTION") {
                        if (isset($options[$answer])) {
                            $answers[] = $options[$answer];
                        } else {
                            $answers[] = "";
                        }
                    } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                        $optionIds = explode(',', $answer->value);
                        $list = array();
                        foreach ($optionIds as $optionId) {
                            if (isset($options[$optionId])) {
                                $list[] = $options[$optionId];
                            }
                        }
                        $answers[] = implode('; ', $list);
                    } else {
                        $answer->value = preg_replace('/amp;/', "", $answer->value);
                        $answers[] = htmlspecialchars_decode($answer->value);
                    }
                } elseif ($answer->skipReason == "DONT_KNOW") {
                    $answers[] = $study->valueDontKnow;
                } elseif ($answer->skipReason == "REFUSE") {
                    $answers[] = $study->valueRefusal;
                } elseif ($answer->value == $study->valueLogicalSkip) {
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
                    $match = MatchedAlters::find()
                    ->where(new \yii\db\Expression("alterId1 = $alter->id OR alterId2 = $alter->id"))
                    ->one();
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
                    if ($withAlters) {
                        $answers[] = $alter->name;
                    }
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
                foreach ($previous_questions as $question) {
                    $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id, "alterId1" => $alter->id));
                    if (!$answer) {
                        $answers[] = $study->valueNotYetAnswered;
                        continue;
                    }
                    if ($answer->value != "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                        if ($question->answerType == "SELECTION") {
                            $answers[] = $options[$answer->value];
                        } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                            $optionIds = explode(',', $answer->value);
                            $list = array();
                            foreach ($optionIds as $optionId) {
                                if (isset($options[$optionId])) {
                                    $list[] = $options[$optionId];
                                }
                            }
                            if (count($list) == 0) {
                                $answers[] = $study->valueNotYetAnswered;
                            } else {
                                $answers[] = implode('; ', $list);
                            }
                        } else {
                            $answers[] =  htmlspecialchars_decode($answer->value);
                        }
                    } elseif ($answer->skipReason == "DONT_KNOW") {
                        $answers[] = $study->valueDontKnow;
                    } elseif ($answer->skipReason == "REFUSE") {
                        $answers[] = $study->valueRefusal;
                    } elseif ($answer->value == $study->valueLogicalSkip) {
                        $answers[] = $study->valueLogicalSkip;
                    } else {
                        $answers[] = "";
                    }
                }
                foreach ($alter_questions as $question) {
                    $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id, "alterId1" => $alter->id));
                    if (!$answer) {
                        $answers[] = $study->valueNotYetAnswered;
                        continue;
                    }
                    if ($answer->value != "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                        if ($question->answerType == "SELECTION") {
                            $answers[] = $options[$answer->value];
                        } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                            $optionIds = explode(',', $answer->value);
                            $list = array();
                            foreach ($optionIds as $optionId) {
                                if (isset($options[$optionId])) {
                                    $list[] = $options[$optionId];
                                }
                            }
                            if (count($list) == 0) {
                                $answers[] = $study->valueNotYetAnswered;
                            } else {
                                $answers[] = implode('; ', $list);
                            }
                        } else {
                            $answers[] =  htmlspecialchars_decode($answer->value);
                        }
                    } elseif ($answer->skipReason == "DONT_KNOW") {
                        $answers[] = $study->valueDontKnow;
                    } elseif ($answer->skipReason == "REFUSE") {
                        $answers[] = $study->valueRefusal;
                    } elseif ($answer->value == $study->valueLogicalSkip) {
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


            if ($multiSession && $multiQs) {
                $answers[] = $alter->id;
                $aInts = explode(",", $alter->interviewId);
                $aStudies = array();
                foreach ($aInts as $aInt) {
                    $int = Interview::findOne($aInt);
                    if ($int) {
                        $aStudies[] = $int->studyId;
                    }
                }
                foreach ($multiQs as $q) {
                    $answers[] = intval(in_array($q->studyId, $aStudies));
                }
            }
            if ($file === null) {
                $all_answers[] = $answers;
            } else {
                fputcsv($file, $answers);
            }
            $count++;
        }
        if ($file === null) {
            return $all_answers;
        } else {
            fclose($file);
            die();
        }
    }

    public function exportEgoAlterDataJSON($file = null, $noAlters = false)
    {
        $all_questions = Question::find()->where(["studyId"=>$this->studyId])->orderBy(["ordering"=>"ASC"])->all();
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach ($all_questions as $question) {
            if ($question->subjectType == "EGO_ID") {
                $ego_id_questions[] = $question;
            }
            if ($question->subjectType == "EGO") {
                $ego_questions[] = $question;
            }
            if ($question->subjectType == "ALTER") {
                $alter_questions[] = $question;
            }
            if ($question->subjectType == "NETWORK") {
                $network_questions[] = $question;
            }
            if ($question->subjectType == "NAME_GENERATOR") {
                $name_gen_questions[] = $question;
            }
            if ($question->subjectType == "PREVIOUS_ALTER") {
                $previous_questions[] = $question;
            }
        }

        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $this->id .", interviewId)"))
        ->all();

        if (!$alters || $noAlters === true) {
            $alters = array('0'=>array('id'=>null));
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
        $matchAtAll = MatchedAlters::findOne(["studyId"=>$this->studyId]);
        if ($matchAtAll) {
            $match = MatchedAlters::find()->where(["interviewId1" => $this->id])->orWhere(["interviewId2" => $this->id])->one();
            if ($match) {
                if ($this->id == $match->interviewId1) {
                    $matchInt = Interview::findOne($match->interviewId2);
                } else {
                    $matchInt = Interview::findOne($match->interviewId1);
                }
                $matchIntId = $match->getMatchId();
                $matchU = User::findOne($match->userId);
                $matchUser = $matchU->name;
            }
        }
        $all_answers = array();
        foreach ($alters as $alter) {
            $answers = array();
            $answers['Interview ID'] = $this->id;
            $ego_ids = array();
            $ego_id_string = array();
            $study = Study::findOne($this->studyId);
            $optionsRaw = QuestionOption::findAll(array("studyId"=>$study->id));

            // create an array with option ID as key
            $options = array();
            $optionLabels = array();
            foreach ($optionsRaw as $option) {
                $options[$option->id] = $option->value;
                $optionLabels[$option->id] = $option->name;
            }
            foreach ($ego_id_questions as $question) {
                $result = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
                $answer = $result->value;

                if ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer);
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId])) {
                            $ego_ids[$question->title] = $options[$optionId];
                            if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                                $ego_id_string[] = $optionLabels[$optionId];
                            }
                        } else {
                            $ego_ids[$question->title] = "MISSING_OPTION ($optionId)";
                            if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                                $ego_id_string[] = "MISSING_OPTION ($optionId)";
                            }
                        }
                    }
                    if (!$optionIds) {
                        $ego_ids[$question->title] = "";
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                            $ego_id_string[] = "";
                        }
                    }
                } else {
                    $ego_ids[$question->title] = str_replace(',', '', $answer);
                    if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                        $ego_id_string[] = str_replace(',', '', $answer);
                    }
                }
            }
            $answers["EgoID"] = implode("_", $ego_id_string);
            $answers['Start Time'] = date("Y-m-d H:i:s", $this->start_date);
            if($this->completed == -1)
                $answers['End Time'] = date("Y-m-d H:i:s", $this->complete_date);
            else
                $answers['End Time'] = "";
            foreach ($ego_ids as $title => $eid) {
                $answers[$title] = $eid;
            }
            foreach ($ego_questions as $question) {
                $answer = Answer::findOne(array("interviewId"=>$this->id, "questionId"=>$question->id));
                if (!$answer) {
                    $answers[$question->title] = $study->valueNotYetAnswered;
                    continue;
                }

                if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                    if ($question->answerType == "SELECTION") {
                        if (isset($options[$answer->value])) {
                            $answers[$question->title] = $options[$answer->value];
                        } else {
                            $answers[$question->title] = "";
                        }
                    } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                        $optionIds = explode(',', $answer->value);
                        $list = array();
                        foreach ($optionIds as $optionId) {
                            if (isset($options[$optionId])) {
                                $list[] = $options[$optionId];
                            }
                        }
                        $answers[$question->title] = implode('; ', $list);
                    } elseif ($question->answerType == "TIME_SPAN") {
                        if (!strstr($answer->value, ";")) {
                            $times = array();
                            if (preg_match("/(\d*)\sYEARS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sMONTHS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sWEEKS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sDAYS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sHOURS/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            if (preg_match("/(\d*)\sMINUTES/i", $answer->value, $test)) {
                                $times[] = $test[0];
                            }
                            $answer->value = implode("; ", $times);
                        }
                        $answers[$question->title] = $answer->value;
                    } else {
                        $answers[$question->title] = $answer->value;
                    }
                } elseif ($answer->skipReason == "DONT_KNOW") {
                    $answers[$question->title] = $study->valueDontKnow;
                } elseif ($answer->skipReason == "REFUSE") {
                    $answers[$question->title] = $study->valueRefusal;
                } elseif ($answer->value == $study->valueLogicalSkip) {
                    $answers[$question->title] = $study->valueLogicalSkip;
                } else {
                    $answers[$question->title] = "";
                }
            }

            foreach ($network_questions as $question) {
                $answer = Answer::findOne(array("interviewId"=>$this->id, "questionId"=>$question->id));
                if (!$answer) {
                    $answers[$question->title] = $study->valueNotYetAnswered;
                    continue;
                }
                if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                    if ($question->answerType == "SELECTION") {
                        if (isset($options[$answer])) {
                            $answers[$question->title] = $options[$answer];
                        } else {
                            $answers[$question->title] = "";
                        }
                    } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                        $optionIds = explode(',', $answer->value);
                        $list = array();
                        foreach ($optionIds as $optionId) {
                            if (isset($options[$optionId])) {
                                $list[] = $options[$optionId];
                            }
                        }
                        $answers[$question->title] = implode('; ', $list);
                    } else {
                        $answers[$question->title] = $answer->value;
                    }
                } elseif ($answer->skipReason == "DONT_KNOW") {
                    $answers[$question->title] = $study->valueDontKnow;
                } elseif ($answer->skipReason == "REFUSE") {
                    $answers[$question->title] = $study->valueRefusal;
                } elseif ($answer->value == $study->valueLogicalSkip) {
                    $answers[$question->title] = $study->valueLogicalSkip;
                } else {
                    $answers[$question->title] = "";
                }
            }

            if (isset($stats)) {
                $answers["Density"] = $stats->getDensity();
                $answers["Max Degree Value"] = $stats->maxDegree();
                $answers["Max Betweenness Value"] = $stats->maxBetweenness();
                $answers["Max Eigenvector Value"] = $stats->maxEigenvector();
                $answers["Degree Centralization"] = $stats->degreeCentralization();
                $answers["Betweenness Centralization"] = $stats->betweennessCentralization();
                $answers["Components"] = count($stats->components);
                $answers["Dyads"] = count($stats->dyads);
                $answers["Isolates"] = count($stats->isolates);
            }

            if (isset($alter->id)) {
                if ($matchAtAll) {
                    $matchId = "";
                    $matchName = "";
                    $match = MatchedAlters::find()->where(["alterId1"=>$alter->id])->orWhere(["alterId2"=>$alter->id])->one();
                    if ($match) {
                        $matchId = $match->id;
                        $matchName = $match->matchedName;
                    }
                    
                    
                    $answers["Dyad Match ID"] = $matchIntId;
                    $answers["Match User"] = $matchUser;
                    $answers["Alter Number"] = $count;
                    $answers["Alter Name"] = $alter->name;
                    $answers["Matched Alter Name"] = $matchName;
                    $answers["Alter Pair ID"] = $matchId;
                } else {
                    $answers["Alter Number"] = $count;
                    $answers["Alter Name"] = $alter->name;
                }
                foreach ($name_gen_questions as $question) {
                    if (count($name_gen_questions) == 1) {
                        $answers[$question->title]  = 1;
                        continue;
                    }
                    $nameGenQIds = explode(",", $alter->nameGenQIds);
                    if (in_array($question->id, $nameGenQIds)) {
                        $answers[$question->title]  = 1;
                    } else {
                        $answers[$question->title]  = 0;
                    }
                }
                foreach ($alter_questions as $question) {
                    $answer = Answer::findOne(array("interviewId"=>$this->id, "questionId"=>$question->id, "alterId1"=>$alter->id));
                    if (!$answer) {
                        $answers[$question->title] = $study->valueNotYetAnswered;
                        continue;
                    }
                    if ($answer->value != "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                        if ($question->answerType == "SELECTION") {
                            $answers[$question->title] = $options[$answer->value];
                        } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                            $optionIds = explode(',', $answer->value);
                            $list = array();
                            foreach ($optionIds as $optionId) {
                                if (isset($options[$optionId])) {
                                    $list[] = $options[$optionId];
                                }
                            }
                            if (count($list) == 0) {
                                $answers[$question->title] = $study->valueNotYetAnswered;
                            } else {
                                $answers[$question->title] = implode('; ', $list);
                            }
                        } else {
                            $answers[$question->title] = $answer->value;
                        }
                    } elseif ($answer->skipReason == "DONT_KNOW") {
                        $answers[$question->title] = $study->valueDontKnow;
                    } elseif ($answer->skipReason == "REFUSE") {
                        $answers[$question->title] = $study->valueRefusal;
                    } elseif ($answer->value == $study->valueLogicalSkip) {
                        $answers[$question->title] = $study->valueLogicalSkip;
                    } else {
                        $answers[$question->title] = "";
                    }
                }
            } else {
                $answers['Alter Number'] = 0;
                $answers['Alter Name'] = "";
                
                foreach ($alter_questions as $question) {
                    $answers[$question->title] = $study->valueNotYetAnswered;
                }
            }

            if (isset($stats)) {
                $answers["Degree"] = $stats->getDegree($alter->id);
                $answers["Betweenness"] = $stats->getBetweenness($alter->id);
                $answers["Eigenvector"] = $stats->eigenvectorCentrality($alter->id);
            }
            if ($file === null) {
                $all_answers[] = $answers;
            } else {
                fputcsv($file, $answers);
            }
            //$text .= implode(',', $answers) . "\n";
            $count++;
        }

        if ($file === null) {
            return $all_answers;
        } else {
            die();
            fclose($file);
        }
        //return $text;
    }

    public function exportEgoLevel($file)
    {
        $all_questions = Question::find()->where(["studyId"=>$this->studyId])->orderBy(["ordering"=>"ASC"])->all();
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach ($all_questions as $question) {
            if ($question->subjectType == "EGO_ID") {
                $ego_id_questions[] = $question;
            }
            if ($question->subjectType == "EGO") {
                $ego_questions[] = $question;
            }
            if ($question->subjectType == "ALTER") {
                $alter_questions[] = $question;
            }
            if ($question->subjectType == "NETWORK") {
                $network_questions[] = $question;
            }
            if ($question->subjectType == "NAME_GENERATOR") {
                $name_gen_questions[] = $question;
            }
            if ($question->subjectType == "PREVIOUS_ALTER") {
                $previous_questions[] = $question;
            }
        }

        $text = "";
        $count = 1;

        $matchIntId = "";
        $matchUser = "";
        $criteria = array(
            'condition' => "studyId = $this->studyId",
        );

        $answers = array();
        $answers[] = $this->id;

        $study = Study::findOne($this->studyId);
        $optionsRaw = QuestionOption::findAll(array("studyId" => $study->id));

        // create an array with option ID as key
        $options = array();
        $optionLabels = array();
        foreach ($optionsRaw as $option) {
            $options[$option->id] = $option->value;
            $optionLabels[$option->id] = $option->name;
        }

        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $this->id .", interviewId)"))
        ->all();

        foreach ($ego_id_questions as $question) {
            $result = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
            if (!$result) {
                $answer = $study->valueNotYetAnswered;
            } else {
                $answer = $result->value;
            }

            if ($question->answerType == "MULTIPLE_SELECTION") {
                $optionIds = explode(',', $answer);
                foreach ($optionIds as $optionId) {
                    if (isset($options[$optionId])) {
                        $ego_ids[$question->title] = $options[$optionId];
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                            $ego_id_string[] = $optionLabels[$optionId];
                        }
                    } else {
                        $ego_ids[$question->title] = "MISSING_OPTION ($optionId)";
                        if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                            $ego_id_string[] = "MISSING_OPTION ($optionId)";
                        }
                    }
                }
                if (!$optionIds) {
                    $ego_ids[$question->title] = "";
                    if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                        $ego_id_string[] = "";
                    }
                }
            } else {
                $ego_ids[$question->title] = str_replace(',', '', $answer);
                if ($question->answerType != "STORED_VALUE" && $question->answerType != "RANDOM_NUMBER") {
                    $ego_id_string[] = str_replace(',', '', $answer);
                }
            }
        }
        $answers[] = implode("_", $ego_id_string);
        $answers[] = date("Y-m-d H:i:s", $this->start_date);
        if($this->completed == -1)
            $answers[] = date("Y-m-d H:i:s", $this->complete_date);
        else
            $answers[] = "";

        foreach ($ego_ids as $eid) {
            $answers[] = $eid;
        }
        foreach ($ego_questions as $question) {
            $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
            if (!$answer) {
                $answers[] = $study->valueNotYetAnswered;
                continue;
            }

            if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                if ($question->answerType == "SELECTION") {
                    if (isset($options[$answer->value])) {
                        $answers[] = $options[$answer->value];
                    } else {
                        $answers[] = "";
                    }
                } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer->value);
                    $list = array();
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId])) {
                            $list[] = $options[$optionId];
                        }
                    }
                    $answers[] = implode('; ', $list);
                } elseif ($question->answerType == "TIME_SPAN") {
                    if (!strstr($answer->value, ";")) {
                        $times = array();
                        if (preg_match("/(\d*)\sYEARS/i", $answer->value, $test)) {
                            $times[] = $test[0];
                        }
                        if (preg_match("/(\d*)\sMONTHS/i", $answer->value, $test)) {
                            $times[] = $test[0];
                        }
                        if (preg_match("/(\d*)\sWEEKS/i", $answer->value, $test)) {
                            $times[] = $test[0];
                        }
                        if (preg_match("/(\d*)\sDAYS/i", $answer->value, $test)) {
                            $times[] = $test[0];
                        }
                        if (preg_match("/(\d*)\sHOURS/i", $answer->value, $test)) {
                            $times[] = $test[0];
                        }
                        if (preg_match("/(\d*)\sMINUTES/i", $answer->value, $test)) {
                            $times[] = $test[0];
                        }
                        $answer->value = implode("; ", $times);
                    }
                    $answers[] = $answer->value;
                } else {
                    $answer->value = str_replace('amp;', "", $answer->value);
                    $answers[] = htmlspecialchars_decode($answer->value);
                }
            } elseif ($answer->skipReason == "DONT_KNOW") {
                $answers[] = $study->valueDontKnow;
            } elseif ($answer->skipReason == "REFUSE") {
                $answers[] = $study->valueRefusal;
            } elseif ($answer->value == $study->valueLogicalSkip) {
                $answers[] = $study->valueLogicalSkip;
            } else {
                $answers[] = "";
            }
        }

        foreach ($network_questions as $question) {
            $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
            if (!$answer) {
                $answers[] = $study->valueNotYetAnswered;
                continue;
            }
            if ($answer->value !== "" && $answer->skipReason == "NONE" && $answer->value != $study->valueLogicalSkip) {
                if ($question->answerType == "SELECTION") {
                    if (isset($options[$answer])) {
                        $answers[] = $options[$answer];
                    } else {
                        $answers[] = "";
                    }
                } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                    $optionIds = explode(',', $answer->value);
                    $list = array();
                    foreach ($optionIds as $optionId) {
                        if (isset($options[$optionId])) {
                            $list[] = $options[$optionId];
                        }
                    }
                    $answers[] = implode('; ', $list);
                } else {
                    $answer->value = str_replace('amp;', "", $answer->value);
                    $answers[] = htmlspecialchars_decode($answer->value);
                }
            } elseif ($answer->skipReason == "DONT_KNOW") {
                $answers[] = $study->valueDontKnow;
            } elseif ($answer->skipReason == "REFUSE") {
                $answers[] = $study->valueRefusal;
            } elseif ($answer->value == $study->valueLogicalSkip) {
                $answers[] = $study->valueLogicalSkip;
            } else {
                $answers[] = "";
            }
        }
        //print_r($answers);
        fputcsv($file, $answers);
        fclose($file);
    }

    public function exportAlterPairData($file, $study, $withAlters = false)
    {
        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $this->id .", interviewId)"))
        ->all();
        $i = 1;
        $alterNum = array();
        foreach ($alters as $alter) {
            $alterNum[$alter->id] = $i;
            $i++;
        }
        $alters2 = $alters;

        $alter_pair_questions = Question::findAll(["studyId"=>$study->id, "subjectType"=>"ALTER_PAIR"]);

        $optionsRaw = QuestionOption::findAll(array('studyId' => $study->id));
        // create an array with option ID as key
        $options = array();
        foreach ($optionsRaw as $option) {
            $options[$option->id] = $option->value;
        }
        $result = Answer::findAll([
            "interviewId"=>$this->id,
            "questionType"=>"ALTER_PAIR",
        ]);
        $ap_answers = array();
        foreach ($result as $answer) {
            $ap_answers[$answer->questionId][$answer->alterId1][$answer->alterId2] = $answer;;
        }
        $ego_id = Interview::getEgoId($this->id);

        foreach ($alters as $alter) {
            array_shift($alters2);
            foreach ($alters2 as $alter2) {
                $answers = array();
                $answers[] = $this->id;
                $answers[] = $ego_id;
                $answers[] = $alterNum[$alter->id];
                if ($withAlters) {
                    $answers[] = str_replace(",", ";", $alter->name);
                }
                $answers[] = $alterNum[$alter2->id];
                if ($withAlters) {
                    $answers[] = $alter2->name;
                }
                foreach ($alter_pair_questions as $question) {
                    if(!isset($ap_answers[$question->id][$alter->id][$alter2->id]))
                        continue;
                    $result = $ap_answers[$question->id][$alter->id][$alter2->id];
                    
                    $answer = $result->value;
                    $skipReason = $result->skipReason;
                    if ($answer != "" && $skipReason == "NONE") {
                        if ($question->answerType == "SELECTION") {
                            $answers[] = $options[$answer];
                        } elseif ($question->answerType == "MULTIPLE_SELECTION") {
                            $optionIds = explode(',', $answer);
                            $list = array();
                            foreach ($optionIds as $optionId) {
                                if (isset($options[$optionId])) {
                                    $list[] = $options[$optionId];
                                }
                            }
                            if (count($list) == 0) {
                                $answers[] = $study->valueNotYetAnswered;
                            } else {
                                $answers[] = implode('; ', $list);
                            }
                        } else {
                            if (!$answer) {
                                $answer = $study->valueNotYetAnswered;
                            }
                            $answers[] = $answer;
                        }
                    } elseif (!$answer && ($skipReason == "DONT_KNOW" || $skipReason == "REFUSE")) {
                        if ($skipReason == "DONT_KNOW") {
                            $answers[] = $study->valueDontKnow;
                        } else {
                            $answers[] = $study->valueRefusal;
                        }
                    }
                }
                fputcsv($file, $answers);
            }
        }
    }

    public function exportOtherData($file, $study)
    {
        $options = QuestionOption::findAll(array("otherSpecify"=>true, "studyId"=>$study->id));
        if (!$options) {
            $allOptions = QuestionOption::findAll(array("studyId"=>$study->id));
            foreach ($allOptions as $option) {
                if (preg_match("/OTHER \(*SPECIFY\)*/i", $option->name)) {
                    $options[] = $option;
                }
            }
        }
        if (!$options) {
            die("no other specified data to export");
        }
        foreach ($options as $option) {
            $other_options[$option->id] = $option;
            if (!isset($other_qs[$option->questionId])) {
                $other_qs[$option->questionId] = Question::findOne($option->questionId);
            }
        }

 
        $answers = array();
        $answerList = Answer::findAll(array('interviewId'=>$this->id));
        foreach ($answerList as $a) {
            if ($a->alterId1 && $a->alterId2) {
                $answers[$a->questionId . "-" . $a->alterId1 . "and" . $a->alterId2] = $a;
            } elseif ($a->alterId1 && ! $a->alterId2) {
                $answers[$a->questionId . "-" . $a->alterId1] = $a;
            } else {
                $answers[$a->questionId] = $a;
            }
        }
        foreach ($other_qs as $question) {
            if ($question->subjectType == "ALTER") {
                $alters = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $this->id .", interviewId)"))
                ->all();
                foreach ($alters as $alter) {
                    $answerArray = array();
                    $otherSpecifies = array();
                    if (!isset($answers[$question->id . "-" . $alter->id])) {
                        continue;
                    }
                    $response = $answers[$question->id . "-" . $alter->id]->otherSpecifyText;
                    if (!$response) {
                        continue;
                    }
                    foreach (preg_split('/;;/', $response) as $otherSpecify) {
                        if (strstr($otherSpecify, ':')) {
                            list($optionId, $val) = preg_split('/:/', $otherSpecify);
                            $val = preg_replace("/amp;/", "", $val);
                            $otherSpecifies[$optionId] = htmlspecialchars_decode($val, ENT_QUOTES);
                        }
                    }
                    $optionIds = explode(",", $answers[$question->id . "-" . $alter->id]->value);
                    foreach ($optionIds as $optionId) {
                        if (isset($otherSpecifies[$optionId])) {
                            if (count($optionIds) == 1 && preg_match("/OTHER \(*SPECIFY\)*/i", $other_options[$optionId]->name)) {
                                $answerArray["OTHER SPECIFY"] = $otherSpecifies[$optionId];
                            } else {
                                $answerArray[$other_options[$optionId]->name] = $otherSpecifies[$optionId];
                            }
                        } else {
                            if (isset($other_options[$optionId])) {
                                $answerArray[$other_options[$optionId]->name] = "";
                            } else {
                                $other_options["OTHER SPECIFY"]  = "ERROR:$question->id:$optionId";
                            }
                        }
                    }

                    foreach ($answerArray as $i=>$a) {
                        $answer = array();
                        $answer[] = $this->id;
                        $answer[] = Interview::getEgoId($this->id);
                        $answer[] = $question->title;
                        $answer[] = $alter->name;
                        $answer[] = $i;
                        $answer[] = $a;
                        fputcsv($file, $answer);
                    }
                }
            } else {
                $answerArray = array();
                $otherSpecifies = array();
                if (!isset($answers[$question->id])) {
                    continue;
                }
                $response = $answers[$question->id]->otherSpecifyText;
                if (!$response) {
                    continue;
                }
                foreach (preg_split('/;;/', $response) as $otherSpecify) {
                    if (strstr($otherSpecify, ':')) {
                        list($optionId, $val) = preg_split('/:/', $otherSpecify);
                        $val = preg_replace("/amp;/", "", $val);
                        $otherSpecifies[$optionId] = htmlspecialchars_decode($val, ENT_QUOTES);
                    }
                }
                $optionIds = explode(",", $answers[$question->id]->value);
                foreach ($optionIds as $optionId) {
                    if (isset($other_options[$optionId])) {
                        if (isset($otherSpecifies[$optionId])) {
                            if (count($optionIds) == 1 && preg_match("/OTHER \(*SPECIFY\)*/i", $other_options[$optionId]->name)) {
                                $answerArray["OTHER SPECIFY"] = $otherSpecifies[$optionId];
                            } else {
                                $answerArray[$other_options[$optionId]->name] = $otherSpecifies[$optionId];
                            }
                        } else {
                            $answerArray[$other_options[$optionId]->name] = "";
                        }
                    }
                }

                foreach ($answerArray as $i=>$a) {
                    $answer = array();
                    $answer[] = $this->id;
                    $answer[] = Interview::getEgoId($this->id);
                    $answer[] = $question->title;
                    $answer[] = "";
                    $answer[] = $i;
                    $answer[] = $a;
                    fputcsv($file, $answer);
                }
            }
        }
    }

    public function exportCompletionData($file)
    {
        $row = array();
        $row[] = $this->id;
        $row[] = Interview::getEgoId($this->id);
        $all_questions = Question::find()->where(["studyId"=>$this->studyId])->orderBy(["ordering"=>"ASC"])->all();
        foreach ($all_questions as $question){
            $answer = Answer::findOne(["interviewId"=>$this->id, "questionId"=>$question->id]);
            if ($answer) {
                if($answer->timestamp)
                    $row[] = date("Y-m-d H:i:s", $answer->timestamp);
                else
                    $row[] = "";
            }else{
                $row[] = "";
            }
        }
        fputcsv($file, $row);
    }

    public function exportStudyInterview($filePath, $columns)
    {
        $exclude = array("studyId", "active");
        $interview = $this;
        $answer = Answer::findAll(array("interviewId" => $interview->id));
        $answers[$interview->id] = $answer;
        $alter = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $interview->id .", interviewId)"))
        ->orderBy(['ordering'=>'ASC'])
        ->all();
        $alters[$interview->id] = $alter;
        $graph = Graph::findAll(array("interviewId" => $interview->id));
        $graphs[$interview->id] = $graph;
        $note = Note::findAll(array("interviewId" => $interview->id));
        $notes[$interview->id] = $note;
        $user = array();
        $match = MatchedAlters::findAll(array("interviewId1" => $interview->id));
        foreach ($match as $m) {
            if (!isset($user[$m->userId])) {
                $user[$m->userId] = User::findOne($m->userId);
            }
        }
        $matches[$interview->id] = $match;
        $other = array();
        $others[$interview->id] = $other;
        $x = new \XMLWriter();
        $x->openMemory();
        $x->setIndent(true);
        $x->startElement('interview');
        foreach ($columns['interview'] as $attr) {
            if (!in_array($attr, $exclude)) {
                $x->writeAttribute($attr, $interview->$attr);
            }
        }
        if (isset($answers[$interview->id])) {
            $x->startElement('answers');
            foreach ($answers[$interview->id] as $answer) {
                $x->startElement('answer');
                foreach ($columns['answer'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $answer->$attr);
                    }
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
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $alter->$attr);
                    }
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
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $graph->$attr);
                    }
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
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $note->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
        }
        if (isset($matches[$interview->id])) {
            $x->startElement('matchedAlters');
            foreach ($matches[$interview->id] as $match) {
                $x->startElement('matchedAlter');
                foreach ($columns['matchedAlters'] as $attr) {
                    if (!in_array($attr, $exclude)) {
                        $x->writeAttribute($attr, $match->$attr);
                    }
                }
                $x->endElement();
            }
            $x->endElement();
            if (count($user) > 0) {
                $x->startElement('users');
                foreach ($user as $u) {
                    $x->startElement('user');
                    foreach ($columns['user'] as $attr) {
                        if (!in_array($attr, $exclude)) {
                            $x->writeAttribute($attr, $u->$attr);
                        }
                    }
                    $x->endElement();
                }
                $x->endElement();
            }
        }
        $x->endElement();
        $output = $x->outputMemory();
        file_put_contents($filePath, $output);
    }

    public function getHasMatches()
    {
        $matches = MatchedAlters::find()->where(['interviewId1' => $this->id])->orWhere(['interviewId2' => $this->id])->all();
        foreach ($matches as $match) {
            if ($match->notes != "") {
                return 2;
            }
        }
        if (count($matches) > 0) {
            return 1;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'studyId' => 'Study ID',
            'completed' => 'Completed',
            'start_date' => 'Start Date',
            'complete_date' => 'Complete Date',
        ];
    }
}
