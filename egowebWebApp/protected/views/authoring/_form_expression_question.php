<?php
$after = "";
if($question->answerType == "TEXTUAL" || $question->answerType == "TEXTUAL_PP"){
    echo CHtml::activeHiddenField($model, 'type', array('value'=>'Text'));
    echo "Expression is true for an answer that";
    $choices = array(
        'Contains'=>'Contains',
        'Equals'=>'Equals',
    );
} else if ($question->answerType == "NUMERICAL" ) {
    echo CHtml::activeHiddenField($model, 'type', array('value'=>'Number'));
    echo "Expression is true for an answer is";
    $choices = array(
        'Greater'=>'Greater',
        'GreaterOrEqual'=>'Greater Or Equal',
        'Equals'=>'Equals',
        'LessOrEqual'=>'Less Or Equal',
        'Less'=>'Less'
    );
} else {
    echo CHtml::activeHiddenField($model, 'type', array('value'=>'Selection'));
    echo "Expression is true for an answer that contains ";
    $choices = array(
        'Some'=>'Some',
        'All'=>'All',
        'None'=>'None',
    );
    $after = " of the selected options below:";

}

echo CHtml::activeDropDownList($model,
    'operator',
    $choices
);
echo $after . "<br>";
?>
<?php
    
echo CHtml::activeHiddenField($model, 'id', array('value'=>$model->id));
echo CHtml::activeHiddenField($model, 'studyId', array('value'=>$question->studyId));
echo CHtml::activeHiddenField($model, 'questionId', array('value'=>$question->id));

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
    echo CHtml::activeHiddenField($model, 'value');

}else{
    echo CHtml::activeTextField($model, 'value', array('style'=>'width:100px'));
}
// converts multiple selection checkboxes into answer value
echo "
<script>
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
</script>";
?>