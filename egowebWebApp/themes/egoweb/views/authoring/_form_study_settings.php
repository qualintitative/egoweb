<?php
/* @var $this StudyController */
/* @var $model Study */
/* @var $form CActiveForm */
?>
<div class="form mtl">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'study-form',
		'enableAjaxValidation'=>false,
	)); ?>
		<?php echo $form->errorSummary($model); ?>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<?php echo $form->labelEx($model,'name',array('class'=>'control-label')); ?>
					<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'name'); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<?php echo $form->labelEx($model,'introduction',array('class'=>'control-label')); ?>
					<?php echo $form->textArea($model,'introduction',array('rows'=>6, 'cols'=>50,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'introduction'); ?>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<?php echo $form->labelEx($model,'egoIdPrompt',array('class'=>'control-label')); ?>
					<?php echo $form->textArea($model,'egoIdPrompt',array('rows'=>6, 'cols'=>50,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'egoIdPrompt'); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<?php echo $form->labelEx($model,'alterPrompt',array('class'=>'control-label')); ?>
					<?php echo $form->textArea($model,'alterPrompt',array('rows'=>6, 'cols'=>50,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'alterPrompt'); ?>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<?php echo $form->labelEx($model,'conclusion',array('class'=>'control-label')); ?>
					<?php echo $form->textArea($model,'conclusion',array('rows'=>6, 'cols'=>50,'class'=>'form-control')); ?>
					<?php echo $form->error($model,'conclusion'); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group row">
					<?php echo $form->labelEx($model,'minAlters',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php echo $form->textField($model,'minAlters',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'minAlters'); ?>
					</div>
					
				</div>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'maxAlters',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php echo $form->textField($model,'maxAlters',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'maxAlters'); ?>
					</div>
				</div>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'adjacencyExpressionId',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php
						$criteria=new CDbCriteria;
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
							array('empty' => 'Choose One','class'=>'form-control')
						); ?>
						<?php echo $form->error($model,'adjacencyExpressionId'); ?>
					</div>
				</div>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'valueRefusal',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php echo $form->textField($model,'valueRefusal',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'valueRefusal'); ?>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group row">
					<?php echo $form->labelEx($model,'valueDontKnow',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php echo $form->textField($model,'valueDontKnow',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'valueDontKnow'); ?>
					</div>
				</div>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'valueLogicalSkip',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php echo $form->textField($model,'valueLogicalSkip',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'valueLogicalSkip'); ?>
					</div>
				</div>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'valueNotYetAnswered',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
						<?php echo $form->textField($model,'valueNotYetAnswered',array('class'=>'form-control')); ?>
						<?php echo $form->error($model,'valueNotYetAnswered'); ?>
					</div>
				</div>
				<div class="form-group row">
					<?php echo $form->labelEx($model,'multiSessionEgoId',array('class'=>'col-sm-6 control-label')); ?>
					<div class="col-sm-6">
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
							array('empty' => 'Choose One','class'=>'form-control')
						); ?>
						<?php echo $form->error($model,'multiSessionEgoId'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="row mtl">
			<div class="col-sm-3 col-sm-offset-9">
				<div class="row">
					<div class="col-xs-6">
						<?php echo CHtml::submitButton($model->isNewRecord?'Create':'Save', array('class'=>'btn btn-primary btn-lg btn-block')); ?>
					</div>
					<div class="col-xs-6">
						<?php if(!$model->isNewRecord): ?>
							<?php echo CHtml::button(
								"Delete",
								array('class'=>'btn btn-danger btn-lg btn-block',"onclick"=>"js:if(confirm('Are you sure you want to delete this study?')){document.location.href='/authoring/delete/".$model->id. "'}")
							); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php $this->endWidget(); ?>
</div>
