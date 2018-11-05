<?php
/* @var $this DataController */
$this->pageTitle = "Dyad Matching";
?>

<div class="panel panel-default">
<div class="panel-heading">Studies</div>
<div class="panel-body">
<?php foreach($studies as $data): ?>
	<?php echo CHtml::link(
		CHtml::encode(Study::getName($data->id)),
		Yii::app()->createUrl('dyad/study/'.$data->id)
		); ?><br>
<?php endforeach; ?>
</div>
</div>
