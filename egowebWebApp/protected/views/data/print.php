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
@font-face {
  font-family: 'Flat-UI-Icons';
  src: url('../fonts/Flat-UI-Icons.eot');
  src: url('../fonts/Flat-UI-Icons.eot?#iefix') format('embedded-opentype'), url('../fonts/Flat-UI-Icons.woff') format('woff'), url('../fonts/Flat-UI-Icons.ttf') format('truetype'), url('../fonts/Flat-UI-Icons.svg#Flat-UI-Icons') format('svg');
  font-weight: normal;
  font-style: normal;
}
</style>
<?php
$interviewId = ''; $expressionId = 0; $params = "";
if(isset($_GET['interviewId']) && $_GET['interviewId'])
	$interviewId = $_GET['interviewId'];

if(isset($_GET['expressionId']) && $_GET['expressionId'])
	$expressionId = $_GET['expressionId'];

if(isset($_GET['params']) && $_GET['params'])
	$params = $_GET['params'];
?>
<script>
expressionId = <?= $expressionId ?>;
interviewId = <?= $interviewId ?>;

function getAdjacencies(newExpressionId){
	url = "/data/visualize?expressionId=" + newExpressionId + "&interviewId=" + interviewId;
	document.location = url;
}

printView = true;
</script>
<div id="print-view" style="width:960px">
<?php if($expressionId): ?>
<div class="col-sm-12 pull-left">
	<?php echo "<h2 class='margin-top-10'>" .Study::getName($studyId) . " &nbsp| &nbsp" . Interview::getEgoId($interviewId)."</h2>"; ?>
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
