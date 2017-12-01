<?php
class visualize extends Plugin
{
	public $params = "";
	public $networkTitle = "";
	public $edgeColors = array(
		'#000'=>'black',
		'#ccc'=>'gray',
		'#07f'=>'blue',
		'#0c0'=>'green',
		'#F80'=>'orange',
		'#fa0'=>'yellow',
		'#f00'=>'red',
		'#c0f'=>'purple',
	);
	public $edgeSizes = array(
		"0.5"=>'0.5',
		"2"=>'2',
		"4"=>'4',
		"8"=>'8',
	);
	public $nodeColors = array(
		'#000'=>'black',
		'#ccc'=>'gray',
		'#07f'=>'blue',
		'#0c0'=>'green',
		'#F80'=>'orange',
		'#fa0'=>'yellow',
		'#f00'=>'red',
		'#c0f'=>'purple',
	);
	public $nodeShapes = array(
		'circle'=>'circle',
		'star'=>'star',
		'diamond'=>'diamond',
		'cross'=>'cross',
		'equilateral'=>'triangle',
		'square'=>'square',
	);
	public $nodeSizes = array(
		2=>'1',
		4=>'2',
		6=>'3',
		8=>'4',
		10=>'5',
		12=>'6',
		14=>'7',
		16=>'8',
		18=>'9',
		20=>'10',
	);
	/*
	public $gradient = array(
		0=>"#00f",
		1=>"#0ae",
		2=>"#0cd",
		3=>"#0dc",
		4=>"#7ea",
		5=>"#ae7",
		6=>"#cd0",
		7=>"#dc0",
		8=>"#ea0",
		9=>"#f00",
	);
	*/
	public $gradient = array(
		0=>"#F5D6D6",
		1=>"#ECBEBE",
		2=>"#E2A6A6",
		3=>"#D98E8E",
		4=>"#CF7777",
		5=>"#C65F5F",
		6=>"#BC4747",
		7=>"#B32F2F",
		8=>"#A91717",
		9=>"#A00000",
	);
	public $stats = "";
	public $answers = array();

	private function getNodeColor($nodeId){
		$default = "#07f";
		if(isset($this->params['nodeColor'])){
			if(in_array($this->params['nodeColor']['questionId'], array("degree", "betweenness", "eigenvector"))){
				if($this->params['nodeColor']['questionId'] == "degree"){
					$max = $this->stats->maxDegree();
					$min = $this->stats->minDegree();
					$value = $this->stats->getDegree($nodeId);
				}
				if($this->params['nodeColor']['questionId'] == "betweenness"){
					$max = $this->stats->maxBetweenness();
					$min = $this->stats->minBetweenness();
					$value = $this->stats->getBetweenness($nodeId);
				}
				if($this->params['nodeColor']['questionId'] == "eigenvector"){
					$max = $this->stats->maxEigenvector();
					$min = $this->stats->minEigenvector();
					$value = $this->stats->EigenvectorCentrality($nodeId);
				}
				$range = $max - $min;
				if($range == 0)
					$range = 1;
				$value = round((($value-$min) / ($range)) * 9);
				return $this->gradient[$value];
			}else if(stristr($this->params['nodeColor']['questionId'], "expression")){
				list($label, $expressionId) = explode("_", $this->params['nodeColor']['questionId']);
				$expression = Expression::model()->findByPk($expressionId);
				if($expression->evalExpression($this->method, $nodeId, null, $this->answers)){
					foreach($this->params['nodeColor']['options'] as $option){
						if($option->id == 1)
							return $option['color'];
					}
				}else{
					foreach($this->params['nodeColor']['options'] as $option){
						if($option->id == 0)
							return $option['color'];
					}
				}

			}else if($this->params['nodeColor']['questionId']){
                $criteria = array(
                    "condition"=>"questionId = ".$this->params['nodeColor']['questionId']. " AND alterId1 = " .$nodeId,
                );
				$answer = Answer::model()->find($criteria);
				$answerV = explode(',', $answer->value);
				foreach($this->params['nodeColor']['options'] as $option){
                    if($option['id'] == 0 && $answer->skipReason != "NONE")
                        return $option['color'];
					if($option->id == $answerV || in_array($option->id, $answerV))
						return $option['color'];
				}
			}
		}
		return $default;
	}

	private function getNodeShape($nodeId){
		$default = "circle";
		if(isset($this->params['nodeShape'])){
            $criteria = array(
                "condition"=>"questionId = ".$this->params['nodeShape']['questionId']. " AND alterId1 = " .$nodeId,
            );
            $answer = Answer::model()->find($criteria);
            $answerV = explode(',', $answer->value);
			foreach($this->params['nodeShape']['options'] as $option){
                if($option['id'] == 0 && ($answerV == "" || $answer->skipReason != "NONE"))
                    return $option['shape'];
				if($option->id == $answerV || in_array($option->id, $answerV))
					return $option['shape'];
			}
		}
		return $default;
	}


