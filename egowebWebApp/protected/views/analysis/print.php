		<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<style>
.print-button{
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
function saveNote(){
	$.post("/analysis/savenote", $("#note-form").serialize(), function(data){
		$("#" + data + " .name").html($("#" + data + " .name").html() + " <span class='fui-new'></span>");
	});
}
</script>
<?php
echo "<h3 class='margin-top-10'>" . "<small>" .Study::getName($studyId) . " &nbsp| &nbsp" . Interview::getRespondant($interviewId)."</small></h3>";
?>

<?php if($expressionId): ?>
<div class="col-sm-12 pull-left">
<?php $this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$expressionId, 'params'=>$params)); ?>
</div>

<?php endif; ?>
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
