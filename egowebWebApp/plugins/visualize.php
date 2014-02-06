<?php
class visualize extends Plugin
{
	public $params = "";
	public $edgeColors = array(
		'#f00'=>'red',
		'#fa0'=>'yellow',
		'#0c0'=>'green',
		'#07f'=>'blue',
		'#c0f'=>'purple',
	);
	public $edgeSizes = array(
		"0.5"=>'1',
		"1.0"=>'2',
		"1.5"=>'3',
		"2.0"=>'4',
		"2.5"=>'5',
	);
	public $nodeColors = array(
		'#f00'=>'red',
		'#fa0'=>'yellow',
		'#0c0'=>'green',
		'#07f'=>'blue',
		'#c0f'=>'purple',
	);
	public $nodeShapes = array(
		'star'=>'star',
		'circle'=>'circle',
		'triangle'=>'triangle',
		'square'=>'square',
	);
	public $nodeSizes = array(
		5=>'1',
		10=>'2',
		15=>'3',
		20=>'4',
		25=>'5',
	);

	private function getNodeColor($nodeId){
		$default = "#07f";
		if(isset($this->params['nodeColor'])){
			$answer = q("SELECT value FROM answer WHERE questionID = ".$this->params['nodeColor']['questionId']. " AND alterId1 = " .$nodeId)->queryScalar();
			$answer = explode(',', $answer);
			foreach($this->params['nodeColor']['options'] as $option){
				if($option['id'] == $answer || in_array($option['id'], $answer))
					return $option['color'];
			}
		}
		return $default;
	}

	private function getNodeShape($nodeId){
		$default = "star";
		if(isset($this->params['nodeShape'])){
			$answer = q("SELECT value FROM answer WHERE questionID = ".$this->params['nodeShape']['questionId']. " AND alterId1 = " .$nodeId)->queryScalar();
			$answer = explode(',', $answer);
			foreach($this->params['nodeShape']['options'] as $option){
				if($option['id'] == $answer || in_array($option['id'], $answer))
					return $option['shape'];
			}
		}
		return $default;
	}


	private function getNodeSize($nodeId){
		$default = 5;
		if(isset($this->params['nodeSize'])){
			$answer = q("SELECT value FROM answer WHERE questionID = ".$this->params['nodeSize']['questionId']. " AND alterId1 = " .$nodeId)->queryScalar();
			$answer = explode(',', $answer);
			foreach($this->params['nodeSize']['options'] as $option){
				if($option['id'] == $answer || in_array($option['id'], $answer))
					$default = intval($option['size']);
			}
		}
		return $default;
	}

	private function getEdgeColor($nodeId1, $nodeId2){
		$default = "#07f";
		if(isset($this->params['edgeColor'])){
			$answer = q("SELECT value FROM answer WHERE questionID = ".$this->params['edgeColor']['questionId']. " AND alterId1 = " .$nodeId1 . " AND alterId2 = " . $nodeId2)->queryScalar();
			$answer = explode(',', $answer);
			foreach($this->params['edgeColor']['options'] as $option){
				if($option['id'] == $answer || in_array($option['id'], $answer))
					return $option['color'];
			}
		}
		return $default;
	}

	private function getEdgeSize($nodeId1, $nodeId2){
		$default = 0.5;
		if(isset($this->params['edgeSize'])){
			$answer = q("SELECT value FROM answer WHERE questionID = ".$this->params['edgeSize']['questionId']. " AND alterId1 = " .$nodeId1. " AND alterId2 = " . $nodeId2)->queryScalar();
			$answer = explode(',', $answer);
			foreach($this->params['edgeSize']['options'] as $option){
				if($option['id'] == $answer || in_array($option['id'], $answer))
					$default = floatval($option['size']);
			}
		}
		return $default;
	}

