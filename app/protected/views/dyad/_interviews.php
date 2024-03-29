<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\MatchedAlters;
use app\models\Interview;

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
        <?php if (Yii::$app->user->identity->permissions >= 3): ?>
        <th><em class="fa fa-cog"></em></th>
        <?php endif;?>
    </tr>
  </thead>
  <tbody>
<?php
    foreach ($interviews as $interview) {
        if ($interview->completed == -1) {
            $completed = "<span style='color:#0B0'>". date("Y-m-d h:i:s", $interview->complete_date) . "</span>";
        } else {
            $completed = "";
        }
        $mark = "";
        $matchId = "";
        $matchUser = "";
        $match = MatchedAlters::find()->where(["interviewId1"=>$interview->id])->orWhere(["interviewId2"=>$interview->id])->one();
        $hasMatches = $interview->hasMatches;
        if ($hasMatches) {
            if ($hasMatches == 1) {
                $mark = "class='success'";
            } else {
                $mark = "class='warning'";
            }
            if ($interview->id == $match->interviewId1) {
                $matchInt = Interview::findOne($match->interviewId2);
            } else {
                $matchInt = Interview::findOne($match->interviewId1);
            }
            $matchId = $match->getMatchId();
            if(isset($users[$match->userId]))
                $matchUser = $users[$match->userId]->name;
            else 
                $matchUser = "User Not Found";
        }
        echo "<tr $mark>";
        echo "<td>".Html::checkbox('export[' .$interview['id'].']'). "</td><td>" . $interview->egoId."</td>";
        echo "<td class='hidden-xs'>".date("Y-m-d h:i:s", $interview->start_date)."</td>";
        echo "<td class='hidden-xs'>".$completed."</td>";
        echo "<td class='hidden-xs'>".$matchId."</td>";
        echo "<td class='hidden-xs'>".$matchUser."</td>";
        if (Yii::$app->user->identity->permissions >= 3) {
            echo "<td>";
            echo Html::button('Review', array("class"=>"btn btn-xs btn-info", 'submit'=>Url::to('/interview/'.$study->id.'/'.$interview->id.'#/page/0')));
            echo "</td>";
        }
        echo "</tr>";
    }
?>
</tbody>
</table>
