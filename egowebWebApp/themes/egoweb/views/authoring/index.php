<?php
/* @var $this StudyController */
/* @var $dataProvider CActiveDataProvider */
?>
<div class="container">
	<div class="row">
		<div class="col-sm-6">
			<?php $form=$this->beginWidget('CActiveForm', array(
				'id'=>'study-form',
				'enableAjaxValidation'=>false,
				    'htmlOptions'=>array('class'=>'mvl')
			)); ?>
				<?php echo $form->errorSummary($model); ?>
				<?php echo $form->labelEx($model,'name',array('class'=>'control-label')); ?>
				<div class="form-inline">
					<div class="form-group">
						<?php echo $form->textField($model,'name',array('maxlength'=>100,'class'=>'form-control input-lg')); ?>
						<?php echo $form->error($model,'name'); ?>
					</div>
					<?php echo CHtml::submitButton($model->isNewRecord?'Create':'Save',array('class'=>'btn btn-primary btn-lg')); ?>
				</div>
			<?php $this->endWidget(); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div class="h6">Single Session Studies</div>
			<?php foreach($single as $data): ?>
			<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
			<?php endforeach; ?>
		</div>
		<div class="col-sm-6">
			<div class="h6">Multi Session Studies</div>
			<?php foreach($multi as $data): ?>
			<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>