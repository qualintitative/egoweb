<?php
/* @var $this StudyController */
/* @var $model Study */
$this->pageTitle = $model->name;
?>

<script>
$(function(){
	$('#Study_introduction').summernote({
		toolbar:noteBar,
		height:200,
		onChange: function(contents, $editable) {
			$('#Study_introduction').val(contents);
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#Study_egoIdPrompt').summernote({
		toolbar:noteBar,
		height:200,
		onChange: function(contents, $editable) {
			$('#Study_egoIdPrompt').val(contents);
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#Study_alterPrompt').summernote({
		toolbar:noteBar,
		height:200,
		onChange: function(contents, $editable) {
			$('#Study_alterPrompt').val(contents);
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#Study_conclusion').summernote({
		toolbar:noteBar,
		height:200,
		onChange: function(contents, $editable) {
			$('#Study_conclusion').val(contents);
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#Study_footer').summernote({
		toolbar:noteBar,
		height:200,
		onChange: function(contents, $editable) {
			$('#Study_footer').val(contents);
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
	$('#Study_header').summernote({
		toolbar:noteBar,
		height:200,
		onChange: function(contents, $editable) {
			$('#Study_header').val(contents);
		},
		onpaste: function(e) {
			var thisNote = $(this);
			var updatePastedText = function(someNote){
				var original = someNote.code();
				var cleaned = CleanPastedHTML(original);
				someNote.code('').html(cleaned);
			};
			setTimeout(function () {
				updatePastedText(thisNote);
			}, 10);
		}
	});
});
function deleteAlterList(studyId){
    if(confirm("Are you sure you want to delete all the participants in the list?")){
        $.get("<?=$this->createUrl('/authoring/ajaxdelete?studyId=')?>" + studyId + "&AlterList[id]=all", function(data){
            $("#alterList").html(data);
        });
    }
}
function exportAlterList(){
  $("#exportlistform").submit();
}

</script>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'study-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array('class'=>'form-horizontal'),
)); ?>

<?php echo $form->errorSummary($model); ?>

<br>

<div class="form-group">
    <div class="col-sm-6">
        <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100, 'class'=>'form-control')); ?>
    </div>
    <div class="col-sm-6">
        <label class='control-label'>Last Updated <?php echo $model->modified; ?></label>
    </div>
</div>

<div class="form-group">

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'introduction'); ?>
		<?php echo $form->textArea($model,'introduction',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'introduction'); ?>
	</div>

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'egoIdPrompt'); ?>
		<?php echo $form->textArea($model,'egoIdPrompt',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'egoIdPrompt'); ?>
	</div>

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'conclusion'); ?>
		<?php echo $form->textArea($model,'conclusion',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'conclusion'); ?>
	</div>

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'header'); ?>
		<?php echo $form->textArea($model,'header',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'header'); ?>
	</div>

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'footer'); ?>
		<?php echo $form->textArea($model,'footer',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'footer'); ?>
	</div>

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'javascript'); ?>
		<?php echo $form->textArea($model,'javascript',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'javascript'); ?>
	</div>

    <div class="col-sm-6">
        <label>Style CSS</label>
        <?php echo $form->textarea($model,'style',array('rows'=>6, 'cols'=>50)); ?>
    </div>

</div>

<div class="col-sm-6">

	<div class="form-group">
		<?php echo $form->labelEx($model,'valueRefusal', array('class'=>'control-label col-sm-6')); ?>
		<div class='col-sm-6'>
		    <?php echo $form->textField($model,'valueRefusal', array('class'=>'form-control')); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'valueDontKnow', array('class'=>'control-label col-sm-6')); ?>
		<div class='col-sm-6'>
		    <?php echo $form->textField($model,'valueDontKnow', array('class'=>'form-control')); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'valueLogicalSkip', array('class'=>'control-label col-sm-6')); ?>
		<div class='col-sm-6'>
		    <?php echo $form->textField($model,'valueLogicalSkip', array('class'=>'form-control')); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'valueNotYetAnswered', array('class'=>'control-label col-sm-6')); ?>
		<div class='col-sm-6'>
		    <?php echo $form->textField($model,'valueNotYetAnswered', array('class'=>'form-control')); ?>
		</div>
	</div>
</div>

