<h2><?php echo $study->name; ?></h2>
<script>
function exportEgo(){
	$('#analysis').attr('action', '/analysis/exportego');
	$('#analysis').submit();
}
function exportAlterPair(){
	$('#analysis').attr('action', '/analysis/exportalterpair');
	$('#analysis').submit();
}
function exportOther(){
	$('#analysis').attr('action', '/analysis/exportother');
	$('#analysis').submit();
}
function exportAlterList(){
	$('#analysis').attr('action', '/analysis/exportalterlist');
	$('#analysis').submit();
}
function deleteInterviews(){
	if(confirm("Are you sure you want to DELETE these interviews?  The data will not be retrievable.")){
		$('#analysis').attr('action', '/analysis/deleteinterviews');
		$('#analysis').submit();
	}
}
</script>

<?php echo CHtml::dropdownlist(
    'adjacencyExpressionId',
    "",
    CHtml::listData(
    	Expression::model()->findAll($criteria),
    	'id',
    	function($post) {return CHtml::encode(substr($post->name,0,40));}
    ),
    array(
    	'empty' => 'Choose One',
		'onchange' => '$("#expressionId").val($(this).val())'
    )
);
?>
<br>
<br>
<button onclick='exportEgo()' class='authorButton'>Export Ego-Alter Data</button><br style='clear:both'>
<button onclick='exportAlterPair()' class='authorButton'>Export Alter Pair Data</button><br style='clear:both'>
<button onclick='exportOther()' class='authorButton'>Export Other Specify Data</button><br style='clear:both'>
<button onclick='exportAlterList()' class='authorButton'>Export Pre-defined Alter List</button><br style='clear:both'>
<button onclick='deleteInterviews()' class='authorButton'>Delete Interviews</button><br style='clear:both'>

<br style='clear:both'>
<a href="javascript:void(0)" onclick="$('input[type=checkbox]').prop('checked', true)">Select All</a> ::
<a href="javascript:void(0)" onclick="$('input[type=checkbox]').prop('checked', false)">De-select All</a>

<form id="analysis" method='post'>
<?php
echo CHtml::hiddenField('expressionId', $study->adjacencyExpressionId);
echo CHtml::hiddenField('studyId', $study->id);
foreach($interviews as $interview){
	if($interview['completed'] == -1)
		$completed = "<span style='color:#0B0'>COMPLETED</span>";
	else
		$completed = "INCOMPLETE";
	echo "<div class='multiRow' style='width:200px;text-align:left'>".CHtml::checkbox('export[' .$interview['id'].']').Interview::getRespondant($interview['id'])."</div>";
	echo "<div class='multiRow' style='width:120px'>".$completed."</div>";
	echo "<div class='multiRow'>".CHtml::button('Review',array('submit'=>$this->createUrl('/interviewing/'.$study->id.'?interviewId='.$interview['id'])))."</div>";
	echo "<div class='multiRow'>".CHtml::button('Visualize',array('submit'=>$this->createUrl('/analysis/visualize?expressionId=&interviewId='.$interview['id'])))."</div>";
	echo "<br style='clear:both'>";
}
?>
</form>