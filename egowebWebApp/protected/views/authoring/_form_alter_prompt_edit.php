	<div class="row">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'alter-prompt-edit-form',
	'enableAjaxValidation'=>true,
)); ?>
		<span class="smallheader">Edit Alter Prompt</span>
	<?php echo $form->hiddenField($model,'id',array('value'=>$model->id)); ?>
	<?php echo $form->hiddenField($model,'studyId',array('value'=>$studyId)); ?>

	<label style="float:left; padding:5px;">After</label>
	<?php echo $form->textField($model,'afterAltersEntered', array('style'=>'width:20px;float:left')); ?>
	<label style="float:left; padding:5px;">alters, display </label>
	<?php echo $form->textField($model,'display', array('style'=>'width:100px;float:left')); ?>
	<?php echo $form->error($model,'afterAltersEntered'); ?>
	<?php echo $form->error($model,'display'); ?>
	<?php if($ajax == true): ?>
		<?php echo CHtml::ajaxSubmitButton ("Update",
        	CController::createUrl('ajaxupdate'), 
        	array('update' => '#alterPrompt'),
        	array('id'=>uniqid(), 'live'=>false, 'style'=>'float:left; margin:3px 5px;'));
		?>
	<?php else: ?>
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	<?php endif; ?>
	<?php $this->endWidget(); ?>
</div>