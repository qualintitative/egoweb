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
        <?php endif;?>
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
        if($interview->hasMatches){
            $mark = "class='success'";
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
