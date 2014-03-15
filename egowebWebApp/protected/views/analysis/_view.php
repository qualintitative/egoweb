<?php
/* @var $this InterviewingController */
/* @var $data Answer */
?>


	<?php echo CHtml::link(
		CHtml::encode(Study::getName($data->id)),
		Yii::app()->createUrl('analysis/study/'.$data->id)
		); ?>
	<br />

