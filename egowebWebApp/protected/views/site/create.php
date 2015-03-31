<?php
/* @var $this ProfileController */
/* @var $model User */
/* @var $form CActiveForm */
?>
<h1>Create Admin User</h1>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'user-form',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('enctype' => 'multipart/form-data', 'class'=>'form-horizontal'),
)); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
	<?php echo $form->label($model,'name',array('class'=>'control-label col-lg-1')); ?>
	<div class="col-lg-3">
		<?php echo $form->textField($model,'name',array('class'=>'form-control')); ?>
	</div>
</div>

<div class="form-group">
	<?php echo $form->label($model,'email',array('class'=>'control-label col-lg-1')); ?>
	<div class="col-lg-3">
		<?php echo $form->textField($model,'email',array('class'=>'form-control')); ?>
	</div>
</div>

<div class="form-group">
	<?php echo $form->label($model,'password',array('class'=>'control-label col-lg-1')); ?>
	<div class="col-lg-3">
		<?php echo $form->passwordField($model,'password',array('class'=>'form-control')); ?>
	</div>
</div>

<div class="form-group">
	<?php echo $form->label($model,'confirm',array('class'=>'control-label col-lg-1')); ?>
	<div class="col-lg-3">
		<?php echo $form->passwordField($model,'confirm',array('class'=>'form-control')); ?>
	</div>
</div>


<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary col-lg-offset-1')); ?>
<?php $this->endWidget(); ?>
