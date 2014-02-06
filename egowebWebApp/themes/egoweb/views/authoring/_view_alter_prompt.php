<?php
Yii::app()->clientScript->registerScript('delete-prompt', "
jQuery('a.delete-prompt').click(function() {

        var url = $(this).attr('href');
        //  do your post request here


        $.get(url,function(data){
             $('#alterPrompt').html(data);
         });
        return false;
});
");
Yii::app()->clientScript->registerScript('update-prompt', "
jQuery('a.update-prompt').click(function() {

        var url = $(this).attr('href');
        //  do your post request here


        $.get(url,function(data){
             $('#edit-alterPrompt').html(data);
         });
        return false;
});
");
?>
<div style="width:100%; float:left">
<label>Variable Alter Prompts</label>

<?php
if(isset($model))
    echo $model->getError('name');
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'alters-prompt-grid',
	'dataProvider'=>$dataProvider,
	'cssFile'=>false,
		'columns'=>array(
        array(
            'name' =>'after',
            'value' => '$data->afterAltersEntered',
            'type' => 'raw',
            'htmlOptions' => array('style'=>'width:60px;'),
        ),
		'display',
		array
		(
    		'class'=>'CButtonColumn',
    		'template'=>'{update}{delete}',
    		'buttons'=>array
    		(
        		'delete' => array
        		(
            		'url'=>'Yii::app()->createUrl("/authoring/ajaxdelete", array("AlterPrompt[id]"=>$data->id, "_"=>"'.uniqid().'"))',
            		'options'=>array('class'=>'delete-prompt'),
        		),
        		'update' => array
        		(
            		'url'=>'Yii::app()->createUrl("/authoring/ajaxload", array("alterPromptId"=>$data->id, "form"=>"_form_alter_prompt_edit", "_"=>"'.uniqid().'"))',
            		'options'=>array('class'=>'update-prompt'),
        		),
    		),

		),
	),
	'summaryText'=>'',
));
?>
</div>
