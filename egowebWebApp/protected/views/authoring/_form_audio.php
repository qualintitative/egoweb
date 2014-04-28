<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 class="modal-title">Upload Audio</h4>
</div>

<div class="modal-body">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'audio-form',
	'enableAjaxValidation'=>false,
	'action'=>'/authoring/uploadaudio',
	'htmlOptions'=>array('enctype'=>'multipart/form-data'),
));
?>
<input type="hidden" name="id" value=<?= $id; ?>>
<input type="hidden" name="studyId" value=<?= $studyId; ?>>
<input type="hidden" name="type" value=<?= $type; ?>>

<input type="file" name="userfile" id="audioFile">
<?php $this->endWidget(); ?>
</div>

<div class="modal-footer">
	<ul class="nav nav-pills">
		<?php if (file_exists(Yii::app()->basePath."/../audio/".$studyId . "/" . $type . "/" . $id . ".mp3")):?>
			<button class="btn btn-danger" onclick="deleteAudio(<?= $id; ?>, <?= $studyId; ?>, '<?= $type; ?>', $('#<?php echo $type . "_" . $id; ?>'));">Delete</button>
		<?php endif; ?>
		<button type="button" class="btn btn-primary" onclick="uploadAudio($('#audioFile')[0], <?= $id; ?>, <?= $studyId; ?>, '<?= $type; ?>', $('#<?php echo $type . "_" . $id; ?>'));">Upload</button>
	</ul>
</div>