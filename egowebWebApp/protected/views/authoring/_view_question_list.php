<?php Yii::app()->clientScript->registerCoreScript('jquery.ui'); ?>
<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view_question',
	'summaryText'=>'',
)); ?>