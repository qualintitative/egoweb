<?php
/* @var $this StudyController */
/* @var $dataProvider CActiveDataProvider */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'study-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>
</div>

<div class="view" style="width:360px;float:left;margin-right:30px">
	<h2>Single Session Studies</h2>
	<?php foreach($single as $data): ?>
	<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
	<?php endforeach; ?>
</div>

<div class="view" style="width:360px;float:left;margin-right:30px">
	<h2>Multi Session Studies</h2>
	<?php foreach($multi as $data): ?>
	<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
	<?php endforeach; ?>
</div>