<?php

class ClientController extends ControllerBase
{

    public function exportAction($sid)
    {
        $this->view->survey = Surveys::findFirst($sid);
        $this->view->access = Access::findFirst($this->session->get('auth')['client'][$sid]);
    }

    public function answersAction($sid)
    {
        $this->view->survey = Surveys::findFirst($sid);
        $this->view->access = Access::findFirst($this->session->get('auth')['client'][$sid]);
    }

    public function analysisAction($sid, $sgid, $page = 1)
    {
        $this->view->survey = Surveys::findFirst($sid);
        $this->view->access = Access::findFirst($this->session->get('auth')['client'][$sid]);
        $this->view->subgroup = Subgroups::findFirst(array(
                "sgid = :sgid:",
                "bind" => array(
                    "sgid" => $sgid,
                )
        ));
        $this->view->page = $page;
    }

    public function liveanalysisAction($sid, $sgid, $page = 1)
    {
        $this->view->survey = Surveys::findFirst($sid);
        $this->view->access = Access::findFirst($this->session->get('auth')['client'][$sid]);
        $this->view->subgroup = Subgroups::findFirst(array(
                "sgid = :sgid:",
                "bind" => array(
                    "sgid" => $sgid,
                )
        ));
        $this->view->page = $page;
    }

    private function headers($title)
    {
        header("Expires: 0; ");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0; ");
        header("Cache-Control: private; ",false);
        header("Content-type: text/csv; ");
        header("Content-Disposition: attachment; filename=\"$title.csv\"; ");
    }

