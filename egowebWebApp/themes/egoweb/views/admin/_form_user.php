<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'add-alter-form',
	'enableAjaxValidation'=>false,
	'action'=>'user',
));
?>
<?php echo $form->hiddenField($user,'id',array('value'=>$user->id)); ?>
<?php echo $form->labelEx($user,'name'); ?>
<?php echo $form->textField($user,'name', array('style'=>'width:100px')); ?>
<?php echo $form->error($user,'name'); ?>
<?php echo $form->labelEx($user,'email'); ?>
<?php echo $form->textField($user,'email', array('style'=>'width:100px')); ?>
<?php echo $form->error($user,'email'); ?>
<?php echo $form->labelEx($user,'permissions'); ?>

<?php echo $form->dropdownlist(
	$user,
	'permissions',
		array(
			1=>"interviewer",
			11=>"admin"
		),

	array('empty' => 'Choose One')
); ?>

<?php echo CHtml::submitButton (($user->isNewRecord) ? "Add User" : "Update");
?>
<?php $this->endWidget(); ?>