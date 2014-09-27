<?php
/* @var $this ImportExportController */

?>
<h1>Import Study</h1>


<form id="importForm" enctype="multipart/form-data" method="POST" action="/importExport/importstudy">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <!-- Name of input element determines name in $_FILES array -->
    New Name (optional)
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?>" />
    <input id="userfile" name="userfile" type="file" />
    <input type="text" name="newName" />
    <input type="submit" value="Send File" />
</form>
<br clear=all>
<br clear=all>
<h1>Replicate Study</h1>
<?php
// replicate study
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'replicate',
    'enableAjaxValidation'=>false,
    'action'=>'/importExport/replicate'
));
?>
new name
<?php

echo CHtml::textField('name');
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist('studyId', '', CHtml::listData(Study::model()->findAll($criteria), 'id', 'name'));
echo CHtml::submitButton( 'Replicate'); 
$this->endWidget(); ?>
<br clear=all>
<br clear=all>
<h1>Export Study</h1>
<?php
// export study
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'export',
    'enableAjaxValidation'=>false,
    'action'=>'/importExport/exportstudy'
));
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist('studyId', '', CHtml::listData(Study::model()->findAll($criteria), 'id', 'name'));
echo CHtml::submitButton( 'Export'); 
$this->endWidget(); ?>

<script type="text/javascript">
//On import study form submit
$( "#importForm" ).submit(function( event) {
    var userfile = document.getElementById('userfile').files[0];

    if(userfile && userfile.size < <?php echo 'MAX = ' + Yii::app()->params['maxUploadFileSize']; ?> ) { //This size is in bytes.

        var res_field = document.getElementById('userfile').value;
        var extension = res_field.substr(res_field.lastIndexOf('.') + 1).toLowerCase();
        var allowedExtensions = ['xml'];
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
            $("#importForm").submit();
        }
    } else {
        //Prevent default and display error
        event.preventDefault();
        alert("Upload file cannot exceed <?php echo number_format(Yii::app()->params['maxUploadFileSize'] / 1048576, 1) . ' MB'; ?>");
        return false;
    }
});
</script>