    public function dataAction($sid, $pid)
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $filter = new Phalcon\Filter();
        $survey = Surveys::findFirst($sid);
        $access = Access::findFirst($this->session->get('auth')['client'][$sid]);
        $phases = Phases::find(array(
                "sid = :sid: AND pid <= :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => $pid,
                ),
                "order" => "pid"
            ));
        foreach($phases as $p){
            if($p->pid == $pid)
                $phase = $p;
        }

        $title = $survey->name . " (" . $phase->name . ") Data";

        $this->headers($title);

        $stdout = fopen('php://output', 'w');

        $qid_to_question = array();
        $pid_to_qids = array();

        $range = range("A", "Z");
        $letters = array();
        foreach ($range as $letter) {
            $letters[] = $letter;
        }
        for ($i=1; $i<=100; $i++) {
            foreach ($range as $letter) {
              $letters[] = "$letter$i";
            }
        }

        $row1 = array("");
        $row2 = array("");
        $row3 = array("User");
        foreach($phases as $phase){

            if ($phase->pf_id != 1){
                $pid = $phase->pid;
                $round_identfier = "R$pid";
                $pid_to_qids[$pid] = array();

                //$subgroups = sql_select_subgroups($sid, $pid);
                foreach ($survey->getPhaseSubgroups($phase->pid) as $subgroup){
                    //$sgid = $sg["sgid"];
                    //$sg_num = $sg["sg_order"];
                    //$questions = sql_select_questions($sgid, $phase["pid"], true);

                    foreach ($subgroup->getQuestions(array("order"=>"q_order")) as $question){
                        //$qid = $question-["qid"];
                        //$q_num = $q["q_order"];
                        $q_identifier = "S$subgroup->sg_order Q$question->q_order";
                        //$q["identifier"] = $q_identifier;

                        $qid_to_question[$question->qid] = $question;

                        $pid_to_qids[$pid][] = $question->qid;

                        $row1[] = $question->label;
                        $qt = json_decode($question->qt_input, true);
                        if (in_array($question->qtid, array(4, 7, 8, 9, 10, 11, 12, 16))){
                            if (in_array($question->qtid, array(4, 7, 8, 12))){
                                $c_items = explode("\n", trim($qt["items"]));
                            } else {
                                $primary = explode("\n", trim($qt["items"]));
                                $secondary = explode("\n", trim($qt["secondary_items"]));
                                if(isset($qt['tertiary_items']))
                                    $tertiary = explode("\n", trim($qt['tertiary_items']));
                                $c_items = array();
                                foreach($primary as $p){
                                    foreach($secondary as $s){
                                        if(isset($tertiary)){
                                            foreach($tertiary as $t){
                                                $c_items[] = "$p - $s - $t";
                                            }
                                        }else{
                                            $c_items[] = "$p - $s";
                                        }
                                    }
                                }
                            }
                            $count = 0;
                            foreach ($c_items as $item_id => $item){
                                if ($item_id != 1){
                                    $row1[] = "";
                                }
                                $row2[] = $item;
                                $row3[] = $q_identifier . $letters[$item_id] . " $round_identfier";
                                if ($question->explain_answer && $question->qtid == 16){
                                    $count++;
                                    if($count == (count($secondary) * count($tertiary))){
                                        $row1[] = "";
                                        $row2[] = "Explanation";
                                        $row3[] = "";
                                        $count = 0;
                                    }
                                }
                            }

                        }
                        else {
                            $row2[] = html_entity_decode($filter->sanitize($question->question, "striptags"));
                            //$row2[] = html_clean($q["question"]);
                            $row3[] = "$q_identifier $round_identfier";
                        }

                        if ($question->explain_answer && $question->qtid != 16){
                            $row1[] = "";
                            $row2[] = "Explanation";
                            $row3[] = "";
                        }
                    }
                }
            }
        }

        fputcsv($stdout, $row1);
        fputcsv($stdout, $row2);
        fputcsv($stdout, $row3);

        foreach ($survey->getAccess(array("order"=>"name", "conditions"=>"privilege in (0,255)")) as $user){

            $answers =  Answers::find(array(
                "xid = :xid:",
                "bind" => array(
                    "xid" => $user->xid,
                )
            ));
            $aList = array();

            if (count($answers) > 0){
                foreach($answers as $a){
                    $aList[$a->qid][$a->pid] = $a;
                }
                $row = array();
                $row[] = $user->name;

                foreach($pid_to_qids as $pid => $qids){
                    foreach($qids as $qid){
                        $answer = $aList[$qid][$pid];
                        $response = $answer ? $answer->response : "";
                        $question = $qid_to_question[$qid];
                        $qt = json_decode($question->qt_input, true);
                        if($question->qtid == 6)
                            $response = $answer->explanation;
                        if($question->qtid == 16)
                            $explanations = json_decode($answer->explanation);


                        if (in_array($question->qtid, array(4, 7, 8, 9, 10, 11, 12, 16))){

                            // Special Format for Questions with Items
                            if ($response){
                                $response_array = explode(",", $response);
                                $count = 0;
                                foreach($response_array as $item_response){
                                    $row[] = $item_response;
                                    if ($question->explain_answer && $question->qtid == 16){
                                        $count++;
                                        if($count == (count($secondary) * count($tertiary))){
                                            $row[] = array_shift($explanations);
                                            $count = 0;
                                        }
                                    }
                                }
                            }
                            else {
                                if (in_array($question->qtid, array(4, 7, 8, 12))){
                                    $c_items = explode("\n", trim($qt["items"]));
                                } else {
                                    $primary = explode("\n", trim($qt["items"]));
                                    $secondary = explode("\n", trim($qt["secondary_items"]));
                                    if(isset($qt['tertiary_items']))
                                        $tertiary = explode("\n", trim($qt['tertiary_items']));
                                    $c_items = array();
                                    foreach($primary as $p){
                                        foreach($secondary as $s){
                                            if(isset($tertiary)){
                                                foreach($tertiary as $t){
                                                    $c_items[] = "$p - $s - $t";
                                                }
                                            }else{
                                                $c_items[] = "$p - $s";
                                            }
                                        }
                                    }

                                }
                                $num_items = count($c_items);
                                for($i = 0; $i < $num_items; $i++){
                                    $row[] = "";
                                }

                            }
                        }
                        else {
                            $row[] = $response;
                        }

                        if ($question->explain_answer && $question->qtid != 16){
                            $row[] = $answer ? $answer->explanation : "";
                        }
                    }
                }

                fputcsv($stdout, $row);
            }
        }
        fclose($stdout);
    }

    public function livedataAction($sid, $pid, $latest = 0)
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $filter = new Phalcon\Filter();
        $survey = Surveys::findFirst($sid);
        $access = Access::findFirst($this->session->get('auth')['client'][$sid]);
        $phase = Phases::findFirst(array(
                "sid = :sid: AND pid = :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => 1,
                ),
                "order" => "pid"
            ));

        $title = $survey->name . " (" . $phase->name . ") Data";

        $this->headers($title);

        $stdout = fopen('php://output', 'w');

        $qid_to_question = array();
        $pid_to_qids = array();

        $range = range("A", "Z");
        $letters = array();
        foreach ($range as $letter) {
            $letters[] = $letter;
        }
        for ($i=1; $i<=100; $i++) {
            foreach ($range as $letter) {
              $letters[] = "$letter$i";
            }
        }

        $row1 = array("");
        $row2 = array("");
        $row3 = array("User");
        $qMax = array();
        $qA = array();
        $num_items = array();


                $pid = $phase->pid;
                //$pid_to_qids[$pid] = array();
                //$subgroups = sql_select_subgroups($sid, $pid);
                foreach ($survey->getPhaseSubgroups($phase->pid) as $subgroup){
                    //$sgid = $sg["sgid"];
                    //$sg_num = $sg["sg_order"];
                    //$questions = sql_select_questions($sgid, $phase["pid"], true);
                    $questions = $subgroup->getQuestions(array("order"=>"q_order"));
                    foreach ($questions as $question){
                        //$qid = $question-["qid"];
                        $qMax[$question->qid] = $question->getMaxanswers();
                        if($qMax[$question->qid] == 0)
                            $qMax[$question->qid] = 1;
                        if($latest == 0){
                            $qA[$question->qid] = $question->getLiveanswersall();
                        }else{
                            $qA[$question->qid] = $question->getLiveanswers();
                            $qMax[$question->qid] = 1;
                        }



                        //$q_num = $q["q_order"];
                        $q_identifier = "S$subgroup->sg_order Q$question->q_order";
                        //$q["identifier"] = $q_identifier;

                        $qid_to_question[$question->qid] = $question;

                        //$pid_to_qids[$pid][] = $question->qid;
                        //if($question->qtid != 6)
                        $row1[] = $question->label;
                        $qt = $question->qt;
                        if (in_array($question->qtid, array(4, 7, 8, 9, 10, 11, 12, 16))){
                            if (in_array($question->qtid, array(4, 7, 8, 12))){
                                $c_items = explode("\n", trim($qt["items"]));
                            } else {
                                $primary = explode("\n", trim($qt["items"]));
                                $secondary = explode("\n", trim($qt["secondary_items"]));
                                if(isset($qt['tertiary_items']))
                                    $tertiary = explode("\n", trim($qt['tertiary_items']));
                                $c_items = array();
                                foreach($primary as $p){
                                    foreach($secondary as $s){
                                        if(isset($tertiary)){
                                            foreach($tertiary as $t){
                                                $c_items[] = "$p - $s - $t";
                                            }
                                        }else{
                                            $c_items[] = "$p - $s";
                                        }
                                    }
                                }
                            }
                            $num_items[$question->qid] = count($c_items);
                            $count = 0;
                            for($i=0;$i<$qMax[$question->qid];$i++){
                                $round_identfier = "X$i";
                                if ($i != 0){
                                    $row1[] = "";
                                }
                                foreach ($c_items as $item_id => $item){
                                    if ($item_id != 1){
                                        $row1[] = "";
                                    }
                                    $row2[] = $item;
                                    $row3[] = $q_identifier . $letters[$item_id] . " $round_identfier";
                                    if ($question->explain_answer && $question->qtid == 16){
                                        $count++;
                                        if($count == (count($secondary) * count($tertiary))){
                                            $row1[] = "";
                                            $row2[] = "Explanation";
                                            $row3[] = "";
                                            $count = 0;
                                        }
                                    }
                                }
                                if ($question->explain_answer && $question->qtid != 16){
                                    $row1[] = "";
                                    $row2[] = "Explanation";
                                    $row3[] = "";
                                }
                            }
                        }
                        else {

                            $qTitle = html_entity_decode($filter->sanitize($question->question, "striptags"));
                            //$row2[] = html_clean($q["question"]);
                            for($i=0;$i<$qMax[$question->qid];$i++){
                                $round_identfier = "X$i";
                                if($i != 0)
                                    $row1[] = "";
                                $row2[] = $qTitle;
                                $row3[] = "$q_identifier $round_identfier";
                                if ($question->explain_answer && $question->qtid != 16){
                                    $row1[] = "";
                                    $row2[] = "Explanation";
                                    $row3[] = "";
                                }
                            }
                        }


                    }
                }

        fputcsv($stdout, $row1);
        fputcsv($stdout, $row2);
        fputcsv($stdout, $row3);

        foreach ($survey->getAccess(array("order"=>"name", "conditions"=>"privilege in (0,255)")) as $user){

                $row = array();
                $row[] = $user->name;

                foreach($qid_to_question as $qid=>$question){
                    $qt = $question->qt;
                    for($i=0;$i<$qMax[$qid]; $i++){
                        if(isset($qA[$qid][$user->xid][$i]))
                            $answer = $qA[$qid][$user->xid][$i];
                        else
                            $answer = false;
                        $response = $answer ? $answer->response : "";
                        //$question = $qid_to_question[$qid];

                        if($question->qtid == 6)
                            $response = $answer ? $answer->explanation : "";
                        if($question->qtid == 16)
                            $explanations = json_decode($answer->explanation);


                        if ($question->qtid == 4 ||
                        $question->qtid ==  7 ||
                        $question->qtid ==  8 ||
                        $question->qtid ==  9 ||
                        $question->qtid ==  10 ||
                        $question->qtid ==  11 ||
                        $question->qtid ==  12 ||
                        $question->qtid ==  16){

                            // Special Format for Questions with Items
                            if ($response){
                                $response_array = explode(",", $response);
                                $count = 0;
                                foreach($response_array as $item_response){
                                    $row[] = $item_response;
                                    if ($question->explain_answer && $question->qtid == 16){
                                        $count++;
                                        if($count == (count($secondary) * count($tertiary))){
                                            $row[] = array_shift($explanations);
                                            $count = 0;
                                        }
                                    }
                                }
                            }
                            else {
                                for($j = 0; $j < $num_items[$qid]; $j++){
                                    $row[] = "";
                                }
                            }
                        } else {
                            $row[] = $response;
                        }

                        if ($question->explain_answer && $question->qtid != 16)
                            $row[] = $answer ? $answer->explanation : "";

                    }
                }

                fputcsv($stdout, $row);

        }
        fclose($stdout);
    }

    public function codebookAction($sid, $pid)
    {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $filter = new Phalcon\Filter();
        $survey = Surveys::findFirst($sid);
        $phase = Phases::findFirst(array(
                "sid = :sid: AND pid = :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => $pid,
                )
        ));

        $title = $survey->name . " (" . $phase->name . ") Codebook";

        $this->headers($title);

        $stdout = fopen('php://output', 'w');

        foreach ($survey->getPhaseSubgroups($phase->pid) as $subgroup){
            $sg_identifier = "S" . $subgroup->sg_order;
            foreach ($subgroup->questions as $question){
                $q_identifier = "Q" . $question->q_order;
                $qt = json_decode($question->qt_input, true);
                $row = array();
                $row[] = $subgroup->name;
                $row[] = $sg_identifier . $q_identifier;
                $row[] = $question->label;
                $row[] = html_entity_decode($filter->sanitize($question->question, "striptags"));

                $codes = Utils::choices($question->qtid, $qt);
                $codes = Utils::applyLabels($codes, $qt);
                foreach($codes as $index => $code){
                    $row[] = $index . " = " . $code;
                }

                fputcsv($stdout, $row);
            }
        }

        fclose($stdout);
    }

    private function outThread($thread, $type)
    {
        $filter = new Phalcon\Filter();
        $depth = 1;
        if($thread->ref_thid != 0)
            $depth = 2;
        $row = array();
        $row[] = $thread->access->name;
        if($thread->sid)
            $row[] = Utils::time_nice($thread->time, $thread->surveys->timezones->offset, false);
        else
            $row[] = "";
        if($thread->ref_thid > 0){
            if($type == "rationale")
                $row[] = html_entity_decode( $filter->sanitize($thread->answers->explanation, "striptags"));
            else
                $row[] = html_entity_decode( $filter->sanitize($thread->parent->body, "striptags"));
            $row[] = html_entity_decode( $filter->sanitize($thread->body, "striptags"));
            $row[] = $thread->getAgrees($thread->xid);
            $row[] = $thread->getDisagrees($thread->xid);
        }else{
            $row[] = html_entity_decode( $filter->sanitize($thread->body, "striptags"));
            $row[] = "";
            $row[] = $thread->agrees;
            $row[] = $thread->disagrees;
        }
        $row[] = $thread->answers ? $thread->answers->questions->label : "General Discussion";
        $row[] = $thread->subgroups->name;
        return $row;
    }

    private function oldOutThread($thread)
    {
        $filter = new Phalcon\Filter();
        $depth = 1;
        if($thread->ref_thid != 0)
            $depth = 2;
        $row = array();
        $row[] = $thread->access->name;
        if($thread->sid)
            $row[] = Utils::time_nice($thread->time, $thread->surveys->timezones->offset, false);
        else
            $row[] = "";
        $row[] = str_repeat(">", $depth);
        $row[] = html_entity_decode( $filter->sanitize($thread->body, "striptags"));
        $row[] = $thread->agrees;
        $row[] = $thread->disagrees;
        $row[] = $thread->answers ? $thread->answers->questions->label : "General Discussion";
        $row[] = $thread->subgroups->name;
        return $row;
    }

    public function discussionAction($sid, $pid, $panel)
    {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $filter = new Phalcon\Filter();
        $survey = Surveys::findFirst($sid);
        $phase = Phases::findFirst(array(
                "sid = :sid: AND pid = :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => $pid,
                )
        ));

        $title = $survey->name . " (" . $phase->name . ") Discussion (Panel $panel)";

        $this->headers($title);

        $stdout = fopen('php://output', 'w');
        fputcsv($stdout, array("User", "Time", "Original Post", "Comment", "Agree", "Disagree", "Question", "Subgroup"));
        fputcsv($stdout, array());

        echo count($survey->getPhaseSubgroups($pid));
        foreach($survey->getPhaseSubgroups($pid) as $subgroup){
            $discussionThreads = Threads::find(array(
                "ref_aid = 0 AND ref_thid = 0 AND sgid = :sgid: AND panel = :panel:",
                "bind" => array(
                    "sgid" => $subgroup->sgid,
                    "panel" => $panel,
                ),
                "order"=>"time DESC"
            ));
            foreach($discussionThreads as $thread){
                fputcsv($stdout, $this->outThread($thread));
                if($thread->children > 0){
                    foreach($thread->children as $child)
                        fputcsv($stdout, $this->outThread($child));
                }
            }
            foreach($subgroup->getQuestions(array("order"=>"q_order")) as $question){
                if($survey->type == 0){
                    $answers = Answers::find(array(
                        "qid = :qid: AND pid = :pid:",
                        "bind" => array(
                            "qid" => $question->qid,
                            "pid" => $phase->pf_ref,
                        ),
                        "order"=>"response"
                    ));
                }else{
                    $answers = Answers::find(array(
                        "qid = :qid:",
                        "bind" => array(
                            "qid" => $question->qid,
                        ),
                        "order"=>"responsetime"
                    ));
                }
                foreach($answers as $answer){
                    $thread = $answer->getThreads(array("conditions"=>"ref_thid = 0 AND panel = '" . $panel . "'"));
                    if($thread && $thread->children){
                        fputcsv($stdout, $this->outThread($thread));
                        foreach($thread->children as $child)
                            fputcsv($stdout, $this->outThread($child));
                    }
                }
            }
        }

        fclose($stdout);
    }

    public function oldDiscussionAction($sid, $pid, $panel)
    {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $filter = new Phalcon\Filter();
        $survey = Surveys::findFirst($sid);
        $phase = Phases::findFirst(array(
                "sid = :sid: AND pid = :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => $pid,
                )
        ));

        $title = $survey->name . " (" . $phase->name . ") Discussion (Panel $panel)";

        $this->headers($title);

        $stdout = fopen('php://output', 'w');
        fputcsv($stdout, array("User", "Time", "Hierarchy", "Body", "Agree", "Disagree", "Question", "Subgroup"));
        fputcsv($stdout, array());

        echo count($survey->getPhaseSubgroups($pid));
        foreach($survey->getPhaseSubgroups($pid) as $subgroup){
            $discussionThreads = Threads::find(array(
                "ref_aid = 0 AND ref_thid = 0 AND sgid = :sgid: AND panel = :panel:",
                "bind" => array(
                    "sgid" => $subgroup->sgid,
                    "panel" => $panel,
                ),
                "order"=>"time DESC"
            ));
            foreach($discussionThreads as $thread){
                fputcsv($stdout, $this->outThread($thread));
                if($thread->children > 0){
                    foreach($thread->children as $child)
                        fputcsv($stdout, $this->outThread($child));
                }
            }
            foreach($subgroup->getQuestions(array("order"=>"q_order")) as $question){
                if($survey->type == 0){
                    $answers = Answers::find(array(
                        "qid = :qid: AND pid = :pid:",
                        "bind" => array(
                            "qid" => $question->qid,
                            "pid" => $phase->pf_ref,
                        ),
                        "order"=>"response"
                    ));
                }else{
                    $answers = Answers::find(array(
                        "qid = :qid:",
                        "bind" => array(
                            "qid" => $question->qid,
                        ),
                        "order"=>"responsetime"
                    ));
                }
                foreach($answers as $answer){
                    $thread = $answer->getThreads(array("conditions"=>"ref_thid = 0 AND panel = '" . $panel . "'"));
                    if($thread && $thread->children){
                        fputcsv($stdout, $this->outThread($thread));
                        foreach($thread->children as $child)
                            fputcsv($stdout, $this->outThread($child));
                    }
                }
            }
        }

        fclose($stdout);
    }

    public function viewdiscussionAction($sid, $pid, $panel)
    {

        $filter = new Phalcon\Filter();
        $this->view->survey = Surveys::findFirst($sid);
        $this->view->phase = Phases::findFirst(array(
                "sid = :sid: AND pid = :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => $pid,
                )
        ));
        $this->view->pid = $pid;
        $this->view->panel = $panel;

    }

    public function accessAction($sid, $pid)
    {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $filter = new Phalcon\Filter();
        $survey = Surveys::findFirst($sid);
        $phase = Phases::findFirst(array(
                "sid = :sid: AND pid = :pid:",
                "bind" => array(
                    "sid" => $sid,
                    "pid" => $pid,
                )
        ));

        $title = $survey->name . " (" . $phase->name . ") Access";

        $this->headers($title);

        $stdout = fopen('php://output', 'w');

        $rows = array();

        foreach ($survey->access as $user){
            $row = array();
            $row[] = $user->name;
            foreach ($user->getPages(array("conditions"=>"pid = $pid")) as $pages){
                $row[] = Utils::time_nice($pages->start_time, $survey->timezones->offset, false);
                $seconds = strtotime($pages->end_time) - strtotime($pages->start_time);

                $hours = floor($seconds / 3600);
                //$seconds = $seconds % 3600;
                $minutes = ceil($seconds / 60);


                $row[] = $minutes;
            }

            $rows[] = $row;
        }

        $header = array("User");

        if ($rows){
            $longest_row = max($rows);
            $num_cols = count($longest_row);
            $max_accesses = ($num_cols - 1) / 2;
            for ($i = 1; $i <= $max_accesses; $i++){
                $header[] = "Login Time ($i)";
                $header[] = "Duration ($i)";
            }
        }

        fputcsv($stdout, $header);
        fputcsv($stdout, array());

        foreach($rows as $row){
            fputcsv($stdout, $row);
        }

        fclose($stdout);
    }

    public function participationAction($sid)
    {
        $this->view->survey = Surveys::findFirst($sid);
        $this->view->access = Access::findFirst($this->session->get('auth')['administrator'][$sid]);
        $this->view->qCount = $this->view->survey->qCount(1);
        $this->view->qCount3 = $this->view->survey->qCount(3);
        $this->view->xR1count0 = 0;
        $this->view->xR1count50 = 0;
        $this->view->xR1count90 = 0;
        $this->view->xR3count0 = 0;
        $this->view->xR3count50 = 0;
        $this->view->xR3count90 = 0;
        $this->view->xR3and1count0 = 0;
        $this->view->xR3and1count50 = 0;
        $this->view->xR3and1count90 = 0;
        $this->view->xR1Total = 0;
        $this->view->xR3Total = 0;
        $this->view->xR3and1Total = 0;
        $this->view->xR2Accessed = 0;
        $this->view->xR2Posted = 0;
        $this->view->xR2and1Accessed = 0;
        $this->view->xR2and1Posted = 0;
        $this->view->xR2Comments = 0;
        $this->view->xR2ModComments = 0;

        $half = floor($this->view->qCount / 2);
        $ninety = floor($this->view->qCount * 0.9);
        $half3 = floor($this->view->qCount3 / 2);
        $ninety3 = floor($this->view->qCount3 * 0.9);

        foreach($this->view->survey->getAccess(array("conditions"=>"privilege = 0")) as $access){
            $pTable[$access->xid]['name'] = $access->name;
            $pTable[$access->xid]['consent'] = $access->consent == 1 ? "Agreed" : "Not Agreed";
            $pTable[$access->xid]['r1x'] = 0;
            $pTable[$access->xid]['r2x'] = 0;
            $pTable[$access->xid]['r3x'] = 0;
            $pTable[$access->xid]['r2count'] = 0;

            $browsers = array();
            $pages = $access->pages;
            foreach($pages as $page){
                if($page->ip_address){
                    $browsers[] = "($page->start_time - $page->end_time) [" . $page->ip_address  . "] " . $page->user_agent;
                }
            }
            $pTable[$access->xid]['browsers'] = implode("<br>", $browsers);

            $pages = $access->getPages(array("conditions"=>"pid = 1"));
            if(count($pages) > 0){
                $pTable[$access->xid]['r1x'] = count($pages);
            }
            $pages = $access->getPages(array("conditions"=>"pid = 3"));
            if(count($pages) > 0){
                $pTable[$access->xid]['r3x'] = count($pages);
            }

            $answers = $access->countAnswers(1);
            $pTable[$access->xid]['r1count'] = $answers;
            if($answers > 0){
                $this->view->xR1count0++;
            }
            if($answers > $half)
                $this->view->xR1count50++;
            if($answers > $ninety)
                $this->view->xR1count90++;
            $answers3 = $access->countAnswers(3);
            $pTable[$access->xid]['r3count'] = $answers3;
            if($answers3 > 0){
                $this->view->xR3count0++;
                if($answers > 0){
                    $this->view->xR3and1count0++;
                }
            }
            if($answers3 > $half3){
                $this->view->xR3count50++;
                if($answers > 0)
                    $this->view->xR3and1count50++;
            }
            if($answers3 > $ninety3){
                $this->view->xR3count90++;
                if($answers > 0)
                    $this->view->xR3and1count90++;
            }
            $threads = $access->getThreads(array("conditions"=>"pid = 2"));
            if(count($threads) > 0){
                $this->view->xR2Posted++;
                if($pTable[$access->xid]['r1x'] > 0)
                    $this->view->xR2and1Posted++;
                $pTable[$access->xid]['r2count'] = count($threads);
                $this->view->xR2Comments += count($threads);
            }
            $pages = $access->getPages(array("conditions"=>"pid = 2"));
            if(count($pages) > 0){
                $this->view->xR2Accessed++;
                if($pTable[$access->xid]['r1x'] > 0)
                    $this->view->xR2and1Accessed++;
                $pTable[$access->xid]['r2x'] = count($pages);
            }
        }
        foreach($this->view->survey->getAccess(array("conditions"=>"privilege = 3")) as $mod){
            $threads = $mod->getThreads(array("conditions"=>"pid = 2"));
            $this->view->xR2ModComments += count($threads);
        }

        $this->view->xR3and1Total = $this->view->xR1count0;
        $this->view->xR1Total = count($pTable);
        $this->view->xR3Total = count($pTable);
        $this->view->pTable = $pTable;
    }

    public function viewAction($sid, $pid, $sgid = null)
    {
        $this->view->access = Access::findFirst($this->session->get('auth')['client'][$sid]);

        $this->view->survey = Surveys::findFirst($sid);
        $this->view->phase = Phases::findFirst(array(
            "sid = :sid: AND pid = :pid:",
            "bind" => array(
                "sid" => $sid,
                "pid" => $pid,
            )
        ));
        $this->view->mPanel = "";
        $this->view->prev = "";
        $this->view->next = "";
        $this->view->subgroups = $this->view->survey->getPhaseSubgroups($this->view->phase->pid);
        $this->view->required = array();
        $this->view->subgroup_names = array();
        $this->view->o_subgroup = Subgroups::findFirst($sgid);
        $this->view->subgroup = Subgroups::findFirst($sgid);
        if($this->view->access->r_subs){
            $r_array = json_decode($this->view->access->r_subs, true);
            if($this->view->subgroup->r_sub){
                $sgid = $r_array[$sgid];
                $this->view->subgroup = Subgroups::findFirst($sgid);
            }
        }else{
            $r_array = array();
            foreach($subgroups as $i=>$s){
                $r_array[$s->sgid] = $s->sgid;
            }
        }
        foreach($this->view->subgroups as $i=>$s){
            $this->view->subgroup_names[$s->sgid] = $s->name;
            if($o_subgroup == $s)
                $current = $i;
            if(count($r_array) > 0)
                $sgid = $r_array[$s->sgid];
            else
                $sgid = $s->sgid;
            $this->view->required[$sgid] = false;
            if($survey->type == 1)
                continue;
            $aCount = $this->view->access->countAnswers($this->view->phase->pid, $sgid);
            $qCount = $this->view->survey->qCount($this->view->phase->pid, true, $sgid, true);
            if($qCount > 0 && $aCount / $qCount != 1 && $this->view->phase->pid != 2 && $i != 0)
                $this->view->required[$sgid] = true;
        }
        if(isset($this->view->subgroups[$current-1]))
            $this->view->prev = $this->view->subgroups[$current - 1];
        if(isset($this->view->subgroups[$current+1]))
            $this->view->next = $this->view->subgroups[$current + 1];
        $this->view->current = $current;

        $this->view->r_array = $r_array;

        $questions = $this->view->subgroup->getQuestions(array("conditions"=>"hidden = 0 OR $pid < hidden", "order"=>"q_order"));
        foreach ($questions as $question) {
        	if (($question->qtid == Questions::GRID_RADIO_NUMERIC) ||
        	    ($question->qtid == Questions::GRID_RADIO_CUSTOM) ||
        	    ($question->qtid == Questions::GRID_DROPDOWN_NUMERIC) ||
        	    ($question->qtid == Questions::GRID_DROPDOWN_CUSTOM) ||
        	    ($question->qtid == Questions::RANKING)) {
        	    foreach ($question->getLivedata($phase, $access) as $d) {
        		    Utils::categorizePMax($d);
        	    }
        	} else {
                if ($question->qtid != Questions::TEXT && $question->qtid != Questions::TEXT_STATIC)
        		    Utils::categorizePMax($question->getLivedata($phase, $access));
        	}

        }

        $this->view->questions = $questions;

        $this->flashSession->notice("You are viewing this study as a client. Answers will not be saved. Discussion posting is disabled.  " . $this->tag->linkTo(array('/client/participation/' . $sid, 'Back', 'class' => 'edit-btn')));
    }

}
