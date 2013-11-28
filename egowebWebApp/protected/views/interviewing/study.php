<div class="view" style="width:360px;float:left;">
<h2><a href="/interviewing/<?php echo $studyId; ?>">Start new interview</a></h2>

<h2>Continue interview</h2>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'interviews',
	'dataProvider'=>$dataProvider,
	'pager'=>array(
		'header'=> '',
	),
	'summaryText'=>'',
	'columns'=>array(
		array(
			'name'=>'id',
			'header'=>'Interview',
			'type'=>'raw',
			'value'=>'CHtml::link($data->id . ": " .Study::getName($data->studyId)." - ".Interview::getRespondant($data->id), Yii::app()->createUrl("/interviewing/".$data->studyId."?interviewId=".$data->id."&page=".$data->completed))',
		),
	),
));
?>
</div>
