<div class="view" style="width:360px;float:left;margin-right:30px">
	<h2>Single Session Studies</h2>
	<?php foreach($single as $data): ?>
	<?php echo CHtml::encode($data->name); ?>

		<?php echo CHtml::button(
			"Restore",
			array(
				"class"=>"btn btn-primary btn-sm pull-right",
				"onclick"=>"js:document.location.href='/archive/restore/".$data->id."'"
			)
		); ?>
		<?php echo CHtml::button(
			"Delete",
			array(
				"class"=>"btn btn-danger btn-sm pull-right",
				"onclick"=>"js:if(confirm('Are you sure you want to delete this study?')){document.location.href='/authoring/delete/".$data->id. "'}"
			)
		); ?>


	<?php endforeach; ?>
</div>

<div class="view" style="width:360px;float:left;margin-right:30px">
	<h2>Multi Session Studies</h2>
	<?php foreach($multi as $data): ?>
	<?php echo CHtml::encode($data->name); ?>
		<?php echo CHtml::button(
			"Restore",
			array(
				"class"=>"btn btn-primary btn-sm pull-right",
				"onclick"=>"js:document.location.href='/archive/restore/".$data->id."'"
			)
		); ?>
		<?php echo CHtml::button(
			"Delete",
			array(
				"class"=>"btn btn-danger btn-sm pull-right",
				"onclick"=>"js:if(confirm('Are you sure you want to delete this study?')){document.location.href='/authoring/delete/".$data->id. "'}"
			)
		); ?>

	<?php endforeach; ?>
</div>