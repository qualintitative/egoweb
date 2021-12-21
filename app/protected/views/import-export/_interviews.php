<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>
<a href="javascript:void(0)" onclick="$('input[type=checkbox]').prop('checked', true)">Select All</a> ::
<a href="javascript:void(0)" onclick="$('input[type=checkbox]').prop('checked', false)">De-select All</a>
Include Response Data<br>
<?php

    foreach ($interviews as $interview) {
        if ($interview->completed == -1) {
            $completed = "<span style='color:#0B0'>COMPLETED</span>";
        } else {
            $completed = "INCOMPLETE";
        }
        echo "<div class='multiRow' style='width:200px;text-align:left'>".Html::checkbox('export[]', false, array('class'=>"export",'value'=>$interview->id, "id"=>"export-$interview->id,")). " " . $interview->egoId."</div>";
        echo "<div class='multiRow' style='width:120px'>".$completed."</div>";
        echo "<div class='multiRow'>".Html::button('Review', array("class"=>"btn btn-xs btn-info",'onclick'=>"document.location='".Url::to('/interview/'.$study->id.'/'.$interview->id . "#/page/0'")))."</div>";
        echo "<br style='clear:both'>";
    }
?>
<br clear="all">
