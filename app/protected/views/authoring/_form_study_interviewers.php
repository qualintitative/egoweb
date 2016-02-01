<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'alter-list-edit-form',
	'enableAjaxValidation'=>false,
	'action'=>'/authoring/addUser',
)); ?>
<?php
	echo $form->hiddenField($model,'studyId',array('value'=>$studyId));
		$criteria = array(
			'condition'=>"permissions != 11",
		);
		$allUsers = User::model()->findAll($criteria);
?>
		<?php echo $form->dropdownlist(
			$model,
			'interviewerId',
			CHtml::listData(
				$allUsers,
				'id',
				'name'
			),
			array('empty' => 'None')
		); ?>

<?php if($ajax == true): ?>
	<?php echo CHtml::submitButton ("Add User", array("class"=>"btn btn-primary btn-xs"));?>
<?php else: ?>
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
<?php endif; ?>
<?php $this->endWidget(); ?>