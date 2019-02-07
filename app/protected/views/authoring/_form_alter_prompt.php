<div id="alterPrompt" style="padding:20px">
<?php
    $criteria=new CDbCriteria;
    $criteria=array(
        'condition'=>  "studyId = " . $study->id . " AND questionId = " . $question->id,
    );
    $dataProvider=new CActiveDataProvider('AlterPrompt',array(
        'criteria'=>$criteria,
        'pagination'=>array(
            'pageSize'=>50,
        )
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
                      //  'url'=>'"javascript:deletePrompt(\"" . Yii::app()->createUrl("/authoring/ajaxdelete", array("AlterPrompt[id]"=>$data->id, "form"=>"_form_alter_prompt_edit", "_"=>"'.uniqid().'")) . "\")"',
                		'url'=>'Yii::app()->createUrl("/authoring/ajaxdelete", array("AlterPrompt[id]"=>$data->id, "_"=>"'.uniqid().'"))',
                		//'options'=>array('class'=>'delete-alter-prompt-' . uniqid()),
                    'options' => array(
                        'confirm' => 'Are you sure you want to delete this item?',
                        'ajax' => array(
                            'type' => 'POST',
                            'data'=> array("YII_CSRF_TOKEN"=> Yii::app()->request->csrfToken),
                            'url' => "js:$(this).attr('href')",
                            'success' => 'function(data){
                                if(data.response=="false"){
                                    alert(data.errorMessage);
                                }else{
                                   $("#data-' . $question->id . '").html(data);
                                }
                            }'
                        ),
                    ),
                  ),
/*

            		),*/
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


<div style="float:left; width:100%;margin-top:15px;">
    <span class="smallheader">Add new alter prompt</span>
    <?php
        $alterPrompt = new AlterPrompt;
        $form=$this->beginWidget('CActiveForm', array(
            'id'=>'add-alter-prompt-form',
            'enableAjaxValidation'=>false,
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
        array('update' => '#data-' . $question->id),
        array('id'=>uniqid(), 'live'=>false, 'style'=>'float:left; margin:3px 5px;', "class"=>"btn btn-primary btn-xs"));
    ?>
    <?php $this->endWidget(); ?>
    <br>
    <br>
    <br>
    <?php echo CHtml::form('/authoring/importprompts', 'post', array('id'=>'importListForm', 'enctype'=>'multipart/form-data')) ?>
        <!-- MAX_FILE_SIZE must precede the file input field -->
        <!-- Name of input element determines name in $_FILES array -->
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?>" />
        <input name="userfile" type="file" />
        <input type="hidden" name="studyId" value="<?= $study->id; ?>" />
        <input type="hidden" name="questionId" value="<?= $question->id; ?>" />
        <input class="btn btn-primary btn-xs" type="submit" value="Import Variable Prompts" />
    </form>
    <div id="edit-alterPrompt" style="margin-top:15px;float:left;clear:both;"></div>
</div>
<script type="text/javascript">
    //On import study form submit
    $( "#importListForm" ).submit(function( event) {
        var userfile = document.getElementById('userfile').files[0];

        if(userfile && userfile.size < <?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?> ) { //This size is in bytes.

            var res_field = document.getElementById('userfile').value;
            var extension = res_field.substr(res_field.lastIndexOf('.') + 1).toLowerCase();
            var allowedExtensions = ['csv'];
            event.preventDefault();
            if (res_field.length > 0)
            {
                if( allowedExtensions.indexOf(extension) === -1 )
                {
                    event.preventDefault();
                    alert('Invalid file Format. Only ' + allowedExtensions.join(', ') + ' allowed.');
                    return false;
                }
            }
            else{
                //Submit form
                $("#importListForm").submit();
            }
        } else {
            //Prevent default and display error
            event.preventDefault();
            alert("Upload file cannot exceed <?php echo number_format(Yii::app()->params['maxUploadFileSize'] / 1048576, 1) . ' MB'; ?>");
            return false;
        }
    });
</script>
