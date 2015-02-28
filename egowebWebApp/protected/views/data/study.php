<h2><?php echo $study->name; ?></h2>
<script>
function exportEgo(){
	$('#analysis').attr('action', '/data/exportego');
	$('#analysis').submit();
}
function exportAlterPair(){
	$('#analysis').attr('action', '/data/exportalterpair');
	$('#analysis').submit();
}
function exportOther(){
	$('#analysis').attr('action', '/data/exportother');
	$('#analysis').submit();
}
function exportAlterList(){
	$('#analysis').attr('action', '/data/exportalterlist');
	$('#analysis').submit();
}
function deleteInterviews(){
	if(confirm("Are you sure you want to DELETE these interviews?  The data will not be retrievable.")){
		$('#analysis').attr('action', '/data/deleteinterviews');
		$('#analysis').submit();
	}
}
</script>
Network Statistics
<?php echo CHtml::dropdownlist(
    'adjacencyExpressionId',
    "",
    $expressions,
    array(
    	'empty' => '(none)',
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

<?php
    echo CHtml::form('', 'post', array('id'=>'analysis'));
    echo CHtml::hiddenField('studyId', $study->id);
    foreach($interviews as $interview){
        if($interview->completed == -1)
            $completed = "<span style='color:#0B0'>". date("Y-m-d h:i:s", $interview->complete_date) . "</span>";
        else
            $completed = "";
        echo "<div class='multiRow' style='width:200px;text-align:left'>".CHtml::checkbox('export[' .$interview['id'].']'). " " . Interview::getEgoId($interview->id)."</div>";
        echo "<div class='multiRow' style='width:120px'>".date("Y-m-d h:i:s", $interview->start_date)."</div>";
        echo "<div class='multiRow' style='width:120px'>".$completed."</div>";
        echo "<div class='multiRow'>".CHtml::button('Review',array('submit'=>$this->createUrl('/interviewing/'.$study->id.'?interviewId='.$interview->id)))."</div>";
        echo "<div class='multiRow'>".CHtml::button('Visualize',array('submit'=>$this->createUrl('/data/visualize?expressionId=&interviewId='.$interview->id)))."</div>";
        echo "<br style='clear:both'>";
    }
?>
</form>
