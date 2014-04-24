<?php
/* @var $this StudyController */
/* @var $model Study */
/* @var $form CActiveForm */
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

	<div class="row" style="width:50%; float:left; padding:10px">
		<?php echo $form->labelEx($model,'introduction'); ?>
		<?php echo $form->textArea($model,'introduction',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'introduction'); ?>
	</div>

	<div class="row" style="width:50%; float:left; padding:10px">
		<?php echo $form->labelEx($model,'egoIdPrompt'); ?>
		<?php echo $form->textArea($model,'egoIdPrompt',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'egoIdPrompt'); ?>
	</div>

	<br style="clear:both">

	<div class="row" style="width:50%; float:left; padding:10px">
		<?php echo $form->labelEx($model,'alterPrompt'); ?>
		<?php echo $form->textArea($model,'alterPrompt',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'alterPrompt'); ?>
	</div>

	<div class="row" style="width:50%; float:left; padding:10px">
		<?php echo $form->labelEx($model,'conclusion'); ?>
		<?php echo $form->textArea($model,'conclusion',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'conclusion'); ?>
	</div>

	<br style="clear:both">

	<div class="row" style="width:50%; float:left; padding:10px">

	<div class="row">
		<?php echo $form->labelEx($model,'minAlters'); ?>
		<?php echo $form->textField($model,'minAlters',array('style'=>'width:120px')); ?>
		<?php echo $form->error($model,'minAlters'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'maxAlters'); ?>
		<?php echo $form->textField($model,'maxAlters',array('style'=>'width:120px')); ?>
		<?php echo $form->error($model,'maxAlters'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'adjacencyExpressionId'); ?>
		<?php $criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $model->id,
		);
		?>
		<?php echo $form->dropdownlist(
			$model,
			'adjacencyExpressionId',
			CHtml::listData(
				Expression::model()->findAll($criteria),
				'id',
				function($post) {return CHtml::encode(substr($post->name,0,40));}
			),
			array('empty' => 'Choose One')
		); ?>
		<?php echo $form->error($model,'adjacencyExpressionId'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'valueRefusal'); ?>
		<?php echo $form->textField($model,'valueRefusal'); ?>
		<?php echo $form->error($model,'valueRefusal'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'valueDontKnow'); ?>
		<?php echo $form->textField($model,'valueDontKnow'); ?>
		<?php echo $form->error($model,'valueDontKnow'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'valueLogicalSkip'); ?>
		<?php echo $form->textField($model,'valueLogicalSkip'); ?>
		<?php echo $form->error($model,'valueLogicalSkip'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'valueNotYetAnswered'); ?>
		<?php echo $form->textField($model,'valueNotYetAnswered'); ?>
		<?php echo $form->error($model,'valueNotYetAnswered'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'multiSessionEgoId'); ?>
		<?php $criteria=new CDbCriteria;
		$criteria=array(
			'condition'=>"studyId = " . $model->id . " AND subjectType = 'EGO_ID'",
		);
		?>
		<?php echo $form->dropdownlist(
			$model,
			'multiSessionEgoId',
			CHtml::listData(
				Question::model()->findAll($criteria),
				'id',
				function($post) {return CHtml::encode(substr($post->title,0,40));}
			),
			array('empty' => 'Choose One')
		); ?>
		<?php echo $form->error($model,'multiSessionEgoId'); ?>
	</div>

		<br style="clear:both">

		<label>Fill auto-complete with participant list</label>
		<?php echo $form->checkBox($model,'useAsAlters'); ?>

		<br style="clear:both">

		<label>Restrict alters to participant list</label>
		<?php echo $form->checkBox($model,'restrictAlters'); ?>

		<br style="clear:both">

		<label>Populate alter list from participant list</label>
		<?php echo $form->checkBox($model,'fillAlterList'); ?>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

	<?php $this->endWidget(); ?>
	<?php if(!$model->isNewRecord): ?>
		<?php echo CHtml::button(
			"Delete",
			array("onclick"=>"js:if(confirm('Are you sure you want to delete this study?')){document.location.href='/authoring/delete/".$model->id. "'}")
		); ?>
	<?php endif; ?>