	private function getNodeSize($nodeId){
		$default = 4;
		if(isset($this->params['nodeSize'])){
			if(in_array($this->params['nodeSize']['questionId'], array("degree", "betweenness", "eigenvector"))){
				if($this->params['nodeSize']['questionId'] == "degree"){
					$max = $this->stats->maxDegree();
					$min = $this->stats->minDegree();
					$value = $this->stats->getDegree($nodeId);
				}
				if($this->params['nodeSize']['questionId'] == "betweenness"){
					$max = $this->stats->maxBetweenness();
					$min = $this->stats->minBetweenness();
					$value = $this->stats->getBetweenness($nodeId);
				}
				if($this->params['nodeSize']['questionId'] == "eigenvector"){
					$max = $this->stats->maxEigenvector();
					$min = $this->stats->minEigenvector();
					$value = $this->stats->EigenvectorCentrality($nodeId);
				}
				$range = $max - $min;
				if($range == 0)
					$range = 1;
				$value = round((($value-$min) / ($range)) * 9) + 1;
				$default = current(array_keys($this->nodeSizes, $value));
			}else{
                $criteria = array(
                    "condition"=>"questionId = ".$this->params['nodeSize']['questionId']. " AND alterId1 = " .$nodeId,
                );
                $answer = Answer::model()->find($criteria);
                $answerV = explode(',', $answer->value);
				foreach($this->params['nodeSize']['options'] as $option){
                    if($option['id'] == 0 && $answer->skipReason != "NONE")
                        return intval($option['size']);
					if($option->id == $answerV || in_array($option->id, $answerV))
						$default = intval($option['size']);
				}
			}

		}
		return $default;
	}

	private function getEdgeColor($nodeId1, $nodeId2){
		$default = "#ccc";
		if(isset($this->params['edgeColor'])){
            $criteria = array(
                "condition"=>"questionId = ".$this->params['edgeColor']['questionId']. " AND alterId1 = " .$nodeId1 . " AND alterId2 = " . $nodeId2,
            );
            $answer = Answer::model()->find($criteria);
            $answerV = explode(',', $answer->value);
			foreach($this->params['edgeColor']['options'] as $option){
                if($option['id'] == 0 && $answer->skipReason != "NONE")
                    return $option['color'];
				if($option->id == $answerV || in_array($option->id, $answerV))
					return $option['color'];
			}
		}
		return $default;
	}

	private function getEdgeSize($nodeId1, $nodeId2){
		$default = 1;
		if(isset($this->params['edgeSize'])){
            $criteria = array(
                "condition"=>"questionId = ".$this->params['edgeSize']['questionId']. " AND alterId1 = " .$nodeId1 . " AND alterId2 = " . $nodeId2,
            );
            $answer = Answer::model()->find($criteria);
            $answerV = explode(',', $answer->value);
			foreach($this->params['edgeSize']['options'] as $option){
                if($option['id'] == 0 && $answer->skipReason != "NONE")
                    return floatval($option['size']);
				if($option->id == $answerV || in_array($option->id, $answerV))
					$default = floatval($option['size']);
			}
		}
		return $default;
	}

	public function actionNotes()
	{
		$notes = Note::model()->findAllByAttributes(array("interviewId"=>$this->params, "expressionId"=>$this->id));
		foreach($notes as $note){
			if(is_numeric($note->alterId))
				$label = Alters::getName($note->alterId);
			else
				$label = str_replace("graphNote-", "", $note->alterId);
			echo "<div style='width:50%;float:left;padding-right:20px;clear:both' class=''><h3>" . $label . " </h3><small>$note->notes</small></div>";
		}
	}

