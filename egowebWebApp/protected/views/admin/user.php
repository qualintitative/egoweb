<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'users-grid',
	'dataProvider'=>$dataProvider,
	//'filter'=>$model,
	'pager'=>array(
		'header'=> '',
	),
	'cssFile'=>false,
	'summaryText'=>'',
	'columns'=>array(
		'name',
		'email',
	),
));
?>

<?php
$user = new User;
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'add-alter-form',
	'enableAjaxValidation'=>true,
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

<?php echo CHtml::ajaxSubmitButton ("Add Alter",
	CController::createUrl('ajaxupdate'),
	array('update' => '#alterList'),
	array('id'=>uniqid(), 'live'=>false));
?>
<?php $this->endWidget(); ?>