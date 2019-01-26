<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'add-alter-form',
	'enableAjaxValidation'=>false,
	'action'=>'user',
	'htmlOptions'=>array('class'=>'form-inline'),
));
?>
<?php echo $form->hiddenField($user,'id',array('value'=>$user->id)); ?>
<div class="form-group">
<?php echo $form->labelEx($user,'name'); ?>
<?php echo $form->textField($user,'name', array('class'=>'form-control input-sm')); ?>
</div>
<div class="form-group">
<?php echo $form->labelEx($user,'email', array('class'=>'control-label')); ?>
<?php echo $form->textField($user,'email', array('class'=>'form-control input-sm')); ?>
</div>
<div class="form-group">
<?php echo $form->dropdownlist(
	$user,
	'permissions',
		User::model()->roles(),

	array('empty' => 'Select Permission')
); ?>
</div>
<?php echo CHtml::submitButton (($user->isNewRecord) ? "Add" : "Update", array("class"=>'btn btn-primary'));
?>
<?php $this->endWidget(); ?>
