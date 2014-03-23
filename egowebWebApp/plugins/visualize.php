<?php
class visualize extends Plugin
{
	public $params = "";
	public $edgeColors = array(
		'#07f'=>'blue',
		'#0c0'=>'green',
		'#fa0'=>'yellow',
		'#f00'=>'red',
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
		'#07f'=>'blue',
		'#0c0'=>'green',
		'#fa0'=>'yellow',
		'#f00'=>'red',
		'#c0f'=>'purple',
	);
	public $nodeShapes = array(
		'circle'=>'circle',
		'star'=>'star',
		'triangle'=>'triangle',
		'square'=>'square',
	);
	public $nodeSizes = array(
		4=>'1',
		6=>'2',
		8=>'3',
		10=>'4',
		12=>'5',
	);
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

	public $stats = "";

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
				$value = round((($value-$min) / ($range)) * 9);
				return $this->gradient[$value];
			}else if(stristr($this->params['nodeColor']['questionId'], "expression")){
				$expression = new Expression;
				list($label, $expressionId) = explode("_", $this->params['nodeColor']['questionId']);
				if($expression->evalExpression($expressionId, $this->method, $nodeId)){
					foreach($this->params['nodeColor']['options'] as $option){
						if($option['id'] == 1)
							return $option['color'];
					}
				}else{
					foreach($this->params['nodeColor']['options'] as $option){
						if($option['id'] == 0)
							return $option['color'];
					}
				}

			}else{
				$answer = q("SELECT value FROM answer WHERE questionID = ".$this->params['nodeColor']['questionId']. " AND alterId1 = " .$nodeId)->queryScalar();
				$answer = explode(',', $answer);
				foreach($this->params['nodeColor']['options'] as $option){
					if($option['id'] == $answer || in_array($option['id'], $answer))
						return $option['color'];
				}
			}
		}
		return $default;
	}

	private function getNodeShape($nodeId){
		$default = "circle";
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
		$default = 4;
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
		$centralities = array("degree", "betweenness", "eigenvector");

		if(isset($params['nodeColor'])){
			$nodeColorId = $params['nodeColor']['questionId'];
			foreach($params['nodeColor']['options'] as $option){
				$nodeColors[$option['id']] = $option['color'];
			}
		}
		$alter_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id)->queryAll();
		echo "<div class='form-group'>";
		echo "<label class='control-label'>Node Color</label>";
		echo "<select id='nodeColorSelect' class='form-control' onchange='$(\".nodeColorOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle();'>";
		echo "<option value=''> Select </option>";

		foreach($centralities as $centrality){
			$selected = '';
			if($nodeColorId == $centrality)
				$selected = "selected";
			echo "<option value='" . $centrality . "_nodeColor' $selected>" . ucfirst($centrality) . " Centrality</option>";
		}

			$questionIds = [];
			foreach($alter_qs as $alter_q){
				$questionIds[] = $alter_q['id'];
			}
			$questionIds = implode(",", $questionIds);
			if(!$questionIds)
				$questionIds = 0;
			$alter_expression_ids = q("SELECT id FROM expression WHERE studyId = " . $this->id . " AND questionId in (" . $questionIds . ")")->queryColumn();
			$all_expression_ids = $alter_expression_ids;
			foreach($alter_expression_ids as $id){
				$all_expression_ids = array_merge(q("SELECT id FROM expression WHERE FIND_IN_SET($id, value)")->queryColumn(),$all_expression_ids);
			}
			$alter_expressions = q("SELECT * FROM expression WHERE id in (" . implode(",",$all_expression_ids) . ")")->queryAll();

		foreach($alter_expressions as $expression){
			$selected = '';
			if($nodeColorId == "expression_" . $expression['id'])
				$selected = "selected";
			echo "<option value='expression_"  . $expression['id']. "_nodeColor' $selected>" .$expression['name'].  "</option>";

		}
		foreach($alter_qs as $question){
			$selected = '';
			if($nodeColorId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_nodeColor' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></div>";
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

		foreach($alter_expressions as $expression){
			echo "<div class='nodeColorOptions' id='expression_" .$expression['id'] ."_nodeColor' style='" . ( "expression_" . $expression['id'] != $nodeColorId ? "display:none" : "") . "'>";
			$options = array("false", "true");
			foreach($options as $index=>$option){
				echo "<label style='width:200px;float:left'>". $option. "</label>";
				echo CHtml::dropDownList(
					$index,
					(isset($nodeColors[$index]) ? $nodeColors[$index] : ''),
					$this->nodeColors
				). "<br>";
			}
			echo "</div>";

		}
		foreach($centralities as $centrality){
			echo "<div class='nodeColorOptions' id='" .$centrality ."_nodeColor' style='" . ($centrality != $nodeColorId ? "display:none" : "") . "'>";
			?>
			<span style="color:#00f; font-size:20px">■</span>
			<span style="color:#0ae; font-size:20px">■</span>
			<span style="color:#0cd; font-size:20px">■</span>
			<span style="color:#0dc; font-size:20px">■</span>
			<span style="color:#7ea; font-size:20px">■</span>
			<span style="color:#ae7; font-size:20px">■</span>
			<span style="color:#cd0; font-size:20px">■</span>
			<span style="color:#dc0; font-size:20px">■</span>
			<span style="color:#ea0; font-size:20px">■</span>
			<span style="color:#f00; font-size:20px">■</span>
			<?php
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
		$alter_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id)->queryAll();
		echo "<div class='form-group'>";
		echo "<label class='control-label'>Node Shape</label>";
			echo "<select id='nodeShapeSelect' class='form-control' onchange='$(\".nodeShapeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";

			echo "<option value=''> Select </option>";

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeShapeId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_nodeShape' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></div>";
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
		$alter_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id)->queryAll();
		echo "<div class='form-group'>";
		echo "<label class='control-label'>Node Size</label>";
			echo "<select id='nodeSizeSelect' class='form-control' onchange='$(\".nodeSizeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";

			echo "<option value=''> Select </option>";

		foreach($alter_qs as $question){
			$selected = '';
			if($nodeSizeId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_nodeSize' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></div>";
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
		$alter_pair_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id)->queryAll();
		echo "<div class='form-group'>";
		echo "<label class='control-label'>Edge Color</label>";
			echo "<select id='edgeColorSelect' class='form-control' onchange='$(\".edgeColorOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";

			echo "<option value=''> Select </option>";

		foreach($alter_pair_qs as $question){
			$selected = '';
			if($edgeColorId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_edgeColor' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></div>";
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
		$alter_pair_qs = q("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND answerType = 'MULTIPLE_SELECTION' AND studyId = ". $this->id)->queryAll();
		echo "<div class='form-group'>";
		echo "<label class='control-label'>Edge Size</label>";

		echo "<select id='edgeSizeSelect' class='form-control' onchange='$(\".edgeSizeOptions\").hide();$(\"#\" + $(\"option:selected\", this).val(), $(this).closest(\"#visualize-bar\")).toggle()'>";
			echo "<option value=''> Select </option>";

		foreach($alter_pair_qs as $question){
			$selected = '';
			if($edgeSizeId == $question['id'])
				$selected = "selected";
			echo "<option value='"  . $question['id']. "_edgeSize' $selected>" .$question['title'].  "</option>";
		}
		echo "</select></div>";
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
		$this->stats = new Statistics;
		$this->stats->initComponents($this->method, $this->id);

		$notes = Note::model()->findAllByAttributes(array("interviewId"=>$this->method, "expressionId"=>$this->id));
		$alterNotes = array();
		foreach($notes as $note){
			$alterNotes[$note->alterId] = $note;
		}

		$interview = Interview::model()->findByPK($this->method);

			$questionIds = q("SELECT id FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = ".$interview->studyId)->queryColumn();
			$questionIds = implode(",", $questionIds);
			if(!$questionIds)
				$questionIds = 0;
			$alter_pair_expression_ids = q("SELECT id FROM expression WHERE studyId = " . $interview->studyId . " AND questionId in (" . $questionIds . ")")->queryColumn();

		$expression = Expression::model()->findByPk($this->id);
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
					if(!$filter->evalExpression($filterId, $this->method, $alter['id'])){
						array_splice($alters, $index, 1);
						array_splice($alterIds, $index, 1);
					}
				}
			}
		}

		$currentNode = '';
		$alters2 = $alters;
		array_shift($alters2);
		//foreach($answers as $answer){
		foreach($alters as $alter){
			foreach($alters2 as $alter2){

			if($expression->evalExpression($expression->id, $this->method, $alter['id'], $alter2['id'])){
				if($currentNode != $alter['id']){
					$currentNode = $alter2['id'];
					$nodes[$currentNode]['id'] = $currentNode;
					$nodes[$currentNode]['name'] = $alterNames[$currentNode];
				}
				$nodes[$currentNode]['adjacencies'][] = array(
					'nodeTo'=>$alter['id'],
					'nodeFrom'=>$alter2['id'],
					'data'=>array(
						"\$color"=>$this->getEdgeColor($alter['id'], $alter2['id']),
						"\$lineWidth"=>$this->getEdgeSize($alter['id'], $alter2['id'])
					),
				);
			}
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
					'name'=>$node['name'] . (isset($alterNotes[$node['id']]) ? " <span class='fui-new'></span>" : ""),
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
						'name'=>$alterNames[$node]  . (isset($alterNotes[$node]) ? " <span class='fui-new'></span>" : ""),
						"data"=>array(
							"\$color"=>$this->getNodeColor($node),
							"\$type"=>$this->getNodeShape($node),
							"\$dim"=>$this->getNodeSize($node)
						)
					)
				);
			}
			$adjacencies =json_encode($json);
		}
		Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jit.js');
		Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/css/base.css');
		?>