</div>
	<div class="row" style="float:left;width:100%; padding:10px">

		<div id="interviewers">
		<?php
					$criteria=new CDbCriteria;
					$criteria = array(
					'condition'=>"studyId = " . $model->id,
					'order'=>'id DESC',
				);
		$dataProvider=new CActiveDataProvider('Interviewer',array(
				'criteria'=>$criteria,
				'pagination'=>false,
			));
		$this->renderPartial('_view_study_interviewers', array('dataProvider'=>$dataProvider, 'ajax'=>true), false, false);
		$interviewer = new Interviewer;
		$this->renderPartial('_form_study_interviewers', array('dataProvider'=>$dataProvider, 'model'=>$interviewer, 'studyId'=>$model->id, 'ajax'=>true), false, false);
		?>

		</div>

		<div id="alterList" >
		<?php
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $model->id,
				'order'=>'ordering',
			);
			$dataProvider=new CActiveDataProvider('AlterList',array(
				'criteria'=>$criteria,
			));
   			$this->renderPartial('_view_alter_list', array('dataProvider'=>$dataProvider, 'model'=>$model, 'studyId'=>$model->id, 'ajax'=>true), false, false);
		?>
		</div>



		<div id="showLink" style="padding:10px;clear:both;"></div>

		<div style="float:left; width:400px;">
			<div style="margin-bottom:15px;">
				<span class="smallheader">Add new participant</span>
				<?php
					$alterList = new AlterList;
					$form=$this->beginWidget('CActiveForm', array(
						'id'=>'add-alter-form',
						'enableAjaxValidation'=>true,
					));
				?>
				<?php echo $form->hiddenField($alterList,'id',array('value'=>$alterList->id)); ?>
				<?php echo $form->hiddenField($alterList,'studyId',array('value'=>$model->id)); ?>
				<?php echo $form->labelEx($alterList,'name'); ?>
				<?php echo $form->textField($alterList,'name', array('style'=>'width:100px')); ?>
				<?php echo $form->error($alterList,'name'); ?>
				<?php echo $form->labelEx($alterList,'email'); ?>
				<?php echo $form->textField($alterList,'email', array('style'=>'width:100px')); ?>
				<?php echo $form->error($alterList,'email'); ?>
				<?php
				$interviewerIds = q("SELECT interviewerId FROM interviewers WHERE studyId = " . $model->id)->queryColumn();
				$interviewers = array();
				foreach($interviewerIds as $interviewerId){
					$interviewers[$interviewerId] = User::getName($interviewerId);
				}
				?>
				<?php echo $form->dropdownlist(
					$alterList,
					'interviewerId',
					$interviewers,
					array('empty' => 'None')
				); ?>
				<?php echo CHtml::ajaxSubmitButton ("Add Alter",
					CController::createUrl('ajaxupdate'),
					array('update' => '#alterList'),
					array('id'=>uniqid(), 'live'=>false));
				?>
				<?php $this->endWidget(); ?>
			</div>
			<div id="edit-alterList" style="margin-bottom:15px;"></div>
		</div>
		<div style="float:left; width:400px; clear:left">
<form enctype="multipart/form-data" method="POST" action="/authoring/importlist">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <!-- Name of input element determines name in $_FILES array -->
     <input name="userfile" type="file" />
    <input type="hidden" name="studyId" value="<?= $model->id; ?>" />
    <input class="btn btn-primary" type="submit" value="Import Participant List" />
</form>
	</div>
		<div id="alterPrompt" >
		<?php
			$criteria=new CDbCriteria;
			$criteria=array(
				'condition'=>"studyId = " . $model->id,
			);
			$dataProvider=new CActiveDataProvider('AlterPrompt',array(
				'criteria'=>$criteria,
			));
			$this->renderPartial('_view_alter_prompt', array('dataProvider'=>$dataProvider, 'model'=>$model, 'studyId'=>$model->id, 'ajax'=>true), false, false);
		?>
		</div>
		<div style="float:left; width:100%;margin-top:15px;">
			<span class="smallheader">Add new alter prompt</span>
			<?php
				$alterPrompt = new AlterPrompt;
				$form=$this->beginWidget('CActiveForm', array(
					'id'=>'add-alter-prompt-form',
					'enableAjaxValidation'=>true,
				));
			?>
			<?php echo $form->hiddenField($alterPrompt,'id',array('value'=>$alterPrompt->id)); ?>
			<?php echo $form->hiddenField($alterPrompt,'studyId',array('value'=>$model->id)); ?>
			<label style="float:left; padding:5px;">After</label>
			<?php echo $form->textField($alterPrompt,'afterAltersEntered', array('style'=>'width:20px;float:left')); ?>
			<label style="float:left; padding:5px;">alters, display </label>
			<?php echo $form->textField($alterPrompt,'display', array('style'=>'width:100px;float:left')); ?>
			<?php echo $form->error($alterPrompt,'afterAltersEntered'); ?>
			<?php echo $form->error($alterPrompt,'display'); ?>
			<?php echo CHtml::ajaxSubmitButton ("Add",
				CController::createUrl('ajaxupdate'),
				array('update' => '#alterPrompt'),
				array('id'=>uniqid(), 'live'=>false, 'style'=>'float:left; margin:3px 5px;'));
			?>
			<?php $this->endWidget(); ?>
			<div id="edit-alterPrompt" style="margin-top:15px;float:left;clear:both;"></div>
		</div>
	</div>
