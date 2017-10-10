<div id="alterPrompt" >
<?php
    $criteria=new CDbCriteria;
    $criteria=array(
        'condition'=>  "studyId = " . $study->id . " AND questionId = " . $question->id,
    );
    $dataProvider=new CActiveDataProvider('AlterPrompt',array(
        'criteria'=>$criteria,
    ));

?>
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
                        //'url'=>'"javascript:deletePrompt(\"" . Yii::app()->createUrl("/authoring/ajaxdelete", array("AlterPrompt[id]"=>$data->id, "form"=>"_form_alter_prompt_edit", "_"=>"'.uniqid().'")) . "\")"',
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

</div>

<div style="float:left; width:100%;margin-top:15px;">
    <span class="smallheader">Add new alter prompt</span>
    <?php
        $alterPrompt = new AlterPrompt;
        $form=$this->beginWidget('CActiveForm', array(
            'id'=>'add-alter-prompt-form',
            'enableAjaxValidation'=>true,
        ));
    ?>
    <?php echo $form->hiddenField($alterPrompt,'id',array('value'=>$alterPrompt->id)); ?>
    <?php echo $form->hiddenField($alterPrompt,'studyId',array('value'=>$study->id)); ?>
    <?php echo $form->hiddenField($alterPrompt,'questionId',array('value'=>$question->id)); ?>
    <label style="float:left; padding:5px;">After</label>
    <?php echo $form->textField($alterPrompt,'afterAltersEntered', array('style'=>'width:20px;float:left')); ?>
    <label style="float:left; padding:5px;">alters, display </label>
    <?php echo $form->textField($alterPrompt,'display', array('style'=>'width:100px;float:left')); ?>
    <?php echo $form->error($alterPrompt,'afterAltersEntered'); ?>
    <?php echo $form->error($alterPrompt,'display'); ?>
    <?php echo CHtml::ajaxSubmitButton ("Add",
        CController::createUrl('ajaxupdate'),
        array('update' => '#alterPrompt'),
        array('id'=>uniqid(), 'live'=>false, 'style'=>'float:left; margin:3px 5px;', "class"=>"btn btn-primary btn-xs"));
    ?>
    <?php $this->endWidget(); ?>
    <div id="edit-alterPrompt" style="margin-top:15px;float:left;clear:both;"></div>
</div>
