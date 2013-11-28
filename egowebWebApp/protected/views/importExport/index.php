<?php
/* @var $this ImportExportController */

?>
<h1>Import Study</h1>


<form enctype="multipart/form-data" method="POST" action="/importExport/importstudy">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <!-- Name of input element determines name in $_FILES array -->
    New Name (optional)  <input name="userfile" type="file" /><input type="text" name="newName" />
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