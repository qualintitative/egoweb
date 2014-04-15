		<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/flat-ui.css" />
<style>
.print-button{
	display:none;
}
#left-container{
	display:none;
}
</style>
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
printView = true;
</script>

<div id="print-view" style="width:960px">
<?php if($expressionId): ?>
<div class="col-sm-12 pull-left">
	<?php echo "<h2 class='margin-top-10'>" .Study::getName($studyId) . " &nbsp| &nbsp" . Interview::getRespondant($interviewId)."</h2>"; ?>
</div>

<div class="col-sm-12 pull-left">
<?php $this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$expressionId, 'params'=>$params)); ?>
</div>
<div class="col-sm-12 pull-left">
<h2 class='margin-top-10'>Notes</h2>
<?php $this->widget('plugins.visualize', array('method'=>"notes", 'id'=>$expressionId, 'params'=>$interviewId)); ?>
</div>
<?php endif; ?>
</div>
<?php
/*

if($interviewId && $expressionId){

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
}
*/
?>
