<script>
function getAdjacencies(expressionId, interviewId){
	url = "/analysis/visualize?expressionId=" + expressionId + "&interviewId=" +interviewId;
	document.location = url;
}
</script>
<?php
$interviewId = ''; $expressionId = ''; $params = "";
if(isset($_GET['interviewId']))
	$interviewId = $_GET['interviewId'];

if(isset($_GET['expressionId']))
	$expressionId = $_GET['expressionId'];

if(isset($_GET['params']))
	$params = $_GET['params'];

$studyId = q("SELECT studyId FROM interview WHERE id = ".$interviewId)->queryScalar();
echo "<h1>".CHtml::link(Study::getName($studyId), $this->createUrl("/analysis/study/".$studyId)) . " - " . Interview::getRespondant($interviewId)."</h1>";
?>
<h3>Adjacency
<?php
$list = array();
$list[''] = "-- select Expression --";
foreach($alter_pair_expressions as $expression){
	$list[$expression['id']] = substr($expression['name'], 0 , 30);
}
if(isset($_GET['expressionId'])){
	$selected = $_GET['expressionId'];
}else{
	$selected = '';
}
echo CHtml::dropDownList(
	'loadAdj',
	$selected,
	$list,
	array('onchange'=>'js:getAdjacencies($("#loadAdj option:selected").val(),'.$interviewId .')')
);



$this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$expressionId, 'params'=>$params));
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
<br>
<button onclick='refresh()' style="padding:3px">Refresh</button>
<br><?php
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
?>