	public function actionNodecolor(){
		$params = json_decode($this->params, true);
		$nodeColorId = ''; $nodeColors = array();
		$centralities = array("degree", "betweenness", "eigenvector");

		if(isset($params['nodeColor'])){
			$nodeColorId = $params['nodeColor']['questionId'];
			foreach($params['nodeColor']['options'] as $option){
				$nodeColors[$option['id']] = $option['color'];
			}
		}
		echo "<div class='form-horizontal'>";
		echo "<label class='control-label col-sm-6'>Node Color</label>";
        echo "<div class='col-sm-6'>";
        echo CHtml::dropDownList(
                "0",
                (isset($nodeColors[0]) ? $nodeColors[0] : ''),
                $this->nodeColors,
                array("class"=>"form-control", "id"=>"defaultNodeColor")
            );
        echo "</div>";
		echo "<select id='nodeColorSelect' class='form-control' onchange='$(\".nodeColorOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle();'>";
		echo "<option value=''> Select </option>";

		foreach($centralities as $centrality){
			$selected = '';
			if($nodeColorId == $centrality)
				$selected = "selected";
			echo "<option value='" . $centrality . "_nodeColor' $selected>" . ucfirst($centrality) . " Centrality</option>";
		}

		$questionIds = array();
        $criteria = array(
            "condition"=>"subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id,
        );
        $alter_qs = Question::model()->findAll($criteria);

		foreach($alter_qs as $alter_q){
			$questionIds[] = $alter_q->id;
		}
		$questionIds = implode(",", $questionIds);
		if(!$questionIds)
			$questionIds = 0;
        $criteria = array(
            "condition"=>"studyId = " . $this->id . " AND questionId in (" . $questionIds . ")",
        );
        $alter_expressions = Expression::model()->findAll($criteria);
        $all_expression_ids = array();
		foreach($alter_expressions as $exp){
			$all_expression_ids[] = $exp->id;
            $criteria = array(
                "condition"=>"FIND_IN_SET($exp->id, value)",
            );
            $sub_exps = Expression::model()->findAll($criteria);
            if($sub_exps){
                foreach($sub_exps as $s_exp){
                    $all_expression_ids[] = $s_exp->id;
                }
            }
		}
		if(count($all_expression_ids) > 0){
            $criteria = array(
                "condition"=>"id in (" . implode(",",$all_expression_ids) . ")",
            );
            $alter_expressions = Expression::model()->findAll($criteria);
		}else{
			$alter_expressions = array();
		}

		foreach($alter_expressions as $expression){
			$selected = '';
			if($nodeColorId == "expression_" . $expression->id)
				$selected = "selected";
			echo "<option value='expression_"  . $expression->id . "_nodeColor' $selected>" .$expression['name'].  "</option>";

		}
		foreach($alter_qs as $question){
			$selected = '';
			if($nodeColorId == $question->id)
				$selected = "selected";
			echo "<option value='"  . $question->id. "_nodeColor' $selected>" .$question->title.  "</option>";
		}
		echo "</select></div>";
		foreach($alter_qs as $question){
			echo "<div class='nodeColorOptions' id='" .$question->id ."_nodeColor' style='" . ( $question->id != $nodeColorId ? "display:none" : "") . "'>";
            $options = QuestionOption::model()->findAllByAttributes(array("questionId"=>$question->id));
			foreach($options as $option){
				echo "<div><label style='width:200px;float:left;font-size: .7em;'>". $option->name . "</label>";
				echo CHtml::dropDownList(
						$option->id,
						(isset($nodeColors[$option->id]) ? $nodeColors[$option->id] : ''),
						$this->nodeColors
					). "</div>";
			}
            echo "</div>";
		}

		foreach($alter_expressions as $expression){
			echo "<div class='nodeColorOptions' id='expression_" .$expression->id ."_nodeColor' style='" . ( "expression_" . $expression->id != $nodeColorId ? "display:none" : "") . "'>";
			$options = array("false", "true");
			foreach($options as $index=>$option){
				echo "<div><label style='width:200px;float:left;font-size: .7em;'>". $option. "</label>";
				echo CHtml::dropDownList(
						$index,
						(isset($nodeColors[$index]) ? $nodeColors[$index] : ''),
						$this->nodeColors
					). "</div>";
			}
			echo "</div>";
		}

		foreach($centralities as $centrality){
			echo "<div class='nodeColorOptions' id='" .$centrality ."_nodeColor' style='" . ($centrality != $nodeColorId ? "display:none" : "") . "'>";
			for($i = 0; $i<10; $i++){
				echo '<span style="color:' . $this->gradient[$i] . '; font-size:20px">■</span>';
			}
			echo "</div>";
		}
	}

