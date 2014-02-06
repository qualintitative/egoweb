<div class="container">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'alter-list-edit-form',
		'enableAjaxValidation'=>false,
		'action'=>'/authoring/addInterviewer',
		'htmlOptions'=>array('class'=>'mbl')
	)); ?>
		<?php echo $form->hiddenField($model,'studyId',array('value'=>$studyId)); ?>
		<?php echo CHtml::label('Add new alter','interviewerId',array('class'=>'control-label')); ?>
		<div class="form-inline">
			<div class="form-group row">
				<?php echo $form->dropdownlist(
					$model,
					'interviewerId',
					CHtml::listData(
						User::model()->findAll(),
						'id',
						'name'
					),
					array('empty' => 'None','class'=>'form-control input-lg')
				); ?>
				<?php if($ajax == true): ?>
					<?php echo CHtml::submitButton ("Add Interviewer", array('class'=>'btn btn-primary btn-lg'));?>
				<?php else: ?>
					<?php echo CHtml::submitButton($model->isNewRecord?'Create':'Save',array('class'=>'btn btn-primary btn-lg')); ?>
				<?php endif; ?>
			</div>
		</div>
		
	<?php $this->endWidget(); ?>
</div>