<script>
interviewId = <?php echo $this->method; ?>;
expressionId = <?php echo $this->id; ?>;
var labelType, useGradients, nativeTextSupport, animate;
(function() {
	var ua = navigator.userAgent,
		iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
		typeOfCanvas = typeof HTMLCanvasElement,
		nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
		textSupport = nativeCanvasSupport
		&& (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
	//I'm setting this based on the fact that ExCanvas provides text support for IE
	//and that as of today iPhone/iPad current text support is lame
	labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
	nativeTextSupport = labelType == 'Native';
	useGradients = nativeCanvasSupport;
	animate = !(iStuff || !nativeCanvasSupport);
})();

var Log = {
	elem: false,
	write: function(text){
		if (!this.elem)
			this.elem = document.getElementById('log');
		this.elem.innerHTML = text;
		this.elem.style.left = (500 - this.elem.offsetWidth / 2) + 'px';
	}
};

function saveNodes()
{
	var nodes = {};
	for(var k in fd.graph.nodes){
		nodes[k] = {x:fd.graph.nodes[k].pos.x, y:fd.graph.nodes[k].pos.y};
	}
	$("#Graph_nodes").val(JSON.stringify(nodes));
}

function saveGraph(){
	if($("#Graph_name").val()){
		refresh();
		saveNodes();
		$('#graph-form').submit();
	}else{
		alert ("Please enter a graph name");
	}
}

function init(json)
{
	// init json
	if(!json)
		json = [];
	else
		$("#Graph_json").val(JSON.stringify(json));

	// init ForceDirected
	fd = new $jit.ForceDirected({
		//id of the visualization container
		injectInto: 'infovis',
		//Enable zooming and panning
		//with scrolling and DnD
		Navigation: {
			enable: true,
			type: 'Native',
			//Enable panning events only if we're dragging the empty
			//canvas (and not a node).
			panning: false,//'avoid nodes',
			zooming: false //40 //zoom speed. higher is more sensible
		},
		// Change node and edge styles such as
		// color and width.
		// These properties are also set per node
		// with dollar prefixed data-properties in the
		// JSON structure.
		Node: {
			overridable: true,
			dim: 5
		},
		Edge: {
			overridable: true,
			color: '#23A4FF',
			lineWidth: 0.5,
			epsilon: 2
		},

		//Add Tips
		Tips: {
			enable: true,
			enableForEdges: true,
			onShow: function(tip, node) {
				//count connections
				var count = 0;
				node.eachAdjacency(function() { count++; });
				//display node info in tooltip
				tip.innerHTML = "<div class=\"tip-title\">" + node.name + "</div>"
					+ "<div class=\"tip-text\"><b>connections:</b> " + count + "</div>";
			}
		},
		// Add node events
		Events: {
			enable: true,
			enableForEdges: true,
			type: 'Native',
			//Change cursor style when hovering a node
			onMouseEnter: function(node, eventInfo, e) {
				if(node){
					if(typeof node.nodeFrom == "undefined") {
						fd.canvas.getElement().style.cursor = 'move';
					}else{
						fd.canvas.getElement().style.cursor = 'crosshair';
					}
				}else{
					fd.canvas.getElement().style.cursor = 'default';
				}
			},
			onMouseMove: function(node, eventInfo, e) {
				if(node){
					if(typeof node.nodeFrom == "undefined") {
						fd.canvas.getElement().style.cursor = 'move';
					}else{
						fd.canvas.getElement().style.cursor = 'crosshair';
					}
				}else{
					fd.canvas.getElement().style.cursor = 'default';
				}
			},
			onMouseLeave: function() {
				fd.canvas.getElement().style.cursor = 'default';
			},
			//Update node positions when dragged
			onDragMove: function(node, eventInfo, e) {
				if(typeof node.nodeFrom == "undefined") {
					var pos = eventInfo.getPos();
					node.pos.setc(pos.x, pos.y);
					saveNodes();
					fd.plot();
				}
			},
			//Implement the same handler for touchscreens
			onTouchMove: function(node, eventInfo, e) {
				$jit.util.event.stop(e); //stop default touchmove event
				this.onDragMove(node, eventInfo, e);
			}
		},

		//Number of iterations for the FD algorithm
		iterations: 500,
		//Edge length
		levelDistance: 10,
		// This method is only triggered
		// on label creation and only for DOM labels (not native canvas ones).
		onCreateLabel: function(domElement, node){
			// Create a 'name' and 'close' buttons and add them
			// to the main node label
			var nameContainer = document.createElement('span'),
			closeButton = document.createElement('span'),
			style = nameContainer.style;
			nameContainer.className = 'name';
			nameContainer.innerHTML = node.name;
			closeButton.className = 'close';
			closeButton.innerHTML = '';
			domElement.appendChild(nameContainer);
			domElement.appendChild(closeButton);
			style.fontSize = "1em";
			style.color = "#111";
			//Fade the node and its connections when
			//clicking the close button
			closeButton.onclick = function() {
				node.setData('alpha', 0, 'end');
				node.eachAdjacency(function(adj) {
					adj.setData('alpha', 0, 'end');
				});
				fd.fx.animate({
					modes: ['node-property:alpha',
					'edge-property:alpha'],
					duration: 500
				});
			};
			//Toggle a node selection when clicking
			//its name. This is done by animating some
			//node styles like its dimension and the color
			//and lineWidth of its adjacencies.
			nameContainer.onclick = function() {
				//set final styles
				fd.graph.eachNode(function(n) {
					if(n.id != node.id) delete n.selected;
					//n.setData('dim', 5, 'end');
					/*n.eachAdjacency(function(adj) {
						adj.setDataset('end', {
							lineWidth: 0.4,
							color: '#23a4ff'
						});
					});*/
				});
				if(!node.selected) {
					node.selected = true;
					//node.setData('dim',15, 'end');
					/*node.eachAdjacency(function(adj) {
						adj.setDataset('end', {
							lineWidth: 2,
							color: '#36acfb'
						});
					});*/
				} else {
					delete node.selected;
				}
				$('.name').css("background-color", "transparent");
				$('.name').css("color", "#000");
				$(this).css("color", "#FFF");
				$(this).css("background-color", "#555");

				//trigger animation to final styles
				fd.fx.animate({
					modes: ['node-property:dim'/*,
						  'edge-property:lineWidth:color'*/],
					duration: 500
				});
				// Build the right column relations list.
				// This is done by traversing the clicked node connections.
				var html = "<b> connections:</b><ul><li>",
					list = [];
				node.eachAdjacency(function(adj){
					if(adj.getData('alpha')) list.push(adj.nodeTo.name.replace("<span class='fui-new'></span>",""));
				});
				//append connections information
				var url = "/analysis/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + node.id;
				$.get(url, function(data){
					$jit.id('inner-details').innerHTML = data;
					$jit.id('inner-details').innerHTML = $jit.id('inner-details').innerHTML + "<div class='pull-left col-sm-3'>" +
					"<label>Connections</label><br>" +
					list.join("</li><li>") + "</li></ul></div>"
				});
			};
		},
		// Change node styles when DOM labels are placed
		// or moved.
		onPlaceLabel: function(domElement, node){
			var style = domElement.style;
			var left = parseInt(style.left);
			var top = parseInt(style.top);
			var w = domElement.offsetWidth;
			style.left = (left - w / 2) + 'px';
			style.top = (top + 6) + 'px';
			style.display = '';
		}
	});

	// load JSON data.
	fd.loadJSON(json);
	// compute positions incrementally and animate.
	fd.computeIncremental({
		iter: 500,
		property: 'end',
		onStep: function(perc){
			Log.write(perc + '% loaded...');
		},
		onComplete: function(){
			Log.write('done');
			fd.canvas.scale(0.9,0.9);
			$('#log').hide();
			fd.animate({
				modes: ['linear'],
				transition: $jit.Trans.Elastic.easeOut,
				duration: 0,
				onComplete: function(){
					// loads saved node positions
					if($('#Graph_nodes').val()){
						nodes = fd.graph.nodes;
						nodePositions = JSON.parse($('#Graph_nodes').val());
						for (k in nodes) {
							nodes[k].pos.x = nodePositions[k].x;
							nodes[k].pos.y = nodePositions[k].y;
						}
						fd.plot();
					}
				}
			});
		}
	});
}

function refresh(container){
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

function reload(params){
	url = "/analysis/visualize?expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent(JSON.stringify(params));
	document.location = url;
}
$(function(){
	json = <?= $adjacencies; ?>;
	init(json);
});

function print(){
	params = refresh();
	url = "/analysis/visualize?print&expressionId=" + expressionId + "&interviewId=" + interviewId + "&params=" + encodeURIComponent(JSON.stringify(params));
	document.location = url;
}
function saveNote(){
	$.post("/analysis/savenote", $("#note-form").serialize(), function(data){
		$("#" + data + " .name").html($("#" + data + " .name").html() + " <span class='fui-new'></span>");
	});
}
</script>
		<div id="container">
			<div id="center-container">
				<div id="infovis"></div>
			</div>
			<div id="left-container">
				<div id="inner-details">

				</div>
			</div>
			<div id="right-container">
				<button  onclick="print()" class="btn btn-primary print-button">Export Graph</button>
			</div>
			<div id="log"></div>
		</div>
		<?php
	}

}