<?php $this->pageTitle=Yii::app()->name . ' - Reset Password';
?>


<?php if(Yii::app()->user->hasFlash('reset')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('reset'); ?>
</div>

<?php else: ?>

<div class="form halfsize">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'reset-form',
	'enableClientValidation'=>false,
  'htmlOptions'=>array('class'=>'form-horizontal'),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="form-group">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('class'=>'form-control input-sm')); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'confirm'); ?>
		<?php echo $form->passwordField($model,'confirm',array('class'=>'form-control input-sm')); ?>
		<?php echo $form->error($model,'confirm'); ?>
	</div>
  <div class="form-group">
		<?php echo CHtml::submitButton('Submit', array("class"=>"btn btn-primary")); ?>
  </div>
	<?php $this->endWidget(); ?>



</div>

	<?php endif; ?>
