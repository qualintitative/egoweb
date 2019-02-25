<h2><?php echo $study->name; ?></h2>
<script>
function exportEgo(){
    var total = $("input[type='checkbox'][name*='export']:checked").length;
    var finished = 0;

    $(".progress-bar").width(0);
    $("input[type='checkbox']:checked").each(function(index){
        if(!$(this).attr("id"))
            return true;
        var interviewId = $(this).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        $.post(
            "/data/exportegoalter",
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
                    	$('#analysis').attr('action', '/data/exportegoalterall');
                        $('#analysis').submit();
                    }
                }
            }
        );
    });
}
function exportAlterPair(){
    var total = $("input[type='checkbox'][name*='export']:checked").length;
    var finished = 0;

    $(".progress-bar").width(0);
    $("input[type='checkbox']:checked").each(function(index){
        if(!$(this).attr("id"))
            return true;
        var interviewId = $(this).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        $.post(
            "/data/exportalterpair",
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
                    	$('#analysis').attr('action', '/data/exportalterpairall');
                        $('#analysis').submit();
                    }
                }
            }
        );
    });
}
function exportOther(){
	$('#analysis').attr('action', '/data/exportother');
	$('#analysis').submit();
}
function exportOtherLegacy(){
	$('#analysis').attr('action', '/data/legacyexportother');
	$('#analysis').submit();
}
function exportAlterList(){
	$('#analysis').attr('action', '/data/exportalterlist');
	$('#analysis').submit();
}
function matchAlters(){
	$('#analysis').attr('action', '/dyad/matching');
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

<button onclick='matchAlters()' class='authorButton'>Dyad Match</button><br style='clear:both'>



<br style='clear:both'>
<?php
    echo CHtml::form('', 'post', array('id'=>'analysis'));
    echo CHtml::form('', 'post', array('id'=>'analysis'));
    echo CHtml::hiddenField('studyId', $study->id);
    echo CHtml::hiddenField('expressionId', "");
?>
                <table class="table table-striped table-bordered table-list">
                  <thead>
                    <tr>
                        <th><input type="checkbox" onclick="$('input[type=checkbox]').prop('checked', $(this).prop('checked'))" data-toggle="tooltip" data-placement="top" title="Select All"></th>
                        <th>Ego ID</th>
                        <th class="hidden-xs">Started</th>
                        <th class="hidden-xs">Completed</th>
                        <th class="hidden-xs">Dyad Match ID</th>
                        <th class="hidden-xs">Match User</th>
                        <?php if(Yii::app()->user->user->permissions >= 3): ?>
                        <th><em class="fa fa-cog"></em></th>
                        <?php endif; ?>
                    </tr>
                  </thead>
                  <tbody>

<?php

    foreach($interviews as $interview){
        if($interview->completed == -1)
            $completed = "<span style='color:#0B0'>". date("Y-m-d h:i:s", $interview->complete_date) . "</span>";
        else
            $completed = "";
        $mark = "";
        $matchId = "";
        $matchUser = "";
        $hasMatches = $interview->hasMatches;
        if($hasMatches){
            if($hasMatches == 1)
              $mark = "class='success'";
            else
              $mark = "class='warning'";
        		$criteria = array(
        			'condition'=>"interviewId1 = $interview->id OR interviewId2 = $interview->id ORDER BY id DESC",
        		);
    		    $match = MatchedAlters::model()->find($criteria);
            if($interview->id == $match->interviewId1)
                $matchInt = Interview::model()->findByPk($match->interviewId2);
            else
                $matchInt = Interview::model()->findByPk($match->interviewId1);
            $matchId = $match->getMatchId();
            $matchUser = User::getName($match->userId);
        }
        echo "<tr $mark>";
        echo "<td>".CHtml::checkbox('export[' .$interview['id'].']'). "</td><td>" . Interview::getEgoId($interview->id)."</td>";
        echo "<td class='hidden-xs'>".date("Y-m-d h:i:s", $interview->start_date)."</td>";
        echo "<td class='hidden-xs'>".$completed."</td>";
        echo "<td class='hidden-xs'>".$matchId."</td>";
        echo "<td class='hidden-xs'>".$matchUser."</td>";
        if(Yii::app()->user->user->permissions >= 3){
            echo "<td>";
            if($interview->completed == -1)
                echo CHtml::button('Edit',array('submit'=>$this->createUrl('/data/edit/' . $interview->id)));

            echo CHtml::button('Review',array('submit'=>$this->createUrl('/interview/'.$study->id.'/'.$interview->id.'/#/page/0')));
            echo CHtml::button('Visualize',array('submit'=>$this->createUrl('/data/visualize?expressionId=&interviewId='.$interview->id)))."</td>";
        }
        echo "</tr>";
    }
?>
                        </tbody>
                </table>

</form>
