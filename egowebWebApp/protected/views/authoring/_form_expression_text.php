<span>Expression about <?php echo Question::getTitle($question->id); ?></span>
<?php
// text expression form
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-text-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
));
// converts multiple selection checkboxes into answer value
Yii::app()->clientScript->registerScript('optionsToValue', "
jQuery('.valueList').change(function() {
    $('#Expression_value').val('');
    $('.valueList').each(function() {
        if($(this).is(':checked')){
            if($('#Expression_value').val() != '')
                $('#Expression_value').val($('#Expression_value').val() + ',' + $(this).val());
            else
                $('#Expression_value').val($(this).val());
        }
    });
    console.log($('#Expression_value').val());
});
");
?>

<?php echo $form->labelEx($model,'name'); ?>
<?php echo $form->textField($model,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'name'); ?>

<br clear=all>

<?php
$after = "";
if($question->answerType == "TEXTUAL" || $question->answerType == "TEXTUAL_PP"){
    echo $form->hiddenField($model, 'type', array('value'=>'Text'));
    echo "Expression is true for an answer that";
    $choices = array(
        'Contains'=>'Contains',
        'Equals'=>'Equals',
    );
} else if ($question->answerType == "NUMERICAL" ) {
    echo $form->hiddenField($model, 'type', array('value'=>'Number'));
    echo "Expression is true for an answer is";
    $choices = array(
        'Greater'=>'Greater',
        'GreaterOrEqual'=>'Greater Or Equal',
        'Equals'=>'Equals',
        'LessOrEqual'=>'Less Or Equal',
        'Less'=>'Less'
    );
} else {
    echo $form->hiddenField($model, 'type', array('value'=>'Selection'));
    echo "Expression is true for an answer that contains ";
    $choices = array(
        'Some'=>'Some',
        'All'=>'All',
        'None'=>'None',
    );
    $after = " of the selected options below:";

}
echo $form->dropdownlist($model,
    'operator',
    $choices
);
echo $after . "<br>";
?>
<?php echo $form->error($model,'operator'); ?>

<?php
echo $form->hiddenField($model, 'id', array('value'=>$model->id)); 
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'questionId', array('value'=>$question->id));


if($after != ""){
    $selected = explode(',', $model->value);
    echo CHtml::CheckboxList(
        'valueList',
        $selected,
        CHtml::listData(QuestionOption::model()->findAllByAttributes(array('questionId'=>$question->id)), 'id', 'name'),
        array(
            'separator'=>'<br>',
            'class'=>'valueList',
        )
    );
    echo $form->hiddenField($model, 'value', array('value'=>$model->value)); 

}else{
    echo $form->textField($model, 'value', array('style'=>'width:100px')); 
}
?>

<br />

Expression is
<?php 
echo $form->dropdownlist($model,
    'resultForUnanswered',
    array(
        '0'=>"False",
        "1"=>"True"
    )
);
?>
if the question is unanswered.

<br clear=all />
<input type="submit" value="Save"/>
<?php $this->endWidget(); ?>
<button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})">delete</button>