<script>
function buildValue(times, expressionIds, questionIds){
    $('#Expression_value').val(times + ':' + expressionIds + ':' + questionIds);
}
jQuery('.expressionList').change(function() {
    expressionValue = $('#Expression_value').val().split(/:/);
    expressionValue[1] = '';
     if(!expressionValue[0])
    	expressionValue[0] = 1;
    if(typeof expressionValue[2] == 'undefined')
    	expressionValue[2] = '';
    $('.expressionList').each(function() {
        if($(this).is(':checked')){
            if(expressionValue[1] != '')
                expressionValue[1] = expressionValue[1] + ',' + $(this).val();
            else
                expressionValue[1] = $(this).val();
        }
    });
    buildValue(expressionValue[0], expressionValue[1], expressionValue[2]);
    console.log($('#Expression_value').val());
});



jQuery('.questionList').change(function() {
    expressionValue = $('#Expression_value').val().split(/:/);
    if(!expressionValue[0])
    	expressionValue[0] = 1;
    if(typeof expressionValue[1] == 'undefined')
    	expressionValue[1] = '';
    expressionValue[2] = '';
    $('.questionList').each(function() {
        if($(this).is(':checked')){
            if(expressionValue[2] != '')
                expressionValue[2] = expressionValue[2] + ',' + $(this).val();
            else
                expressionValue[2] = $(this).val();
        }
    });
    buildValue(expressionValue[0], expressionValue[1], expressionValue[2]);
    console.log($('#Expression_value').val());
});

jQuery('#times').change(function() {
    expressionValue = $('#Expression_value').val().split(/:/);
    if(!$(this).val())
        $(this).val(1);
    if(typeof expressionValue[1] == 'undefined')
        expressionValue[1] = '';
    if(typeof expressionValue[2] == 'undefined')
        expressionValue[2] = '';
    expressionValue[0] = $(this).val();
    buildValue(expressionValue[0], expressionValue[1], expressionValue[2]);
    console.log($('#Expression_value').val());
});
</script>
<h4>Counting Expression</h4>
<?php
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
    "htmlOptions"=>array("class"=>"form-horizontal"),

));

echo $form->hiddenField($model, 'id', array('value'=>$model->id));
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'questionId', array('value'=>$question->id));
echo $form->hiddenField($model, 'value', array('value'=>$model->value));
echo $form->hiddenField($model, 'type', array('value'=>'Counting'));


if(strstr($model->value, ":")){
    list($times, $expressionIds, $questionIds) = explode(':', $model->value);
}else{
    $times = 1;
    $expressionIds = "";
    $questionIds = "";
}

?>
<div class="form-group">
    <?php echo $form->labelEx($model,'name', array('class'=>'control-label col-sm-2')); ?>
    <div class="col-sm-8">
        <?php echo $form->textField($model,'name', array('class'=>'form-control')); ?>
    </div>
</div>

<br clear=all>

<?php
    echo CHtml::textField('times', $times, array('id'=>'times'));
?>
           times <?php

echo $form->dropdownlist($model,
    'operator',
    array(
        'Sum'=>'Sum',
        'Count'=>'Count',
    )
);
            ?>
    	 	of the selected expressions and questions below

<br style="clear:both">
<br style="clear:both">
<span style="text-decoration:underline">Expressions</span>

<div>
<?php

$study = Study::model()->findByPk($studyId);
$criteria=new CDbCriteria;
if($study->multiSessionEgoId){
    #OK FOR SQL INJECTION
	$multiIds = q("SELECT id FROM question WHERE title = (SELECT title FROM question WHERE id = " .$study->multiSessionEgoId . ")")->queryColumn();
    #OK FOR SQL INJECTION
	$studyIds = q("SELECT id FROM study WHERE multiSessionEgoId in (" . implode(",", $multiIds) . ")")->queryColumn();
	$criteria=array(
		'condition'=>"studyId in (" . implode(",", $studyIds) . ")",
	);
} else {
	$criteria=array(
		'condition'=>"studyId = " . $studyId,
	);
}

    $selected = explode(',', $expressionIds);
    echo CHtml::CheckboxList(
        'expressionList',
        $selected,
        CHtml::listData(Expression::model()->findAll($criteria), 'id', 'name'),
        array(
            'separator'=>'<br>',
            'class'=>'expressionList',
        )
    );
?>
</div>
<br style="clear:both">
    	 	<span style="text-decoration:underline">Questions</span>
    	 	<div>
<?php
	$criteria['order'] = 'FIELD(subjectType, "EGO_ID", "EGO","ALTER", "ALTER_PAIR", "NETWORK")';
    $selected = explode(',', $questionIds);
    echo CHtml::CheckboxList(
        'questionList',
        $selected,
        CHtml::listData(Question::model()->findAll($criteria), 'id', 'title'),
        array(
            'separator'=>'<br>',
            'class'=>'questionList',
        )
    );
?>

            </div>
    	 	<br />
	        <?php

$this->endWidget();
?>
<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button>
</div>