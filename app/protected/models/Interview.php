<?php

namespace app\models;

use Yii;
use app\helpers\Statistics;

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

    public static function multiInterviewIds($interviewId = null, $study = null)
    {
        $interview = Interview::findOne($interviewId);
        $egoAnswer = Answer::findOne(array("interviewId" => $interview->id, "questionId" => $study->multiSessionEgoId));
        $interviewIds = array();
        $multiIdQs = $study->multiIdQs();
        if ($interview && $study && $study->multiSessionEgoId) {
            foreach ($multiIdQs as $q) {
                $newAnswers = Answer::findAll(array("studyId" => $q->studyId, "questionId" => $q->id));
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

    public static function getInterviewFromEmail($studyId, $email)
    {
        $answers = Answer::findAll(array('questionType' => "EGO_ID", "studyId" => $studyId));
        $interview = false;
        foreach ($answers as $answer) {
            if ($answer->value == $email)
                $interview = Interview::findOne($answer->interviewId);
        }
        return $interview;
    }

    public function getEgoId()
    {   
        $egoIdString = [];
        $answers = Answer::findAll(array('questionType' => "EGO_ID", "interviewId" => $this->id));
        foreach($answers as $answer){
            $egoIdString[] = $answer->value;
        }
        return implode("_", $egoIdString);
    }

    public function exportEgoAlterData($file = null, $withAlters = false)
    {
        $all_questions = Question::find()->where(["studyId"=>$this->studyId])->orderBy(["ordering"=>"ASC"])->all();
        $ego_id_questions = [];
        $ego_questions = [];
        $alter_questions = [];
        $network_questions = [];
        $name_gen_questions = [];
        $previous_questions = [];
        foreach($all_questions as $question){
            if($question->subjectType == "EGO_ID")
                $ego_id_questions[] = $question;
            if($question->subjectType == "EGO")
                $ego_questions[] = $question;
            if($question->subjectType == "ALTER")
                $alter_questions[] = $question;
            if($question->subjectType == "NETWORK")
                $network_questions[] = $question;
            if($question->subjectType == "NAME_GENERATOR")
                $name_gen_questions[] = $question;
            if($question->subjectType == "PREVIOUS_ALTER")
                $previous_questions[] = $question;
        }

        $alters = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $this->id .", interviewId)"))
        ->all();

        if (!$alters) {
            $alters = array('0' => array('id' => null));
        } else {
            if (isset($_POST['expressionId']) && $_POST['expressionId']) {
                $stats = new Statistics;
                $stats->initComponents($this->id, $_POST['expressionId']);
            }
        }

        $study = Study::findOne($this->studyId);
        $multiQs = false;
        if(isset($study->multiSessionEgoId) && $study->multiSessionEgoId)
            $multiQs = $study->multiIdQs();

        if($multiQs){
            $interviewIds = Interview::multiInterviewIds($this->id, $study);
            $prevIds = array();
            if(is_array($interviewIds))
                $prevIds = array_diff($interviewIds, array($interviewId));
            foreach($prevIds as $i_id){
                $results = Alters::find()
                ->where(new \yii\db\Expression("FIND_IN_SET(" . $i_id .", interviewId)"))
                ->all();
                foreach($results as $result){
                    $aInts = explode(",",$result->interviewId);
                    if(!in_array($this->id, $aInts))
                        $alters[] = $result;
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
                if ($this->id == $match->interviewId1)
                    $matchInt = Interview::findOne($match->interviewId2);
                else
                    $matchInt = Interview::findOne($match->interviewId1);
                $matchIntId = $match->getMatchId();
                $matchUser = User::getName($match->userId);
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
            $answers[] = $alter->id;
            $ego_ids = array();
            $ego_id_string = array();

            foreach ($ego_id_questions as $question) {

                #OK FOR SQL INJECTION
                $result = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
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
                $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
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
                        $answer->value = preg_replace('/amp;/', "", $answer->value);
                        $answers[] = htmlspecialchars_decode($answer->value, ENT_QUOTES);
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
                $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id));
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
                        $answer->value = preg_replace('/amp;/', "", $answer->value);
                        $answers[] = htmlspecialchars_decode($answer->value);
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

            if($multiQs){
                $aInts = explode(",",$alter->interviewId);
                $aStudies = array();
                foreach($aInts as $aInt){
                    $int = Interview::findOne($aInt);
                    $aStudies[] = $int->studyId;
                }
                foreach($multiQs as $q){
                    $answers[] = intval(in_array($q->studyId, $aStudies));
                }
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
                foreach ($previous_questions as $question) {
                    $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id, "alterId1" => $alter->id));
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
                            $answers[] =  htmlspecialchars_decode($answer->value);
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
                foreach ($alter_questions as $question) {
                    $answer = Answer::findOne(array("interviewId" => $this->id, "questionId" => $question->id, "alterId1" => $alter->id));
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
                            $answers[] =  htmlspecialchars_decode($answer->value);
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
            if ($file === null) {
                $all_answers[] = $answers;
            }else{
                fputcsv($file, $answers);
            }
            $count++;
        }
        if ($file === null){
            return $all_answers;
        }else{
            fclose($file);
            die();
        }
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
                    $result = Answer::findOne([
                        "interviewId"=>$this->id,
                        "questionId"=>$question->id,
                        "alterId1"=>$alter->id,
                        "alterId2"=>$alter2->id,
                    ]);
                    if(!$result)
                        continue;
                    $answer = $result->value;
                    $skipReason = $result->skipReason;
                    if ($answer != "" && $skipReason == "NONE") {
                        if ($question->answerType == "SELECTION") {
                            $answers[] = $options[$answer];
                        } else if ($question->answerType == "MULTIPLE_SELECTION") {
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

    public function exportOtherData ($file, $study)
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
                            $answerArray[$other_options[$optionId]->name] = "";
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
                if(!isset($answers[$question->id]))
                    continue;
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
        foreach($match as $m){
            if(!isset($user[$m->userId]))
                $user[$m->userId] = User::findOne($m->userId);
        }
        $matches[$interview->id] = $match;
        $other = array();
        $others[$interview->id] = $other;
        $x = new \XMLWriter();
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
        if (isset($matches[$interview->id])) {
            $x->startElement('matchedAlters');
            foreach ($matches[$interview->id] as $match) {
                $x->startElement('matchedAlter');
                foreach ($columns['matchedAlters'] as $attr) {
                    if (!in_array($attr, $exclude))
                        $x->writeAttribute($attr, $match->$attr);
                }
                $x->endElement();
            }
            $x->endElement();
            if(count($user) > 0){
                $x->startElement('users');
                foreach ($user as $u) {
                    $x->startElement('user');
                    foreach ($columns['user'] as $attr) {
                        if (!in_array($attr, $exclude))
                            $x->writeAttribute($attr, $u->$attr);
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
