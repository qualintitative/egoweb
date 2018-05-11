
<div style="width:100%; float:left">
<h3>Variable Alter Prompts</h3>

<?php
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
                    'options'=>array('class'=>'delete-alter-prompt-' . uniqid()),
        		),
        		'update' => array
        		(
            		'url'=>'"javascript:updatePrompt(\"" . Yii::app()->createUrl("/authoring/ajaxload", array("alterPromptId"=>$data->id, "form"=>"_form_alter_prompt_edit", "_"=>"'.uniqid().'")) . "\")"',
            		'options'=>array('class'=>'update-prompt'),
        		),
    		),

		),
	),
	'summaryText'=>'',
));
?>
</div>
