<?php
/* @var $this ImportExportController */
?>

<div class="panel panel-success">
    <div class="panel-heading">
        Import Study
    </div>

    <div class="panel-body">
        <?php echo CHtml::form('/importExport/importstudy', 'post', array('id'=>'importForm','enctype'=>'multipart/form-data')); ?>
        <div class="form-group">
            <div class="col-lg-3">
                <input id="userfile" name="files[]" class="form-control" type="file" multiple/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-3">
                <input type="text" name="newName" class="form-control" placeholder="New Name (optional)">
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-4 ">
                <button class="btn btn-success">Import</button>
            </div>
        </div>
        </form>
    </div>
</div>

<br clear=all>
<br clear=all>

<div class="panel panel-warning">
    <div class="panel-heading">
        Replicate Study
    </div>

    <div class="panel-body">
        <?php
        // replicate study
        $form=$this->beginWidget('CActiveForm', array(
            'id'=>'replicate',
            'enableAjaxValidation'=>false,
            'action'=>'/importExport/replicate'
        ));
        ?>
        <div class="form-group">
            <div class="col-lg-3">
<?php
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist(
    'studyId',
    '',
    CHtml::listData(Study::model()->findAll($criteria), 'id', 'name'),
    array("class"=>"form-control")
);
?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-3">
                <?php echo CHtml::textField('name', '',array('class'=>"form-control", "placeholder"=>"new name")); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-4 ">
                <button class="btn btn-warning">Replicate</button>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>

<br clear=all>
<br clear=all>

<div class="panel panel-info">
  <div class="panel-heading">
    Export Study
  </div>
  <div class="panel-body">
<?php
// export study
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'export',
    'enableAjaxValidation'=>false,
    'action'=>'/importExport/exportstudy'
));
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist(
	'studyId',
	'',
	CHtml::listData(Study::model()->findAll($criteria),'id', 'name'),
	                array(
                        'empty' => 'Select',
                        'onchange'=>"js:getInterviews(\$(this), '#export-interviews')",
                        'class'=>'form-control'
                    )

);
?>
    <br>Include Response Data<br>
    <div id="export-interviews"></div>
    <div class="form-group">
      <div class="col-lg-4 ">
        <button class="btn btn-info">Export</button>
      </div>
    </div>
    <?php $this->endWidget(); ?>
  </div>
</div>

<div class="panel panel-info">
  <div class="panel-heading">
    Send Study to Server
  </div>
  <div class="panel-body">
<?php
// export study
$form=$this->beginWidget('CActiveForm', array(
    'id'=>'sendForm',
    'enableAjaxValidation'=>false,
));
$criteria=new CDbCriteria;
$criteria->order = 'name';
echo CHtml::dropdownlist(
	'studyId',
	'',
	CHtml::listData(Study::model()->findAll($criteria),'id', 'name'),
    array(
          'id'=>'sendStudy',
          'empty' => 'Select',
          'onchange'=>"js:getInterviews(\$(this),'#send-interviews')",
          'class'=>'form-control'
    )
);
?>
    <br>Include Response Data<br>
    <div id="send-interviews"></div>

    <div id="sendNotice" class="col-sm-12 alert alert-info"></div>
    <div class="form-group">
      <label class="col-sm-4">Server Address</label>
      <div class='col-sm-8'>
        <input class="form-control" id="serverAddress">
      </div>
      <div class="col-sm-4 ">
        <button class="btn btn-info" onclick="getData();return false;">Send</button>
      </div>
    </div>
    <?php $this->endWidget(); ?>
    <textarea id="sendJson" class="hidden"></textarea>
  </div>
</div>

<script>
function getInterviews(dropdown, container){
	$.get('/importExport/ajaxinterviews/' + dropdown.val(), function(data){$(container).html(data);});
}
function getData(){
  $.post('/importExport/send/' + $("#sendStudy option:selected").val(), $("#sendForm").serialize(), function(data){
    $("#sendJson").val(data);
    if(!$("#serverAddress").val().match("http"))
      $("#serverAddress").val('http://'+$("#serverAddress").val())
    $.post($("#serverAddress").val()+ '/mobile/uploadData/', {"data":$("#sendJson").val()}, function(data){
      $("#sendNotice").html(data);
    });
  });
}

//On import study form submit
/*
$( "#importForm" ).submit(function( event) {
    var userfile = document.getElementById('userfile').files[0];

    if(userfile && userfile.size < <?php Yii::app()->params['maxUploadFileSize']; ?> ) { //This size is in bytes.

        var res_field = document.getElementById('userfile').value;
        var extension = res_field.substr(res_field.lastIndexOf('.') + 1).toLowerCase();
        var allowedExtensions = ['study'];
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
});*/


</script>
