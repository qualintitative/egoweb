<?php
/* @var $this StudyController */
/* @var $dataProvider CActiveDataProvider */
?>
<div class="container">
	<form class="form-inline mvl" role="form">
		<div class="form-group">
			<label for="study_name" class="sr-only">Study Name</label>
			<input type="email" class="form-control input-lg" id="study_name" placeholder="Study Name">
		</div>
		<button type="submit" class="btn btn-primary btn-lg">Create</button>
	</form>
	<div class="row">
		<div class="col-sm-6">
			<div class="h6">Single Session Studies</div>
			<?php foreach($single as $data): ?>
			<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
			<?php endforeach; ?>
		</div>
		<div class="col-sm-6">
			<div class="h6">Multi Session Studies</div>
			<?php foreach($multi as $data): ?>
			<?php echo CHtml::link(CHtml::encode($data->name), array('edit', 'id'=>$data->id))."<br>"; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>