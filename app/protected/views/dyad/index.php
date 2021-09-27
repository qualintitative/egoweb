<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

?>
<script>
function getInterviews(dropdown, container){
	$.get('/dyad/ajaxinterviews/' + dropdown.val(), function(data){
    $("#sendError").hide();
    $("#sendNotice").hide();
    $(container).html(data);
  });
}
</script>
<div class="card">
<div class="card-body">
<?php
    echo Html::beginForm('dyad/matching', 'post', array('id'=>'analysis'));
    echo Html::input('hidden', 'studyId', '');
    echo Html::input('hidden', 'expressionId', "");
?>
<?php

echo Html::dropdownlist(
	'studyId',
	'',
    ArrayHelper::map(
        $studies
    , 'id','name'),
    array(
        'prompt' => 'Select Study',
        'onchange'=>"js:getInterviews(\$(this), '#interview-1')",
        'class'=>'form-control'
    )

);
?>
<div id="interview-1"></div>
<br>
<?php

echo Html::dropdownlist(
	'studyId',
	'',
    ArrayHelper::map(
        $studies
    , 'id','name'),
    array(
        'prompt' => 'Select Different Study',
        'onchange'=>"js:getInterviews(\$(this), '#interview-2')",
        'class'=>'form-control'
    )

);
?>
<div id="interview-2"></div>
</div>
</div>
<div class="card">
<div class="card-body">
<button class='btn btn-primary'>Dyad Match</button><br style='clear:both'>
</div>
</div>
</form>
</div>
</div>
