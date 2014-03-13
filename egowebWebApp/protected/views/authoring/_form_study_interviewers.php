<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'alter-list-edit-form',
	'enableAjaxValidation'=>false,
	'action'=>'/authoring/addInterviewer',
)); ?>
<?php echo $form->hiddenField($model,'studyId',array('value'=>$studyId)); ?>
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
	<?php echo CHtml::submitButton ("Add Interviewer");?>
<?php else: ?>
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
<?php endif; ?>
<?php $this->endWidget(); ?>