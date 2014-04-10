<?php
Yii::app()->clientScript->registerScript('delete', "
jQuery('a.delete').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		if(confirm('Do really want to delete ' +$(this).parent().parent().children(':first-child').text()+'?')){
		$.get(url,function(data){
			 $('#userlist').html(data);
		 });
		}
		return false;
});
");
Yii::app()->clientScript->registerScript('update', "
jQuery('a.update').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		$.get(url,function(data){
			 $('#edit-user').html(data);
		 });
		return false;
});
");
Yii::app()->clientScript->registerScript('link', "
jQuery('a.link').click(function() {

		var url = $(this).attr('href');
		//  do your post request here


		$.get(url,function(data){
			 $('#reset-link').html(data);
		 });
		return false;
});
");
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'users-grid',
	'dataProvider'=>$dataProvider,
	'pager'=>array(
		'header'=> '',
	),
	'cssFile'=>false,
	'summaryText'=>'',
	'columns'=>array(
		'name',
		'email',
		array(
			'name'=>'permissions',
			'header'=>'Permission',
			'type'=>'raw',
			'value'=>'$data->permission',
		),
		array
		(
			'class'=>'CButtonColumn',
			'template'=>'{link}{update}{delete}',
			'buttons'=>array
			(
				'delete' => array
				(
					'label' => '<span class="fui-cross"></i>',
					'imageUrl' => false,
					'url'=>'Yii::app()->createUrl("/admin/userdelete", array("userId"=>$data->id, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'delete'),
				),
				'update' => array
				(
					'label' => '<span class="fui-new"></i>',
					'imageUrl' => false,
					'url'=>'Yii::app()->createUrl("/admin/useredit", array("userId"=>$data->id, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'update'),
				),
				'link' => array
				(
					'label' => '<span class="fui-cmd"></i>',
					'imageUrl' => false,
					'url'=>'Yii::app()->createUrl("/admin/getlink", array("email"=>$data->email, "_"=>"'.uniqid().'"))',
					'options'=>array('class'=>'link'),
				),
			),

		),
	),
));
?>
<div class="col-sm-12" id="reset-link"></div>