<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'alter-list-edit-form',
	'enableAjaxValidation'=>true,
)); ?>

<span class="smallheader">Edit an alter</span>
<?php echo $form->hiddenField($model,'id',array('value'=>$model->id)); ?>
<?php echo $form->hiddenField($model,'studyId',array('value'=>$studyId)); ?>

<?php echo $form->labelEx($model,'name'); ?>
<?php echo $form->textField($model,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'name'); ?>
<?php echo $form->labelEx($model,'email'); ?>
<?php echo $form->textField($model,'email', array('style'=>'width:100px')); ?>
<?php echo $form->error($model,'email'); ?>
<div class="form-group">
  Name Generator<br>
<?php
$alterlist = new AlterList;
//echo $form->checkBoxList($model, 'originalFileCalendars', CHtml::listData(OriginalFile::model()->getCalendarType(), 'ct_id', 'type_name'));
$model->nameGenQIds = explode(",",$model->nameGenQIds);
echo $form->checkBoxList(
  $model,
  'nameGenQIds',
  CHtml::listData(
    Question::model()->findAllByAttributes(array("studyId"=>$model->studyId, "subjectType"=>"NAME_GENERATOR")),
    'id',
    'title'
  ),
  array('empty' => 'None')
); ?>
</div>
		<?php echo $form->dropdownlist(
			$model,
			'interviewerId',
			CHtml::listData(
				User::model()->findAll(),
				'id',
				'name'
			),
			array('empty' => 'None')
		); ?>

<?php if($ajax == true): ?>
<?php echo CHtml::ajaxSubmitButton ("Edit Alter",
	CController::createUrl('ajaxupdate'),
	array('update' => '#alterList'),
	array('id'=>uniqid(), 'live'=>false));
?>
<?php else: ?>
<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
<?php endif; ?>
<?php $this->endWidget(); ?>
