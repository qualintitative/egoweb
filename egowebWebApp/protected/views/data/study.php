<h2><?php echo $study->name; ?></h2>
<script>
function exportEgo(){
    var total = $("input[type='checkbox']:checked").length;
    var finished = 0;

    $(".progress-bar").width(0);
    $("input[type='checkbox']:checked").each(function(index){
        var interviewId = $(this).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        $.post(
            "/data/exportinterview",
            {studyId: $("#studyId").val(), interviewId:  interviewId, expressionId: $("#expressionId").val(), YII_CSRF_TOKEN:$("input[name='YII_CSRF_TOKEN']").val()},
            function(data){
                if(data == "success"){
                    finished++;
                    $("#status").html(
                        "Processed " + finished + " / " + total + " interviews"
                    );
                    $(".progress-bar").width((finished / total * 100) + "%");
                    if(finished == total){
                        $("#status").html("Done!");
                    	$('#analysis').attr('action', '/data/exportego');
                        $('#analysis').submit();
                    }
                }
            }
        );
    });
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
function matchAlters(){
	$('#analysis').attr('action', '/data/matching');
	$('#analysis').submit();
}
function deleteInterviews(){
	if(confirm("Are you sure you want to DELETE these interviews?  The data will not be retrievable.")){
		$('#analysis').attr('action', '/data/deleteinterviews');
		$('#analysis').submit();
	}
}
/*
$(function(){
    $("input[type='checkbox']").prop("checked", true);
});
*/
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
<div id="status"></div>
<div class="progress">
  <div class="progress-bar progress-bar-striped active" role="progressbar"
  aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
  </div>
</div>
<button onclick='exportEgo()' class='authorButton'>Export Ego-Alter Data</button><br style='clear:both'>
<button onclick='exportAlterPair()' class='authorButton'>Export Alter Pair Data</button><br style='clear:both'>
<button onclick='exportOther()' class='authorButton'>Export Other Specify Data</button><br style='clear:both'>
<button onclick='exportAlterList()' class='authorButton'>Export Pre-defined Alter List</button><br style='clear:both'>
<button onclick='matchAlters()' class='authorButton'>Match Alters</button><br style='clear:both'>
<button onclick='deleteInterviews()' class='authorButton'>Delete Interviews</button><br style='clear:both'>



<br style='clear:both'>
<a href="javascript:void(0)" onclick="$('input[type=checkbox]').prop('checked', true)">Select All</a> ::
<a href="javascript:void(0)" onclick="$('input[type=checkbox]').prop('checked', false)">De-select All</a>

<?php
    echo CHtml::form('', 'post', array('id'=>'analysis'));
    echo CHtml::hiddenField('studyId', $study->id);
    echo CHtml::hiddenField('expressionId', "");
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
