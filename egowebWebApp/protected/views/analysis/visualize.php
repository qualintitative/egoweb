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
</script>
<?php
echo "<h3 class='margin-top-10'>".CHtml::link("Analysis &nbsp| &nbsp", $this->createUrl("/analysis/study/".$studyId)) . "<small>" .Study::getName($studyId) . " &nbsp| &nbsp" . Interview::getRespondant($interviewId)."</small></h3>";
?>

	<?php if($expressionId): ?>
	<div id="load-bar" class="col-sm-3 pull-right">
		<form  class="form-horizontal" role="form">
			<div class="form-group">
		<?php
		echo CHtml::dropDownList(
			'loadGraph',
			$graphId,
			CHtml::listData($graphs,'id','name'),
			array(
				'empty' => 'Load saved graphs',
				'onchange'=>'js:getGraph($("option:selected", this).val(),'.$interviewId .')',
				'class'=>'form-control'
			)
		);
		?>
			</div>
		</form>
	</div>
	<?php endif; ?>

	<div id="expression-bar" class="col-sm-3 pull-left">
		<form  class="form-horizontal" role="form">
			<div class="form-group">
				<label class="control-label">Adjacency</label>
				<?php
				$list = array();
				foreach($alter_pair_expressions as $expression){
					$list[$expression['id']] = substr($expression['name'], 0 , 30);
				}
				echo CHtml::dropDownList(
					'loadAdj',
					$expressionId,
					$list,
					array(
						'empty' => 'Select',
						'onchange'=>'js:getAdjacencies($("#loadAdj option:selected").val(),'.$interviewId .')',
						'class'=>'form-control'
					)
				);
				?>
			</div>
		</form>
	</div>




<?php if($expressionId): ?>
<div class="col-sm-9 pull-right">
<?php $this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$expressionId, 'params'=>$params)); ?>
</div>

<div id="visualize-bar" class="col-sm-3 pull-left">
	<form class="form-horizontal">
		<?php
		$this->widget('plugins.visualize', array('method'=>'nodecolor', 'id'=>$studyId, 'params'=>$params));
		$this->widget('plugins.visualize', array('method'=>'nodeshape', 'id'=>$studyId, 'params'=>$params));
		$this->widget('plugins.visualize', array('method'=>'nodesize', 'id'=>$studyId, 'params'=>$params));
		$this->widget('plugins.visualize', array('method'=>'edgecolor', 'id'=>$studyId, 'params'=>$params));
		$this->widget('plugins.visualize', array('method'=>'edgesize', 'id'=>$studyId, 'params'=>$params));
		?>
	</form>
	<?php
	if(isset($_GET['graphId']) && $_GET['graphId'])
		$graph = Graph::model()->findByPk($_GET['graphId']);
	else
		$graph = new Graph;
	$form  =$this->beginWidget('CActiveForm', array(
		'id'=>'graph-form',
		'action'=>'/analysis/savegraph',
		'htmlOptions'=>array("class"=>"form-horizontal"),
	));?>
		<?php echo $form->hiddenField($graph,'id',array('value'=>$graph->id)); ?>
		<?php echo $form->hiddenField($graph,'interviewId',array('value'=>$interviewId)); ?>
		<?php echo $form->hiddenField($graph,'expressionId',array('value'=>$expressionId)); ?>
		<?php echo $form->hiddenField($graph,'json',array('value'=>$graph->json)); ?>
		<?php echo $form->hiddenField($graph,'nodes',array('value'=>$graph->nodes)); ?>
		<?php echo $form->hiddenField($graph,'params',array('value'=>$params)); ?>
		<div class="form-group">
			<label>Graph Name</label>
			<?php echo $form->textField($graph,'name',array('value'=>$graph->name, 'class'=>'form-control', 'placeholder'=>'graph name')); ?>
		</div>
		<div class="form-group">
			<button class="btn btn-primary" onclick="saveGraph();return false;">Save</button>
			<button class="btn btn-info" onclick="reload(refresh());return false;">Refresh</button>
		</div>
	<?php $this->endWidget(); ?>
	<br><br>
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
