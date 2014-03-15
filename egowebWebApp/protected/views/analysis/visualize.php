<?php
$interviewId = ''; $expressionId = 0; $params = ""; $graphId= "";
if(isset($_GET['interviewId']) && $_GET['interviewId'])
	$interviewId = $_GET['interviewId'];

if(isset($_GET['expressionId']) && $_GET['expressionId'])
	$expressionId = $_GET['expressionId'];

if(isset($_GET['params']) && $_GET['params'])
	$params = $_GET['params'];
if(isset($_GET['graphId']) && $_GET['graphId'])
	$graphId = $_GET['graphId'];
?>
<script>
params = [];
expressionId = <?= $expressionId ?>;
interviewId = <?= $interviewId ?>;
<?php
foreach($graphs as $graph){
	echo "params[" . $graph->id . "] = '" . $graph->params . "';";
}
?>
function getAdjacencies(newExpressionId){
	url = "/analysis/visualize?expressionId=" + newExpressionId + "&interviewId=" + interviewId;
	document.location = url;
}
function getGraph(graphId){
	if(graphId){
		url = "/analysis/visualize?expressionId=" + expressionId + "&interviewId=" + interviewId + "&graphId=" + graphId + "&params=" + encodeURIComponent(params[graphId]);
		document.location = url;
	}
}
function saveNote(){
	$.post("/analysis/savenote", $("#note-form").serialize(), function(data){
		console.log(data);
	});
}
</script>
<?php

echo "<h1>".CHtml::link(Study::getName($studyId), $this->createUrl("/analysis/study/".$studyId)) . " - " . Interview::getRespondant($interviewId)."</h1>";
?>

<form  class="form-horizontal" role="form">

  <div class="form-group col-sm-6 pull-left">

<label>Adjacency</label>
<?php
$list = array();
$list[''] = "-- select Expression --";
foreach($alter_pair_expressions as $expression){
	$list[$expression['id']] = substr($expression['name'], 0 , 30);
}
echo CHtml::dropDownList(
	'loadAdj',
	$expressionId,
	$list,
	array('onchange'=>'js:getAdjacencies($("#loadAdj option:selected").val(),'.$interviewId .')',
	)
);

?>
</div>
  <div class="form-group col-sm-6 pull-right">
<label>Load saved graph</label>

<?php
echo CHtml::dropDownList(
	'loadGraph',
	$graphId,
	CHtml::listData($graphs,'id','name'),
	array(
		'empty' => '-- SELECT --',
		'onchange'=>'js:getGraph($("option:selected", this).val(),'.$interviewId .')',
	)
);
?>
  </div>
</form>

<div class="col-sm-9 pull-right">

<?php $this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$expressionId, 'params'=>$params)); ?>
</div>
<div class="col-sm-3 pull-left">

<?php
$this->widget('plugins.visualize', array('method'=>'nodecolor', 'id'=>$interviewId, 'params'=>$params));
?><br><?php
$this->widget('plugins.visualize', array('method'=>'nodeshape', 'id'=>$interviewId, 'params'=>$params));
?><br><?php
$this->widget('plugins.visualize', array('method'=>'nodesize', 'id'=>$interviewId, 'params'=>$params));
?><br><?php
$this->widget('plugins.visualize', array('method'=>'edgecolor', 'id'=>$interviewId, 'params'=>$params));
?><br><?php
$this->widget('plugins.visualize', array('method'=>'edgesize', 'id'=>$interviewId, 'params'=>$params));
?>
<?php
if(isset($_GET['graphId']) && $_GET['graphId'])
	$graph = Graph::model()->findByPk($_GET['graphId']);
else
	$graph = new Graph;
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'graph-form',
	'action'=>'/analysis/savegraph',
	//'htmlOptions'=>array("class"=>"form-horizontal"),
));?>
<?php echo $form->hiddenField($graph,'id',array('value'=>$graph->id)); ?>
  <div class="form-group">
<?php echo $form->textField($graph,'name',array('value'=>$graph->name, 'class'=>'form-control', 'placeholder'=>'graph name')); ?>

  </div>
<?php echo $form->hiddenField($graph,'interviewId',array('value'=>$interviewId)); ?>
<?php echo $form->hiddenField($graph,'expressionId',array('value'=>$expressionId)); ?>
<?php echo $form->hiddenField($graph,'json',array('value'=>$graph->json)); ?>
<?php echo $form->hiddenField($graph,'nodes',array('value'=>$graph->nodes)); ?>
<?php echo $form->hiddenField($graph,'params',array('value'=>$params)); ?>
  <div class="form-group">

<button class="btn btn-primary" onclick="saveGraph();return false;">Save</button>

<button class="btn btn-info" onclick="reload(refresh());return false;">Refresh</button>

  </div>
<?php $this->endWidget(); ?>

</div>

<br><?php
if($interviewId && $expressionId){
/*

	$stats = new Statistics;
	$stats->initComponents($interviewId, $expressionId);

	foreach($stats->nodes as $node){
		echo $stats->names[$node] . ": degrees: ". $stats->getDegree($node). "<br>";
		echo $stats->names[$node] . ": betweenness: ". $stats->getBetweenness($node). "<br>";
		//echo $stats->names[$node] . ": closeness: ". $stats->getCloseness($node)."<br>";
		echo $stats->names[$node] . ": eigenvector: ". $stats->eigenvectorCentrality($node)."<br>";
	}
	echo "<br>";
	echo "Density:". $stats->getDensity()."<br>";
	echo "Max degree value:" .$stats->maxDegree()."<br>";
	echo "Max betweenness value:" .$stats->maxBetweenness()."<br>";
	echo "Max eigenvector value:" .$stats->maxEigenvector()."<br>";
	echo "Degree Centralization:" . $stats->degreeCentralization()."<br>";
	echo "Betweenness Centralization:" . $stats->betweennessCentralization()."<br>";
	echo "Components:".count($stats->components)."<br>";
	echo "Dyads:".count($stats->dyads)."<br>";
	echo "Isolates:".count($stats->isolates)."<br>";
	*/
}
?>
