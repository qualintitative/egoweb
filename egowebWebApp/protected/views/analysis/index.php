<?php
/* @var $this AnalysisController */
?>

<div class="view" style="width:360px;float:left;margin-right:30px">
<h2>Studies</h2>
<?php foreach($studies as $data): ?>
	<?php echo CHtml::link(
		CHtml::encode(Study::getName($data->id)),
		Yii::app()->createUrl('analysis/study/'.$data->id)
		); ?><br>
<?php endforeach; ?>
</div>