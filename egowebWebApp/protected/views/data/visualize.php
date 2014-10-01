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
        params = [];
        expressionId = <?= $expressionId ?>;
        interviewId = <?= $interviewId ?>;
        function getAdjacencies(newExpressionId){
            url = "/data/visualize?expressionId=" + newExpressionId + "&interviewId=" + interviewId;
            document.location = url;
        }
    </script>
<?php
$flashMessages = Yii::app()->user->getFlashes();
if ($flashMessages) {
    foreach($flashMessages as $key => $message) {
        echo '<div class="center halfsize flash-' . $key . '">' . $message . "</div><br><br>\n";
    }
}

echo "<h3 class='margin-top-10'>".CHtml::link("Analysis &nbsp| &nbsp", $this->createUrl("/data/study/".$studyId)) . "<small>" .Study::getName($studyId) . " &nbsp| &nbsp" . Interview::getEgoId($interviewId)."</small></h3>";
?>
    <div id="expression-bar" class="col-sm-3 pull-left">
        <?php echo CHtml::form(null, null;wq, array('class'=>'form-horizontal','role'=>'form')); ?>
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
    <div class="col-sm-8 pull-right">
        <?php $this->widget('plugins.visualize', array('method'=>$interviewId, 'id'=>$expressionId, 'params'=>$params)); ?>
    </div>

    <div id="visualize-bar" class="col-sm-4 pull-left">
        <?php echo CHtml::form(null, null, array('class'=>'form-horizontal')); ?>
            <?php
            $this->widget('plugins.visualize', array('method'=>'nodecolor', 'id'=>$studyId, 'params'=>$params));
            $this->widget('plugins.visualize', array('method'=>'nodeshape', 'id'=>$studyId, 'params'=>$params));
            $this->widget('plugins.visualize', array('method'=>'nodesize', 'id'=>$studyId, 'params'=>$params));
            $this->widget('plugins.visualize', array('method'=>'edgecolor', 'id'=>$studyId, 'params'=>$params));
            $this->widget('plugins.visualize', array('method'=>'edgesize', 'id'=>$studyId, 'params'=>$params));
            ?>
        </form>

        <div class="form-group">
            <button class="btn btn-info" onclick="refresh(resetParams());return false;">Refresh</button>
        </div>

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