<div class="col-sm-6">
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

		<br style="clear:both">

		<label>Fill auto-complete with participant list</label>
		<?php echo $form->checkBox($model,'useAsAlters'); ?>

		<br style="clear:both">

		<label>Restrict alters to participant list</label>
		<?php echo $form->checkBox($model,'restrictAlters'); ?>

		<br style="clear:both">

		<label>Populate alter list from participant list</label>
		<?php echo $form->checkBox($model,'fillAlterList'); ?>

		<label>Hide Ego Id Page (for studies will Ego Id prefills)</label>
		<?php echo $form->checkBox($model,'hideEgoIdPage'); ?>


	</div>



	<div class="buttons col-sm-12">
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array("class"=>"btn btn-primary btn-sm pull-right",)); ?>

	<?php $this->endWidget(); ?>
	<?php if(!$model->isNewRecord): ?>
		<?php echo CHtml::button(
			"Delete",
			array(
				"class"=>"btn btn-danger btn-sm pull-left",
				"onclick"=>"js:if(confirm('Are you sure you want to delete this study?')){document.location.href='".$this->createUrl("/authoring/delete/".$model->id)."'}"
			)
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
        <div class="form-group">
          Name Generator<br>
        <?php
$alterlist = new AlterList;
//echo $form->checkBoxList($model, 'originalFileCalendars', CHtml::listData(OriginalFile::model()->getCalendarType(), 'ct_id', 'type_name'));

        echo $form->checkBoxList(
          $alterlist,
          'nameGenQIds',
          CHtml::listData(
            Question::model()->findAllByAttributes(array("studyId"=>$model->id, "subjectType"=>"NAME_GENERATOR")),
            'id',
            'title'
          ),
          array('empty' => 'None')
        ); ?>
      </div>
      Interviewer
				<?php
        $result = Interviewer::model()->findAllByAttributes(array("studyId"=>$model->id));
        $interviewers = array();
        foreach($result as $interviewer){
					$interviewers[$interviewer->interviewerId] = User::getName($interviewer->interviewerId);
				}
				?>
				<?php echo $form->dropdownlist(
					$alterList,
					'interviewerId',
					$interviewers,
					array('empty' => 'None')
				); ?>
				<?php echo CHtml::ajaxSubmitButton ("Add Participant",
					CController::createUrl('ajaxupdate'),
					array('update' => '#alterList'),
					array('id'=>uniqid(), 'live'=>false, "class"=>"btn btn-primary btn-xs")
				);
				?>
				<?php $this->endWidget(); ?>
        <button class="btn btn-info btn-xs" onclick="exportAlterList()">Export Pre-defined Participant List</button>
				<button class="btn btn-danger btn-xs" onclick="deleteAlterList(<?php echo $model->id; ?>)">Delete Participant List</button>
			</div>
			<div id="edit-alterList" style="margin-bottom:15px;"></div>
		</div>
		<div style="float:left; width:400px; clear:left">
            <?php echo CHtml::form('/authoring/importlist', 'post', array('id'=>'importListForm', 'enctype'=>'multipart/form-data')) ?>
            	<input name="userfile" type="file" />
            	<input type="hidden" name="studyId" value="<?= $model->id; ?>" />
            	<input class="btn btn-primary" type="submit" value="Import Participant List" />
            </form>
            <?php echo CHtml::form('/authoring/exportalterlist', 'post', array('id'=>'exportlistform')) ?>
            <input type="hidden" name="studyId" value="<?= $model->id; ?>" />
            </form>
	    </div>
	</div>
	<script type="text/javascript">
		//On import study form submit
		$( "#importListForm" ).submit(function( event) {
				var userfile = document.getElementById('userfile').files[0];
				var res_field = document.getElementById('userfile').value;
				var extension = res_field.substr(res_field.lastIndexOf('.') + 1).toLowerCase();
				var allowedExtensions = ['csv'];
				event.preventDefault();
				if (res_field.length > 0)
				{
					if( allowedExtensions.indexOf(extension) === -1 )
					{
						event.preventDefault();
						alert('Invalid file Format. Only ' + allowedExtensions.join(', ') + ' allowed.');
						return false;
					}
				}
				else{
					//Submit form
					$("#importListForm").submit();
				}

		});
	</script>
