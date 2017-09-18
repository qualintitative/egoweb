<?php
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'expression-form',
    'enableAjaxValidation'=>false,
    'action'=>'/authoring/expression/'.$studyId,
    "htmlOptions"=>array("class"=>"form-horizontal"),

));
echo $form->hiddenField($model, 'id', array('value'=>$model->id));
echo $form->hiddenField($model, 'studyId', array('value'=>$studyId));
echo $form->hiddenField($model, 'value', array('value'=>$model->value));
echo $form->hiddenField($model, 'type', array('value'=>'Name Generator'));

?>
<div class="form-group">
    <?php echo $form->labelEx($model,'name', array('class'=>'control-label col-sm-2')); ?>
    <div class="col-sm-8">
        <?php echo $form->textField($model,'name', array('value'=>$model->name, 'class'=>'form-control')); ?>
    </div>
</div>
<?php
$after = "";
    echo "Expression is true for an answer that contains ";
    $choices = array(
         'All'=>'All',
        'None'=>'None',
    );
    $after = " of the selected options below:";


echo CHtml::activeDropDownList($model,
    'operator',
    $choices
);
echo $after . "<br>";
?>
<?php

echo CHtml::activeHiddenField($model, 'questionId', array('value'=>$question->id));

if($after != ""){
    $selected = explode(',', $model->value);
    echo CHtml::CheckboxList(
        'valueList',
        $selected,
        CHtml::listData(Question::model()->findAllByAttributes(array('subjectType'=>"NAME_GENERATOR", "studyId"=>$studyId)), 'id', 'title'),
        array(
            'separator'=>'<br>',
            'class'=>'valueList',
        )
    );

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
<div class="btn-group">
<input type="submit" value="Save" class="btn btn-success btn-xs" onclick="$('#expression-form').submit()" />
<?php if($model->id): ?><button onclick="$.get('/authoring/ajaxdelete?expressionId=<?php echo $model->id; ?>&studyId=<?php echo $model->studyId; ?>', function(data){location.reload();})"  class="btn btn-danger btn-xs">delete</button><?php endif; ?>
</div>
<?php
$this->endWidget();
?>
