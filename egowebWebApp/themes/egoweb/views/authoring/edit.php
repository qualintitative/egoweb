<?php
/* @var $this StudyController */
/* @var $model Study */

?>
<div class="container">
    <h1 class="mbl">Study Settings</h1>
    <ul class="nav nav-tabs nav-justified">
        <li class="active"><a href="#study" data-toggle="tab">Study</a></li>
        <li><a href="#interviewers" data-toggle="tab">Assign Interviews</a></li>
        <li><a href="#alterList" data-toggle="tab">Pre-defined Alterlist</a></li>
        <li><a href="#variable-alter-prompts" data-toggle="tab">Variable Alter Prompts</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="study">
            <?php echo $this->renderPartial('_form_study_settings', array('model'=>$model)); ?>
        </div>
        <div class="tab-pane" id="interviewers">
            <?php
            $dataProvider=new CActiveDataProvider('Interviewer');
            $this->renderPartial('_view_study_interviewers', array('dataProvider'=>$dataProvider, 'ajax'=>true), false, false);
            $interviewer = new Interviewer;
            $this->renderPartial('_form_study_interviewers', array('dataProvider'=>$dataProvider, 'model'=>$interviewer, 'studyId'=>$model->id, 'ajax'=>true), false, false);
            ?>
        </div>
        <div class="tab-pane" id="alterList" >
            <?php
                    $criteria=new CDbCriteria;
                    $criteria=array(
                            'condition'=>"studyId = " . $model->id,
                            'order'=>'ordering',
                    );
                    $dataProvider=new CActiveDataProvider('AlterList',array(
                            'criteria'=>$criteria,
                    ));
                    $this->renderPartial('_view_alter_list', array('dataProvider'=>$dataProvider, 'model'=>$model, 'studyId'=>$model->id, 'ajax'=>true), false, false);
            ?>
            <div id="showLink"></div>
            <?php
            $alterList = new AlterList;
            $form=$this->beginWidget('CActiveForm', array(
                    'id'=>'add-alter-form',
                    'enableAjaxValidation'=>true,
                    'htmlOptions'=>array('class'=>'mbl')
            ));
            ?>
                
                <?php echo $form->hiddenField($alterList,'id',array('value'=>$alterList->id)); ?>
                <?php echo $form->hiddenField($alterList,'studyId',array('value'=>$model->id)); ?>
                <?php echo CHtml::label('Add new alter','interviewerId',array('class'=>'control-label')); ?>
                <div class="form-inline">
                    <div class="form-group">
                        <?php echo $form->labelEx($alterList,'name',array('class'=>'control-label')); ?>
                        <?php echo $form->textField($alterList,'name',array('class'=>'form-control input-lg')); ?>
                        <?php echo $form->error($alterList,'name'); ?>
                    </div>
                    <div class="form-group">
                        <?php echo $form->labelEx($alterList,'email',array('class'=>'control-label')); ?>
                        <?php echo $form->textField($alterList,'email',array('class'=>'form-control input-lg')); ?>
                        <?php echo $form->error($alterList,'email'); ?>
                    </div>
                    <?php
                    $interviewerIds = q("SELECT interviewerId FROM interviewers WHERE studyId = " . $model->id)->queryColumn();
                    $interviewers = array();
                    foreach($interviewerIds as $interviewerId){
                            $interviewers[$interviewerId] = User::getName($interviewerId);
                    }
                    ?>
                    <?php echo $form->dropdownlist(
                            $alterList,
                            'interviewerId',
                            $interviewers,
                            array('empty' => 'None','class'=>'form-control input-lg')
                    ); ?>
                    <?php echo CHtml::ajaxSubmitButton ("Add Alter",
                        CController::createUrl('ajaxupdate'),
                        array('update' => '#alterList',),
                        array('id'=>uniqid(), 'live'=>false,'class'=>'btn btn-primary btn-lg'));
                    ?>
		</div>
            <?php $this->endWidget(); ?>
        </div>
        <div class="tab-pane" id="variable-alter-prompts">
            <div id="edit-alterList"></div>
            <div id="alterPrompt" >
                <?php
                        $criteria=new CDbCriteria;
                        $criteria=array(
                                'condition'=>"studyId = " . $model->id,
                        );
                        $dataProvider=new CActiveDataProvider('AlterPrompt',array(
                                'criteria'=>$criteria,
                        ));
                        $this->renderPartial('_view_alter_prompt', array('dataProvider'=>$dataProvider, 'model'=>$model, 'studyId'=>$model->id, 'ajax'=>true), false, false);
                ?>
            </div>
            <?php
            $alterPrompt = new AlterPrompt;
            $form=$this->beginWidget('CActiveForm', array(
                    'id'=>'add-alter-prompt-form',
                    'enableAjaxValidation'=>true,
                    'htmlOptions'=>array('class'=>'mbl')
            ));
            ?>
                <?php echo $form->hiddenField($alterPrompt,'id',array('value'=>$alterPrompt->id)); ?>
                <?php echo $form->hiddenField($alterPrompt,'studyId',array('value'=>$model->id)); ?>
                <?php echo CHtml::label('Add new alter prompt','',array('class'=>'control-label')); ?>
                <div class="form-inline">
                    <div class="form-group">
                        <?php echo CHtml::label('After','afterAltersEntered',array('class'=>'control-label')); ?>
                        <?php echo $form->textField($alterPrompt,'afterAltersEntered',array('class'=>'form-control input-lg')); ?>
                        <?php echo $form->error($alterPrompt,'afterAltersEntered'); ?>
                    </div>
                    <div class="form-group">
                        <?php echo CHtml::label('alters display','display',array('class'=>'control-label')); ?>
                        <?php echo $form->textField($alterPrompt,'display',array('class'=>'form-control input-lg')); ?>
                        <?php echo $form->error($alterPrompt,'display'); ?>
                    </div>
                    <?php echo CHtml::ajaxSubmitButton ("Add",
                            CController::createUrl('ajaxupdate'),
                            array('update' => '#alterPrompt'),
                            array('id'=>uniqid(), 'live'=>false,'class'=>'btn btn-primary btn-lg'));
                    ?>
		</div>
            <?php $this->endWidget(); ?>
            <div id="edit-alterPrompt"></div>
        </div>
    </div>
</div>