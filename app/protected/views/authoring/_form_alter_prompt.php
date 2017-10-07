<div id="alterPrompt" >
<?php
    $criteria=new CDbCriteria;
    $criteria=array(
        'condition'=>"studyId = " . $model->studyId,
    );
    $dataProviderAP=new CActiveDataProvider('AlterPrompt',array(
        'criteria'=>$criteria,
    ));
    $this->renderPartial('_view_alter_prompt', array('dataProvider'=>$dataProviderAP, 'model'=>$study, 'studyId'=>$model->studyId, 'ajax'=>true), false, false);
?>
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
    <?php echo $form->hiddenField($alterPrompt,'studyId',array('value'=>$model->id)); ?>
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
