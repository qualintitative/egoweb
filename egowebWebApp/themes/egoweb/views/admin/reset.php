<?php $this->pageTitle=Yii::app()->name . ' - Reset Password';
?>


<?php if(Yii::app()->user->hasFlash('reset')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('reset'); ?>
</div>

<?php else: ?>

<div class="form halfsize">

<h1>Reset Password</h1>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'reset-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'confirm'); ?>
		<?php echo $form->passwordField($model,'confirm',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'confirm'); ?>
	</div>

		<?php echo CHtml::submitButton('Submit'); ?>
	<?php $this->endWidget(); ?>



</div>

	<?php endif; ?>