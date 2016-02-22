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
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
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
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
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
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
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
		/*onImageUpload: function(files, editor, welEditable) {
			uploadImage(files[0], editor, welEditable);
		},*/
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
});
function deleteAlterList(studyId){
    if(confirm("Are you sure you want to delete all the participants in the list?")){
        $.get("/authoring/ajaxdelete?studyId=" + studyId + "&AlterList[id]=all", function(data){
            $("#alterList").html(data);
        });
    }
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
		<?php echo $form->labelEx($model,'alterPrompt'); ?>
		<div class="audioPlay" id="STUDY_ALTERPROMPT"><?php if(file_exists(Yii::app()->basePath."/../audio/".$model->id . "/STUDY/ALTERPROMPT.mp3")): ?><a class="play-sound" onclick="playSound($(this).attr('file'))" href="#" file="/audio/<?= $model->id . "/STUDY/ALTERPROMPT.mp3"; ?>"><span class="fui-volume"></span></a><?php endif; ?></div>
		<?php if(!$model->isNewRecord):?>
		<a class="btn btn-primary pull-right btn-xs" data-toggle="modal" data-target="#myModal" href="/authoring/uploadaudio?type=STUDY&id=ALTERPROMPT&studyId=<?= $model->id; ?>">Upload Audio</a>
		<?php endif;?>
		<?php echo $form->textArea($model,'alterPrompt',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'alterPrompt'); ?>
	</div>

	<div class="col-sm-6">
		<?php echo $form->labelEx($model,'conclusion'); ?>
		<?php echo $form->textArea($model,'conclusion',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'conclusion'); ?>
	</div>

</div>

<div class="col-sm-6">
	<div class="form-group">
		<?php echo $form->labelEx($model,'minAlters', array('class'=>'control-label col-sm-6')); ?>
		<div class='col-sm-6'>
		    <?php echo $form->textField($model,'minAlters', array('class'=>'form-control')); ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo $form->labelEx($model,'maxAlters', array('class'=>'control-label col-sm-6')); ?>
		<div class='col-sm-6'>
		    <?php echo $form->textField($model,'maxAlters', array('class'=>'form-control')); ?>
		</div>
	</div>

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

        <div>
            <label>Style CSS</label>
            <?php echo $form->textarea($model,'style', array("style"=>"height:6em")); ?>
        </div>

	</div>



	<div class="buttons col-sm-12">
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array("class"=>"btn btn-primary btn-sm pull-right",)); ?>

	<?php $this->endWidget(); ?>
	<?php if(!$model->isNewRecord): ?>
		<?php echo CHtml::button(
			"Delete",
			array(
				"class"=>"btn btn-danger btn-sm pull-left",
				"onclick"=>"js:if(confirm('Are you sure you want to delete this study?')){document.location.href='/authoring/delete/".$model->id. "'}"
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
				<?php
				#OK FOR SQL INJECTION
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
				<?php echo CHtml::ajaxSubmitButton ("Add Participant",
					CController::createUrl('ajaxupdate'),
					array('update' => '#alterList'),
					array('id'=>uniqid(), 'live'=>false, "class"=>"btn btn-primary btn-xs")
				);
				?>
				<?php $this->endWidget(); ?>
				<button class="btn btn-danger btn-xs" onclick="deleteAlterList(<?php echo $model->id; ?>)">Delete Participant List</button>
			</div>
			<div id="edit-alterList" style="margin-bottom:15px;"></div>
		</div>
		<div style="float:left; width:400px; clear:left">
            <?php echo CHtml::form('/authoring/importlist', 'post', array('id'=>'importListForm', 'enctype'=>'multipart/form-data')) ?>
            	<!-- MAX_FILE_SIZE must precede the file input field -->
            	<!-- Name of input element determines name in $_FILES array -->
            	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?>" />
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
				array('id'=>uniqid(), 'live'=>false, 'style'=>'float:left; margin:3px 5px;', "class"=>"btn btn-primary btn-xs"));
			?>
			<?php $this->endWidget(); ?>
			<div id="edit-alterPrompt" style="margin-top:15px;float:left;clear:both;"></div>
		<div style="float:left; width:400px; clear:left">
            <?php echo CHtml::form('/authoring/importprompts', 'post', array('id'=>'importListForm', 'enctype'=>'multipart/form-data')) ?>
            	<!-- MAX_FILE_SIZE must precede the file input field -->
            	<!-- Name of input element determines name in $_FILES array -->
            	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?>" />
            	<input name="userfile" type="file" />
            	<input type="hidden" name="studyId" value="<?= $model->id; ?>" />
            	<input class="btn btn-primary" type="submit" value="Import Variable Prompts" />
            </form>
	    </div>
		</div>
	</div>
	<script type="text/javascript">
		//On import study form submit
		$( "#importListForm" ).submit(function( event) {
			var userfile = document.getElementById('userfile').files[0];

			if(userfile && userfile.size < <?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?> ) { //This size is in bytes.

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
			} else {
				//Prevent default and display error
				event.preventDefault();
				alert("Upload file cannot exceed <?php echo number_format(Yii::app()->params['maxUploadFileSize'] / 1048576, 1) . ' MB'; ?>");
				return false;
			}
		});
	</script>
