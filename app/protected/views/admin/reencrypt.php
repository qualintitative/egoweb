<?php

/* @var $this AdminController */
$this->pageTitle =  "Re-encryption Tool";

?>
<div id="finished" class="alert alert-success" style="display:none;">
  <?php echo $alert; ?>
  <b>You must change the config file, or you won't be able to log back in</b><br>
  Find the following text in your main.php:
  <code style="display:block;white-space:pre-wrap">
    'cryptAlgorithm' => 'blowfish',
    'encryptionKey' => '<?php echo $oldKey; ?>',
  </code>
  and change it to:
  <code id="newCode" style="display:block;white-space:pre-wrap">
  </code>
</div>
  <?php
  $form = $this->beginWidget('CActiveForm', array(
  	'id'=>'key-form',
  	'enableAjaxValidation'=>false,
  	'htmlOptions'=>array('class'=>'form-inline'),
  ));
  ?>
  <div id="encryptForm" class="form-group col-sm-12">
    <div class="alert alert-danger">Please make a backup of your database before starting.  <b>Do not continue if you cannot edit the config file /protected/config/main.php</b></div>
		<?php echo CHtml::Label("Encryption Key", "newKey", array("class" => "control-label")); ?>
		<?php echo CHtml::textField('newKey', $encKey, array("id"=>"newKey", "class"=>"form-control")); ?>
    <?php echo CHtml::submitButton('Encrypt', array("class"=>"btn btn-primary", "onclick"=>"reEncrypt(); return false;")); ?>

	</div>
  <?php $this->endWidget(); ?>

  <div id="notice" class="col-sm-12 alert alert-success" style="display:none"></div>
  <div id="progressBar" class="progress" style="clear:both; display:none;">
    <div class="progress-bar progress-bar-striped active" role="progressbar"
    aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
    </div>
  </div>
<script>
function reEncrypt(){
  if($("#newKey").val().trim().length != 16 && $("#newKey").val().trim().length != 24 && $("#newKey").val().trim().length != 32){
    alert("The encryption key must have a length of 16, 24, or 32 characters!");
    return false;
  }
  var finished = 0;
  interviews = <?php echo $interviews ?>;
  $(".progress-bar").width(0);
  $("#notice").show();
  $("#encryptForm").hide();
  var total = interviews.length;
  interviews.unshift("0");
  var batchPromiseRecursive = function() {
    // note splice is destructive, removing the first batch off
    // the array
    //var batch = studies.splice(0, batchSize);
    if (interviews.length == 0) {
      return $.Deferred().resolve().promise();
    }
    var thisInt = interviews.shift();
    console.log("re-encrypting", thisInt);
    $("#progressBar").show();


    return $.ajax({
          type: "POST",
          url: baseUrl + '/admin/redata/',
          data: {"interviewId":  thisInt, "newKey": $("#newKey").val().trim(),  "YII_CSRF_TOKEN":$("input[name='YII_CSRF_TOKEN']").val()}
        })
          .done(function(msg){

            msg = "Processed " + finished + " / " + total + " interviews: " + msg;
            $(".progress-bar").width((finished / total * 100) + "%");
            $("#notice").show();
            $("#notice").html(msg);
            finished++;
          }).then(function(){
            return batchPromiseRecursive();
          });

  }

  batchPromiseRecursive().then(function() {
    console.log("then..")
    $("#finished").show();
    $("#newCode").html("'cryptAlgorithm' => 'rijndael-128',\r\n'encryptionKey' => '" + $("#newKey").val() + "',");
  });
}
</script>