	public function actionNodecolor(){
		$params = json_decode($this->params, true);
		$nodeColorId = ''; $nodeColors = array();
		if(isset($params['nodeColor'])){
			$nodeColorId = $params['nodeColor']['questionId'];
			foreach($params['nodeColor']['options'] as $option){
				$nodeColors[$option['id']] = $option['color'];
			}
		}
		$interview = Interview::model()->findByPk($this->id);
		$alter_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $interview->studyId)->queryAll();
		echo "<h3>Node Color";
			echo "<select id='nodeColorSelect' style='margin-left:20px' onchange='$(\".nodeColorOptions\").hide();$(\"#\" + $(\"option:selected\", this).val()).toggle()'>";

			echo "<option value=''> -- SELECT -- </option>";

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeColorId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_nodeColor' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></h3>";
		foreach($alter_qs as $question){

			echo "<div class='nodeColorOptions' id='" .$question['id'] ."_nodeColor' style='" . ( $question['id'] != $nodeColorId ? "display:none" : "") . "'>";

			$options = q("SELECT * FROM questionOption WHERE questionId = ".$question['id'])->queryAll();
			foreach($options as $option){
				echo "<label style='width:200px;float:left'>". $option['name'] . "</label>";
				echo CHtml::dropDownList(
					$option['id'],
					(isset($nodeColors[$option['id']]) ? $nodeColors[$option['id']] : ''),
					$this->nodeColors
				). "<br>";
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
		$interview = Interview::model()->findByPk($this->id);
		$alter_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $interview->studyId)->queryAll();
		echo "<h3>Node Shape";
			echo "<select id='nodeShapeSelect' style='margin-left:20px' onchange='$(\".nodeShapeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val()).toggle()'>";

			echo "<option value=''> -- SELECT -- </option>";

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeShapeId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_nodeShape' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></h3>";
		foreach($alter_qs as $question){

			echo "<div class='nodeShapeOptions' id='" .$question['id'] ."_nodeShape' style='" . ( $question['id'] != $nodeShapeId ? "display:none" : "") . "'>";

			$options = q("SELECT * FROM questionOption WHERE questionId = ".$question['id'])->queryAll();
			foreach($options as $option){
				echo "<label style='width:200px;float:left'>". $option['name'] . "</label>";
				echo CHtml::dropDownList(
					$option['id'],
					(isset($nodeShapes[$option['id']]) ? $nodeShapes[$option['id']] : ''),
					$this->nodeShapes
				). "<br>";
			}
			echo "</div>";
		}
	}

	public function actionNodesize(){
		$params = json_decode($this->params, true);
		$nodeSizeId = ''; $nodeSizes = array();
		if(isset($params['nodeSize'])){
			$nodeSizeId = $params['nodeSize']['questionId'];
			foreach($params['nodeSize']['options'] as $option){
				$nodeSizes[$option['id']] = $option['size'];
			}
		}
		$interview = Interview::model()->findByPk($this->id);
		$alter_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $interview->studyId)->queryAll();
		echo "<h3>Node Size";
			echo "<select id='nodeSizeSelect' style='margin-left:20px' onchange='$(\".nodeSizeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val()).toggle()'>";

			echo "<option value=''> -- SELECT -- </option>";

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeSizeId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_nodeSize' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></h3>";
		foreach($alter_qs as $question){

			echo "<div class='nodeSizeOptions' id='" .$question['id'] ."_nodeSize' style='" . ( $question['id'] != $nodeSizeId ? "display:none" : "") . "'>";

			$options = q("SELECT * FROM questionOption WHERE questionId = ".$question['id'])->queryAll();
			foreach($options as $option){
				echo "<label style='width:200px;float:left'>". $option['name'] . "</label>";
				echo CHtml::dropDownList(
					$option['id'],
					(isset($nodeSizes[$option['id']]) ? $nodeSizes[$option['id']] : ''),
					$this->nodeSizes
				). "<br>";
			}
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
		$interview = Interview::model()->findByPk($this->id);
		$alter_pair_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $interview->studyId)->queryAll();
		echo "<h3>Edge Color";
			echo "<select id='edgeColorSelect' style='margin-left:20px' onchange='$(\".edgeColorOptions\").hide();$(\"#\" + $(\"option:selected\", this).val()).toggle()'>";