	public function actionNodeshape(){
		$params = json_decode($this->params, true);
		$nodeShapeId = ''; $nodeShapes = array();
		if(isset($params['nodeShape'])){
			$nodeShapeId = $params['nodeShape']['questionId'];
			foreach($params['nodeShape']['options'] as $option){
				$nodeShapes[$option['id']] = $option['shape'];
			}
		}
        $criteria = array(
            "condition"=>"subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id,
        );
        $alter_qs = Question::model()->findAll($criteria);
        echo "<div class='form-horizontal'>";
		echo "<label class='control-label col-sm-6'>Node Shape</label>";
        echo "<div class='col-sm-6'>";
        echo CHtml::dropDownList(
                0,
                (isset($nodeShapes[0]) ? $nodeShapes[0] : ''),
                $this->nodeShapes,
                array("class"=>"form-control", "id"=>"defaultNodeShape")
            ). "</div>";

        echo "<select id='nodeShapeSelect' class='form-control' onchange='$(\".nodeShapeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";
		echo "<option value=''> Select </option>";

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeShapeId == $question->id)
				$selected = "selected";
			echo "<option value='"  . $question->id. "_nodeShape' $selected>" .$question->title.  "</option>";
		}
		echo "</select></div>";
		foreach($alter_qs as $question){
			echo "<div class='nodeShapeOptions' id='" .$question->id ."_nodeShape' style='" . ( $question->id != $nodeShapeId ? "display:none" : "") . "'>";
			$options = QuestionOption::model()->findAllByAttributes(array("questionId"=>$question->id));
			foreach($options as $option){
				echo "<div><label style='width:200px;float:left;font-size: .7em;'>". $option->name . "</label>";
				echo CHtml::dropDownList(
						$option->id,
						(isset($nodeShapes[$option->id]) ? $nodeShapes[$option->id] : ''),
						$this->nodeShapes
					). "</div>";
			}
			echo "</div>";
		}
	}

	public function actionNodesize(){
		$params = json_decode($this->params, true);
		$nodeSizeId = ''; $nodeSizes = array();
		$centralities = array("degree", "betweenness", "eigenvector");

		if(isset($params['nodeSize'])){
			$nodeSizeId = $params['nodeSize']['questionId'];
			foreach($params['nodeSize']['options'] as $option){
				$nodeSizes[$option['id']] = $option['size'];
			}
		}
        $criteria = array(
            "condition"=>"subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id,
        );
        $alter_qs = Question::model()->findAll($criteria);
	    echo "<div class='form-horizontal'>";
		echo "<label class='control-label col-sm-6'>Node Size</label>";
        echo "<div class='col-sm-6'>";
        echo CHtml::dropDownList(
                0,
                (isset($nodeSizes[0]) ? $nodeSizes[0] : ''),
                $this->nodeSizes,
                array("class"=>"form-control", "id"=>"defaultNodeSize")
            ). "</div>";

		echo "<select id='nodeSizeSelect' class='form-control' onchange='$(\".nodeSizeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";
		echo "<option value=''> Select </option>";

		foreach($centralities as $centrality){
			$selected = '';
			if($nodeSizeId == $centrality)
				$selected = "selected";
			echo "<option value='" . $centrality . "_nodeSize' $selected>" . ucfirst($centrality) . " Centrality</option>";
		}

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeSizeId == $question->id)
				$selected = "selected";
			echo "<option value='"  . $question->id. "_nodeSize' $selected>" .$question->title.  "</option>";
		}
		echo "</select></div>";
		foreach($alter_qs as $question){
			echo "<div class='nodeSizeOptions' id='" .$question->id ."_nodeSize' style='" . ( $question->id != $nodeSizeId ? "display:none" : "") . "'>";
            $options = QuestionOption::model()->findAllByAttributes(array("questionId"=>$question->id));
			foreach($options as $option){
				echo "<div><label style='width:200px;float:left;font-size: .7em;'>". $option->name . "</label>";
				echo CHtml::dropDownList(
						$option->id,
						(isset($nodeSizes[$option->id]) ? $nodeSizes[$option->id] : ''),
						$this->nodeSizes
					). "</div>";
			}
			echo "</div>";
		}

		foreach($centralities as $centrality){
			echo "<div class='nodeSizeOptions' id='" .$centrality ."_nodeSize' style='" . ($centrality != $nodeSizeId ? "display:none" : "") . "'>";
			echo "</div>";
		}
	}

	public function actionEdgecolor(){
		$params = json_decode($this->params, true);
		$edgeColorId = ''; $edgeColors = array();
		if(isset($params['edgeColor'])){
			$edgeColorId = $params['edgeColor']['questionId'];
			foreach($params['edgeColor']['options'] as $option){
				$edgeColors[$option['id']] = $option['color'];
			}
		}
        $criteria = array(
            "condition"=>"subjectType = 'ALTER_PAIR' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id,
        );
        $alter_pair_qs = Question::model()->findAll($criteria);
		echo "<div class='form-horizontal'>";
		echo "<label class='control-label col-sm-6'>Edge Color</label>";
        echo "<div class='col-sm-6'>";
        echo CHtml::dropDownList(
                0,
                (isset($edgeColors[0]) ? $edgeColors[0] : ''),
                $this->edgeColors,
                array("class"=>"form-control", "id"=>"defaultEdgeColor")
            ). "</div>";

		echo "<select id='edgeColorSelect' class='form-control' onchange='$(\".edgeColorOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";
		echo "<option value=''> Select </option>";

		foreach($alter_pair_qs as $question){
			$selected = '';
			if($edgeColorId == $question->id)
				$selected = "selected";
			echo "<option value='"  . $question->id. "_edgeColor' $selected>" .$question->title.  "</option>";
		}
		echo "</select></div>";
		foreach($alter_pair_qs as $question){
			echo "<div class='edgeColorOptions' id='" .$question->id ."_edgeColor' style='" . ( $question->id != $edgeColorId ? "display:none" : "") . "'>";
            $options = QuestionOption::model()->findAllByAttributes(array("questionId"=>$question->id));
			foreach($options as $option){
				echo "<div><label style='width:200px;float:left;font-size: .7em;'>". $option->name . "</label>";
				echo CHtml::dropDownList(
						$option->id,
						(isset($edgeColors[$option->id]) ? $edgeColors[$option->id] : ''),
						$this->edgeColors
					). "</div>";
			}
			echo "</div>";
		}
	}

	public function actionEdgesize(){
		$params = json_decode($this->params, true);
		$edgeSizeId = ''; $edgeSizes = array();
		if(isset($params['edgeSize'])){
			$edgeSizeId = $params['edgeSize']['questionId'];
			foreach($params['edgeSize']['options'] as $option){
				$edgeSizes[$option['id']] = $option['size'];
			}
		}
        $criteria = array(
            "condition"=>"subjectType = 'ALTER_PAIR' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id,
        );
        $alter_pair_qs = Question::model()->findAll($criteria);
        echo "<div class='form-horizontal'>";
		echo "<label class='control-label col-sm-6'>Edge Size</label>";
        echo "<div class='col-sm-6'>";
        echo CHtml::dropDownList(
                0,
                (isset($edgeSizes[0]) ? $edgeSizes[0] : ''),
                $this->edgeSizes,
                array("class"=>"form-control", "id"=>"defaultEdgeSize")
            ). "</div>";
		echo "<select id='edgeSizeSelect' class='form-control' onchange='$(\".edgeSizeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";
		echo "<option value=''> Select </option>";

		foreach($alter_pair_qs as $question){
			$selected = '';
			if($edgeSizeId == $question->id)
				$selected = "selected";
			echo "<option value='"  . $question->id. "_edgeSize' $selected>" .$question->title.  "</option>";
		}
		echo "</select></div>";
		foreach($alter_pair_qs as $question){

			echo "<div class='edgeSizeOptions' id='" .$question->id ."_edgeSize' style='" . ( $question->id != $edgeSizeId ? "display:none" : "") . "'>";

            $options = QuestionOption::model()->findAllByAttributes(array("questionId"=>$question->id));
			foreach($options as $option){
				echo "<div><label style='width:200px;float:left;font-size: .7em;'>". $option->name . "</label>";
				echo CHtml::dropDownList(
						$option->id,
						(isset($edgeSizes[$option->id]) ? $edgeSizes[$option->id] : ''),
						$this->edgeSizes
					). "</div>";
			}
			echo "</div>";
		}
	}

	public function actionIndex(){
		if(!$this->method || !$this->id)
			return;
		$this->params = json_decode($this->params, true);
		$graph = Graph::model()->findByAttributes(array("interviewId"=>$this->method,"expressionId"=>$this->id));
		if(!$graph)
			$graph = new Graph;
		$adjacencies = array();
		$criteria = array(
            "condition"=>"FIND_IN_SET(".$this->method .", interviewId)",
        );
        $alters = Alters::model()->findAll($criteria);
		$alterNames = array();
		$alterIds = array();
		$filterIds = array();
		foreach($alters as $alter){
			$alterIds[] = $alter->id;
			$alterNames[$alter->id] = $alter->name;
		}

        $expression = Expression::model()->findByPk($this->id);
        if(!$expression)
            return;

		$this->stats = new Statistics;
		$this->stats->initComponents($this->method, $this->id);

		$notes = Note::model()->findAllByAttributes(array("interviewId"=>$this->method, "expressionId"=>$this->id));
		$alterNotes = array();
		foreach($notes as $note){
			$alterNotes[$note->alterId] = $note->notes;
		}

		$interview = Interview::model()->findByPK($this->method);
		$study = Study::model()->findByPk($interview->studyId);

        $questionIds = array();
        $criteria = array(
            "condition"=>"subjectType = 'ALTER_PAIR' AND studyId = ". $interview->studyId,
        );
        $alter_qs = Question::model()->findAll($criteria);
		foreach($alter_qs as $alter_q){
			$questionIds[] = $alter_q->id;
		};
        $questionIds = implode(",", $questionIds);
		if(!$questionIds)
			$questionIds = 0;

		if($study->multiSessionEgoId){
            $criteria = array(
                "condition"=>"title = (SELECT title FROM question WHERE id = " . $study->multiSessionEgoId . ")",
            );
            $questions = Question::model()->findAll($criteria);
            $multiIds = array();
            foreach($questions as $question){
                $multiIds[] = $question->studyId;
            }
			$studyId = implode(",",  $multiIds);
		}else{
			$studyId = $interview->studyId;
		}

        $criteria = array(
            'condition'=>"studyId in (" . $studyId . ") AND questionId in (" . $questionIds . ")",
        );
        $alter_pair_expression = Expression::model()->findAll($criteria);
        $alter_pair_expression_ids = array();
        foreach($alter_pair_expression as $expression){
            $alter_pair_expression_ids[] = $expression->id;
        }
		$answerList = Answer::model()->findAllByAttributes(array('interviewId'=>$interview->id));
		foreach($answerList as $answer){
			if($answer->alterId1 && $answer->alterId2)
				$this->answers[$answer->questionId . "-" . $answer->alterId1 . "and" . $answer->alterId2] = $answer;
			else if ($answer->alterId1 && !$answer->alterId2)
				$this->answers[$answer->questionId . "-" . $answer->alterId1] = $answer;
			else
				$this->answers[$answer->questionId] = $answer;
		}


		if($expression->questionId)
			$expression->question = Question::model()->findByPk($expression->questionId);

		if($expression->type == "Compound"){
			$expressionIds = explode(",", $expression->value);
			foreach($expressionIds as $expressionId){
				if(in_array($expressionId, $alter_pair_expression_ids))
					$expression = Expression::model()->findByPk($expressionId);
				else
					$filterIds[] = $expressionId;
			}
			foreach($filterIds as $filterId){
				$filter = Expression::model()->findByPK($filterId);
				foreach($alters as $index=>$alter){
					if(!$filter->evalExpression($this->method, $alter->id)){
						array_splice($alters, $index, 1);
						array_splice($alterIds, $index, 1);
					}
				}
			}
		}

		$alters2 = $alters;
		$nodes = array();
		foreach($alters as $alter){
			array_push(
				$nodes,
				array(
					'id'=>$alter->id,
					'label'=>$alter->name . (isset($alterNotes[$alter->id]) ? " �" : ""),
					'x'=> rand(0, 10) / 10,
					'y'=> rand(0, 10) / 10,
					"type"=>$this->getNodeShape($alter->id),
					"color"=>$this->getNodeColor($alter->id),
					"size"=>$this->getNodeSize($alter->id),
				)
			);
			foreach($alters2 as $alter2){
				if($alter2->id == $alter->id)
					continue;
				if($expression && $expression->evalExpression($this->method, $alter->id, $alter2->id, $this->answers)){
					$edges[] = array(
						"id" => $alter->id . "_" . $alter2->id,
						"source" => $alter2->id,
						"target" => $alter->id,
						"color"=>$this->getEdgeColor($alter->id, $alter2->id),
						"size"=>$this->getEdgeSize($alter->id, $alter2->id),
					);
				}
			}
		}

		$legends = array();
		if($this->networkTitle){
            $question = Question::model()->findByAttributes(array("studyId"=>$interview->studyId, "title"=>$this->networkTitle));
			$result = Legend::model()->findAllByAttributes(array("questionId"=>$question->id));
			if($result){
				foreach($result as $legend){
					$legends[] = array(
						"shape"=>$legend->shape,
						"label"=>$legend->label,
						"color"=>$legend->color,
						"size"=>$legend->size,
					);
				}
			}
		}
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/modal.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/sigma.min.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/sigma.plugins.dragNodes.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/shape-library.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/sigma.renderers.customShapes.min.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/1.0.3/plugins/sigma.layout.forceAtlas2.min.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/www/js/plugins/sigma.notes.js');
		?>
			<div id="infovis"></div>
			<div class="col-sm-4 pull-left" id="legend">
				<canvas id="legend"></canvas>
			</div>
			<div class="col-sm-4" id="left-container"></div>
			<div class="col-sm-4 pull-right" id="right-container">
				<button  onclick="fullscreen()" class="btn btn-info print-button" disabled id="fullscreenButton">Fullscreen</button>
				<button  onclick="print(<?=$this->id;?>,<?=$this->method;?>)" class="btn btn-primary print-button" style="margin-top:10px">Print Preview</button>
                <button  onclick="toggleLabels()" class="btn btn-warning print-button" id="toggleLabelsButton" style="margin-top:10px">Toggle Labels</button>
				<?php
				if($this->networkTitle){
					$interviewIds = Interview::multiInterviewIds($this->method, $study);
					if(is_array($interviewIds)){
						$interviewIds = array_diff($interviewIds, array($this->method));
						echo "<br>Load other graphs:";
						foreach($interviewIds as $interviewId){
							$graphId = "";
                            $g_interview = Interview::model()->findByPk($interviewId);
                            $g_study =  Study::model()->findByPk($g_interview->studyId);
                            $n_question = Question::model()->findByAttributes(array("title"=>$this->networkTitle, "studyId"=>$g_study->id));
							$networkExprId = $n_question->networkRelationshipExprId;
							if($networkExprId){
                                $graph = Graph::model()->findByAttributes(array("expressionId"=>$networkExprId, "interviewId"=>$interviewId));
                                if($graph)
                                    $graphId = $graph->id;
                            }
							if($graphId)
								echo '<br><a href="#" onclick="print(' . $networkExprId . ','. $interviewId . ')">' . $study->name . '</a>';
						}
					}
				}
				$form = $this->beginWidget('CActiveForm', array(
					'id'=>'graph-form',
					'action'=>'/data/savegraph',
					'htmlOptions'=>array("class"=>"form-horizontal"),
				));?>
				<?php echo $form->hiddenField($graph,'id',array('value'=>$graph->id)); ?>
				<?php echo $form->hiddenField($graph,'interviewId',array('value'=>$this->method)); ?>
				<?php echo $form->hiddenField($graph,'expressionId',array('value'=>$this->id)); ?>
				<?php echo $form->hiddenField($graph,'nodes',array('value'=>$graph->nodes)); ?>
				<?php echo $form->hiddenField($graph,'params',array('value'=>($this->params ? json_encode($this->params) : $graph->params ))); ?>
				<button class="btn btn-danger print-button" style="margin-top:10px" onclick="redraw(resetParams());return false;">Redraw</button>
				<?php $this->endWidget(); ?>
			</div>
			<div class="modal fade" id="fullscreenModal" tabindex="-2" role="dialog" aria-labelledby="fullscreenModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title">&nbsp;</h4>
						</div>
						<div class="modal-body">
							<div id="fullscreen"></div>
						</div>
					</div>
				</div>
			</div>
		<script>
			interviewId = <?php echo $this->method; ?>;
			expressionId = <?php echo $this->id; ?>;
			notes = <?php echo json_encode($alterNotes) ?>;

			function saveNodes()
			{
				var nodes = {};
				for(var k in s.graph.nodes()){
					nodes[s.graph.nodes()[k].id] = s.graph.nodes()[k];
				}
				$("#Graph_nodes").val(JSON.stringify(nodes));
				$.post( "/data/savegraph", $('#graph-form').serialize(), function( data ) {
					console.log("nodes saved");
				});
			}

			function resetParams(container){
				var params = new Object;
				if(typeof container == "undefined")
					container = $('body');
				if($('#nodeColorSelect option:selected', container).val()){
					var nodeColor = new Object;
					var question = $('#nodeColorSelect option:selected', container).val();
					nodeColor['questionId'] = question.replace('_nodeColor','');
					nodeColor['options'] = [];
					$("#" + question + " select", container).each(function(index){
						nodeColor['options'].push({"id":$(this).attr('id'),"color":$("option:selected", this).val()});
					});
					params['nodeColor'] = nodeColor;
				}
				if($('#nodeShapeSelect option:selected', container).val()){
					var nodeShape = new Object;
					var question = $('#nodeShapeSelect option:selected', container).val();
					nodeShape['questionId'] = question.replace('_nodeShape','');
					nodeShape['options'] = [];
					$("#" + question + " select", container).each(function(index){
						nodeShape['options'].push({"id":$(this).attr('id'),"shape":$("option:selected", this).val()});
					});
					params['nodeShape'] = nodeShape;
				}
				if($('#nodeSizeSelect option:selected', container).val()){
					var nodeSize = new Object;
					var question = $('#nodeSizeSelect option:selected', container).val();
					nodeSize['questionId'] = question.replace('_nodeSize','');
					nodeSize['options'] = [];
					$( "#" + question + " select", container).each(function(index){
						nodeSize['options'].push({"id":$(this).attr('id'),"size":$("option:selected", this).val()});
					});
					params['nodeSize'] = nodeSize;
				}
				if($('#edgeColorSelect option:selected', container).val()){
					var edgeColor = new Object;
					var question = $('#edgeColorSelect option:selected', container).val();
					edgeColor['questionId'] = question.replace('_edgeColor','');
					edgeColor['options'] = [];
					$("#" + question + " select", container).each(function(index){
						edgeColor['options'].push({"id":$(this).attr('id'),"color":$("option:selected", this).val()});
					});
					params['edgeColor'] = edgeColor;
				}
				if($('#edgeSizeSelect option:selected', container).val()){
					var edgeSize = new Object;
					var question = $('#edgeSizeSelect option:selected', container).val();
					edgeSize['questionId'] = question.replace('_edgeSize','');
					edgeSize['options'] = [];
					$("#" + question + " select", container).each(function(index){
						edgeSize['options'].push({"id":$(this).attr('id'),"size":$("option:selected", this).val()});
					});
					params['edgeSize'] = edgeSize;
				}
				console.log(JSON.stringify(params));

				$("#Graph_params").val(JSON.stringify(params));
				return params;
			}

			function refresh(params){
				url = "/data/visualize?expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent(JSON.stringify(params));
				document.location = url;
			}

			function redraw(params){
				url = "/data/deleteGraph?id=" + $("#Graph_id").val();
				$.get(url, function(data){
					url = "/data/visualize?expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent(JSON.stringify(params));
					document.location = document.location + "&params=" + encodeURIComponent(JSON.stringify(params));
				});
			}

			function print(expressionId, interviewId){
				url = "/data/visualize?print&expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent($("#Graph_params").val()) + "&labelThreshold=" + s.renderers[0].settings("labelThreshold");
				window.open(url);
			}

            function toggleLabels(){
                var labelT = s.renderers[0].settings("labelThreshold");
                if(labelT == 1)
                    s.renderers[0].settings({labelThreshold:100});
                else
                    s.renderers[0].settings({labelThreshold:1});
                s.refresh();
            }

function drawLegend(){
	if(g.legends.length == 0)
		return false;
	var shapes = new Object;
	var node = new Object;
	for(var k in ShapeLibrary.enumerate()){
		shapes[ShapeLibrary.enumerate()[k].name] = ShapeLibrary.enumerate()[k];
		console.log(ShapeLibrary.enumerate()[k].name)
	}
	ctx = $("#legend")[0].getContext("2d");
	ctx.fillStyle = "white";
	ctx.strokeStyle = "gray";
	ctx.lineWidth = "5";
	ctx.strokeRect(5, 5, 120, 120);
	ctx.fillRect(5, 5, 120, 120);
	ctx.fillStyle = "black";
	// initialize placeholder "node" object
	node.cross = new Object;
	node.cross.lineWeight = 1;
	node.star = new Object;
	node.star.numPoints = 0;
	node.star.innerRatio = 0;
	node.equilateral = new Object;
	node.equilateral.numPoints = 0;
	node.equilateral.rotate = 0;
	ctx.fillText("Legend", 35, 25);
	var i = 1;
	for(var k in g.legends){
		console.log(g.legends[k].size);
		shapes[g.legends[k].shape].drawShape(node, 20,  20 + (i*30),  parseInt(g.legends[k].size) , g.legends[k].color, ctx);
		i++;
	}
	i = 1;
	ctx.fillStyle = "black";
	for(var k in g.legends){
		ctx.fillText(g.legends[k].label, 35, 25 + (i*30));
		i++;
	}
}

function fullscreen(){
	elem = document.getElementById("visualizePlugin");
	if (typeof elem.requestFullscreen != "undefined") {
		elem.requestFullscreen();
	} else if (typeof elem.msRequestFullscreen != "undefined") {
		elem.msRequestFullscreen();
	} else if (typeof elem.mozRequestFullScreen != "undefined") {
		elem.mozRequestFullScreen();
	} else if (typeof elem.webkitRequestFullscreen != "undefined") {
		elem.webkitRequestFullscreen();
	}
	$("#infovis").height(640);
    setTimeout( function (){
    document.addEventListener('webkitfullscreenchange', exitHandler, false);
    document.addEventListener('mozfullscreenchange', exitHandler, false);
    document.addEventListener('fullscreenchange', exitHandler, false);
    document.addEventListener('MSFullscreenChange', exitHandler, false);

    }, 500);

}


// transfer graph data from php to javascript
g = {
	nodes:  <?= json_encode($nodes); ?>,
	edges:  <?= json_encode($edges); ?>,
	legends:  <?= json_encode($legends); ?>
};

sizes = [];
for(y in g.nodes){sizes.push(g.nodes[y].size)}
	max_node_size = Math.max.apply(Math, sizes);

sizes = [];
for(y in g.edges){sizes.push(g.edges[y].size)}
	max_edge_size = Math.max.apply(Math, sizes);


// initialize after DOM loads
$(function(){

	$('#fullscreenModal').on('hidden', function(){
		$('#fullscreenModal .inner').remove();
	});

	sigma.renderers.def = sigma.renderers.canvas;
	s = new sigma({
		graph: g,
		renderer: {
			container: document.getElementById('infovis'),
			type: 'canvas'
		},
		settings: {
			doubleClickEnabled: false,
			labelThreshold: <?php echo (isset($_GET['labelThreshold']) ? $_GET['labelThreshold'] : 1); ?>,
			minNodeSize: 2,
			maxNodeSize: max_node_size,
			minEdgeSize: 0.5,
			maxEdgeSize: max_edge_size,
			zoomingRatio: 1.0,
			sideMargin: 2
		}
	});
	if($("#Graph_nodes").val()){
		savedNodes = JSON.parse($("#Graph_nodes").val());
		for(var k in savedNodes){
			var node = s.graph.nodes(k.toString());
			if(node){
				node.x = savedNodes[k].x;
				node.y = savedNodes[k].y;
			}
		}
		$('#fullscreenButton').prop('disabled', false);
	}else{
		s.startForceAtlas2({
			"worker":false,
			"outboundAttractionDistribution":true,
			"speed":2000,
			"gravity": 0.2,
			"jitterTolerance": 0,
			"strongGravityMode": true,
			"barnesHutOptimize": false,
			"totalSwinging": 0,
			"totalEffectiveTraction": 0,
			"complexIntervals":500,
			"simpleIntervals": 1000
		});
		setTimeout("s.stopForceAtlas2(); saveNodes(); $('#fullscreenButton').prop('disabled', false);", 5000);
	}
	s.refresh();
    initNotes(s);
	drawLegend();
});
		</script>
	<?php
	}
}
