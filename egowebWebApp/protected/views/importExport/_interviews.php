<?php

    foreach($interviews as $interview){
        if($interview->completed == -1)
            $completed = "<span style='color:#0B0'>COMPLETED</span>";
        else
            $completed = "INCOMPLETE";
        echo "<div class='multiRow' style='width:200px;text-align:left'>".CHtml::checkbox('export[]', false,array('value'=>$interview->id)). " " . Interview::getEgoId($interview->id)."</div>";
        echo "<div class='multiRow' style='width:120px'>".$completed."</div>";
        echo "<div class='multiRow'>".CHtml::button('Review',array('submit'=>$this->createUrl('/interviewing/'.$study->id.'?interviewId='.$interview->id)))."</div>";
        echo "<div class='multiRow'>".CHtml::button('Visualize',array('submit'=>$this->createUrl('/data/visualize?expressionId=&interviewId='.$interview->id)))."</div>";
        echo "<br style='clear:both'>";
    }
?>