			echo "<option value=''> -- SELECT -- </option>";

		foreach($alter_pair_qs as $question){
			$selected = '';
			if($edgeColorId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_edgeColor' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></h3>";
		foreach($alter_pair_qs as $question){

			echo "<div class='edgeColorOptions' id='" .$question['id'] ."_edgeColor' style='" . ( $question['id'] != $edgeColorId ? "display:none" : "") . "'>";

			$options = q("SELECT * FROM questionOption WHERE questionId = ".$question['id'])->queryAll();
			foreach($options as $option){
				echo "<label style='width:200px;float:left'>". $option['name'] . "</label>";
				echo CHtml::dropDownList(
					$option['id'],
					(isset($edgeColors[$option['id']]) ? $edgeColors[$option['id']] : ''),
					$this->edgeColors
				). "<br>";
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
		$interview = Interview::model()->findByPk($this->id);
		$alter_pair_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $interview->studyId)->queryAll();
		echo "<h3>Edge Size";
			echo "<select id='edgeSizeSelect' style='margin-left:20px' onchange='$(\".edgeSizeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val()).toggle()'>";

			echo "<option value=''> -- SELECT -- </option>";

		foreach($alter_pair_qs as $question){
			$selected = '';
			if($edgeSizeId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_edgeSize' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></h3>";
		foreach($alter_pair_qs as $question){

			echo "<div class='edgeSizeOptions' id='" .$question['id'] ."_edgeSize' style='" . ( $question['id'] != $edgeSizeId ? "display:none" : "") . "'>";

			$options = q("SELECT * FROM questionOption WHERE questionId = ".$question['id'])->queryAll();
			foreach($options as $option){
				echo "<label style='width:200px;float:left'>". $option['name'] . "</label>";
				echo CHtml::dropDownList(
					$option['id'],
					(isset($edgeSizes[$option['id']]) ? $edgeSizes[$option['id']] : ''),
					$this->edgeSizes
				). "<br>";
			}
			echo "</div>";
		}
	}

	public function actionIndex(){
		if(!$this->method || !$this->id)
			return;
		$this->params = json_decode($this->params, true);
		$adjacencies = array();
		$nodes = array();
		$alters = q("SELECT * FROM alters WHERE interviewId = ".$this->method)->queryAll();
		$alterNames = array();
		$alterIds = array();
		foreach($alters as $alter){
			$alterIds[] = $alter['id'];
			$alterNames[$alter['id']] = $alter['name'];
		}
		$expression = Expression::model()->findByPk($this->id);
		if($expression->questionId)
			$answers = q("SELECT * FROM answer WHERE interviewId = ". $this->method . " AND questionId = ". $expression->questionId . " ORDER BY alterId1, alterId2")->queryAll();
		else
			$answers = array();
		//print_r($answers);
		$currentNode = '';
		foreach($answers as $answer){
			if($expression->evalExpression($expression->id, $this->method, $answer['alterId1'], $answer['alterId2'])){
				if($currentNode != $answer['alterId1']){
					$currentNode = $answer['alterId1'];
					$nodes[$currentNode]['id'] = $currentNode;
					$nodes[$currentNode]['name'] = $alterNames[$currentNode];
				}
				$nodes[$currentNode]['adjacencies'][] = array(
					'nodeTo'=>$answer['alterId2'],
					'nodeFrom'=>$answer['alterId1'],
					'data'=>array(
						"\$color"=>$this->getEdgeColor($answer['alterId1'], $answer['alterId2']),
						"\$lineWidth"=>$this->getEdgeSize($answer['alterId1'], $answer['alterId2'])
					),
				);
			}
		}
		$json = array();
		foreach($nodes as $node){
			if(!isset($node['id']))
				continue;
			$nodeArray[] = $node['id'];
			array_push(
				$json,
				array(
					'adjacencies'=>$node['adjacencies'],
					'id'=>$node['id'],
					'name'=>$node['name'],
					"data"=>array(
						"\$color"=>$this->getNodeColor($node['id']),
						"\$type"=>$this->getNodeShape($node['id']),
						"\$dim"=>$this->getNodeSize($node['id'])
					)
				)
			);
		}
		if(isset($nodeArray)){
			$leftOvers = array_diff($alterIds, $nodeArray);

			foreach ($leftOvers as $node){
				array_push(
					$json,
					array(
						'adjacencies'=>array(),
						'id'=>$node,
						'name'=>$alterNames[$node],
						"data"=>array(
							"\$color"=>$this->getNodeColor($node),
							"\$type"=>$this->getNodeShape($node),
							"\$dim"=>$this->getNodeSize($node)
						)
					)
				);
			}

			//print_r($json);
			$adjacencies =json_encode($json);
		}

		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jit.js');
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/visualize.js');
		//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/ForceDirected.css');
		//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/base.css');

		Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/css/base.css');

		echo '
		<script>
		$(function(){
			json = '. $adjacencies .';
			init(json);
		});
		</script>
		<div id="container">
			<div id="center-container">
				<div id="infovis"></div>
			</div>
			<div id="left-container">
				<div id="inner-details"></div>
			</div>
			<div id="log"></div>
		</div>';
		?>
		<script>
		interviewId = <?php echo $this->method; ?>;
		expressionId = <?php echo $this->id; ?>;

		function refresh(questionId, type){
			var params = new Object;
			if($('option:selected', '#nodeColorSelect').val()){
				var nodeColor = new Object;
				var question = $('option:selected', '#nodeColorSelect').val();
				nodeColor['questionId'] = question.replace('_nodeColor','');
				nodeColor['options'] = [];
				$("select", "#" + question).each(function(index){
					nodeColor['options'].push({"id":$(this).attr('id'),"color":$("option:selected", this).val()});
				});
				params['nodeColor'] = nodeColor;
			}
			if($('option:selected', '#nodeShapeSelect').val()){
				var nodeShape = new Object;
				var question = $('option:selected', '#nodeShapeSelect').val();
				nodeShape['questionId'] = question.replace('_nodeShape','');
				nodeShape['options'] = [];
				$("select", "#" + question).each(function(index){
					nodeShape['options'].push({"id":$(this).attr('id'),"shape":$("option:selected", this).val()});
				});
				params['nodeShape'] = nodeShape;
			}
			if($('option:selected', '#nodeSizeSelect').val()){
				var nodeSize = new Object;
				var question = $('option:selected', '#nodeSizeSelect').val();
				nodeSize['questionId'] = question.replace('_nodeSize','');
				nodeSize['options'] = [];
				$("select", "#" + question).each(function(index){
					nodeSize['options'].push({"id":$(this).attr('id'),"size":$("option:selected", this).val()});
				});
				params['nodeSize'] = nodeSize;
			}
			if($('option:selected', '#edgeColorSelect').val()){
				var edgeColor = new Object;
				var question = $('option:selected', '#edgeColorSelect').val();
				edgeColor['questionId'] = question.replace('_edgeColor','');
				edgeColor['options'] = [];
				$("select", "#" + question).each(function(index){
					edgeColor['options'].push({"id":$(this).attr('id'),"color":$("option:selected", this).val()});
				});
				params['edgeColor'] = edgeColor;
			}
			if($('option:selected', '#edgeSizeSelect').val()){
				var edgeSize = new Object;
				var question = $('option:selected', '#edgeSizeSelect').val();
				edgeSize['questionId'] = question.replace('_edgeSize','');
				edgeSize['options'] = [];
				$("select", "#" + question).each(function(index){
					edgeSize['options'].push({"id":$(this).attr('id'),"size":$("option:selected", this).val()});
				});
				params['edgeSize'] = edgeSize;
			}
			console.log(JSON.stringify(params));
			url = "/analysis/visualize?expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent(JSON.stringify(params));
			document.location = url;
		}
		</script>
		<?php

	}

}