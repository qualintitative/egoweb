<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
?>

<div class="card">
  <div class="card-header bg-success text-white">
    Import Study
  </div>

  <div class="card-body">
  <?= Html::beginForm(['/importexport/importstudy'], 'post', array('id' => 'importForm', 'enctype' => 'multipart/form-data')) ?>

    <div class="form-group">
      <div class="col-lg-3">
        <input id="userfile" name="files[]" class="form-control" type="file" multiple accept=".study, .xml" />
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
    <?= Html::endForm() ?>
  </div>
</div>

<div class="card">
  <div class="card-header bg-warning">
    Replicate Study
  </div>

  <div class="card-body">
  <?= Html::beginForm(['/importexport/replicate'], 'post', array('id' => 'replicate')) ?>

    <div class="form-group">
      <div class="col-lg-3">
      <?= Html::dropDownList(
        'studyId',
        '',
        ArrayHelper::map($studies, 'id', 'name'),
        ['class' => 'form-control',]
        ) ?>
      </div>
    </div>
    <div class="form-group">
      <div class="col-lg-3">
        <?php echo Html::input('text','name', '', array('class' => "form-control", "placeholder" => "new name")); ?>
      </div>
    </div>
    <div class="form-group">
      <div class="col-lg-4 ">
        <button class="btn btn-warning">Replicate</button>
      </div>
    </div>
    <?= Html::endForm() ?>
  </div>
</div>


<div id="export-panel" class="card">
  <div class="card-header bg-info text-white">
    Export Study
  </div>
  <div class="card-body">
  <?= Html::beginForm(['/importexport/exportstudy'], 'post', array('id' => 'export')) ?>
  <?= Html::dropDownList(
        'studyId',
        '',
        ArrayHelper::map($studies, 'id', 'name'),
        ['class' => 'form-control',
        'empty' => 'Select',
        'onchange' => "js:getInterviews(\$(this), '#export-interviews')",
        ]
        ) ?>
    
    <div id="export-interviews"></div>
    <div id="exportNotice" class="col-sm-12 alert alert-success" style="display:none"></div>
    <div id="exportError" class="col-sm-12 alert alert-danger" style="display:none"></div>
    <div class="progress" style="clear:both">
      <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
      </div>
    </div>
    <div class="form-group">
      <div class="col-lg-4 ">
        <button  id="sendExport" class="btn btn-info" onclick="exportEgo(); return false;">Export</button>
      </div>
    </div>
    <?= Html::endForm() ?>
  </div>
</div>

<div class="card">
  <div class="card-header bg-info text-white">
    Save External Server Credentials
  </div>
  <div class="card-body">
  <?= Html::beginForm([''], 'post', array('id' => 'sendForm')) ?>

    <div class="form-group">
      <label class="col-sm-2">User Name</label>
      <div class='col-sm-4'>
        <input class="form-control" id="userName" name="Server[username]">
      </div>
      <label class="col-sm-2">Password</label>
      <div class='col-sm-4'>
        <input type="password" class="form-control" id="userPass" name="Server[password]">
      </div>
    </div>
    <br>
    <div class="form-group">
      <label class="col-sm-2">Server Address</label>
      <div class='col-sm-8'>
        <input class="form-control" name="Server[address]" id="sAddress">
      </div>
      <div class="col-sm-2">
        <button class="btn btn-success" onclick="authenticate(); return false;">Save</button>
      </div>
    </div>
    <?= Html::endForm() ?>
    <br><br>
    <ul class="list-group">
      <?php foreach ($servers as $server) : ?>
        <li class="list-group-item"><?php echo $server->address; ?>
          <a class="btn btn-xs pull-right btn-danger" href="javascript:void(0);" onclick="deleteServer(<?php echo $server->id; ?>)">Delete</a>
        </li>
      <?php endforeach; ?>
      <ul>
  </div>
</div>

<div class="card">
  <div class="card-header bg-info text-white">
    Send Study to Server
  </div>
  <div class="card-body">
  <?= Html::beginForm([''], 'post', array('id' => 'syncForm')) ?>
    <div class="form-group">
      <label class="col-sm-2">Server Address</label>
      <div class='col-sm-10'>
      <?= Html::dropDownList(
        'serverId',
        '',
        ArrayHelper::map($servers, 'id', 'address'),
        ['class' => 'form-control',
        'id' => 'serverAddress',
        ]
        ) ?>
      </div>
    </div>
    <br>
    <div class="form-group">
      <label class="col-sm-2">Study</label>
      <div class='col-sm-10'>
      <?= Html::dropDownList(
        'studyId',
        '',
        ArrayHelper::map($studies, 'id', 'name'),
        ['class' => 'form-control',
        'onchange' => "js:getInterviews(\$(this),'#send-interviews')",
        'id' => 'sendStudy',
        ]
        ) ?>
      </div>
      <br>



      <div id="send-interviews"></div>

      <div id="sendNotice" class="col-sm-12 alert alert-success" style="display:none"></div>
      <div id="sendError" class="col-sm-12 alert alert-danger" style="display:none"></div>
      <div class="progress" style="clear:both">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
        </div>
      </div>
      <div class="col-sm-2" style="clear:both">
        <button id="sendSync" class="btn btn-info" onclick="getData();return false;">Send</button>
      </div>
      <?= Html::endForm() ?>
      <textarea id="sendJson" class="hidden"></textarea>
    </div>
  </div>

  <script>
    servers = <?php echo json_encode($servers); ?>;

    function getInterviews(dropdown, container) {
      $.get('<?= Url::to(['/importexport/ajaxinterviews']); ?>' + "/" + dropdown.val(), function(data) {
        $("#sendError").hide();
        $("#sendNotice").hide();
        $(container).html(data);
      });
    }

    function authenticate() {
      url = $("#sAddress").val();
      if (!url.match("http"))
        url = "http://" + url;
      $.ajax({
        type: "POST",
        url: url + '/mobile/authenticate/',
        data: {
          "LoginForm[username]": $("#userName").val(),
          "LoginForm[password]": $("#userPass").val()
        },
        success: function(msg) {
          if (msg != "failed") {
            $("#sendForm").submit();
          } else {
            alert("Authentication failed");
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          alert("Can't connect to server");
        }
      });
    }
    var studies = [];

    function getData() {
      var finished = 0;
      $(".progress-bar").width(0);
      $("#sendError").hide();
      $("#sendNotice").show();
      $("#sendNotice").html("Preparing data to send..");
      $("#sendSync").prop("disabled", true);
      var total = $("#send-interviews .export:checked").length;
      var batchSize = 1;
      var interviews = $("#send-interviews .export:checked");
      if (interviews.length == 0) {
        var x = document.createElement("INPUT");
        interviews = [x];
        total = 1;
        console.log(interviews.length)
      }
      var batchPromiseRecursive = function() {
        if (interviews.length == 0) {
          return;
        }
        var thisInt = interviews.splice(0, batchSize);
        console.log($("exporting", thisInt).val());

        
        return $.post('<?= Url::to(['/importexport/send']); ?>' + "/" + $("#sendStudy option:selected").val(), {
            "YII_CSRF_TOKEN": $("input[name='YII_CSRF_TOKEN']").val(),
            "serverId": $("#serverAddress option:selected").val(),
            "export[]": $(thisInt).val()
          })
          .done(function(res) {
            $("#sendNotice").html($("#sendNotice").html() + "<br>" + "Prepared interview... ");
            if (!servers[$("#serverAddress option:selected").val()].ADDRESS.match("http"))
              servers[$("#serverAddress option:selected").val()].ADDRESS = 'http://' + servers[$("#serverAddress option:selected").val()].ADDRESS;
            $("#sendJson").val(res);
            return $.ajax({
              type: "POST",
              url: servers[$("#serverAddress option:selected").val()].ADDRESS + '/mobile/syncData/',
              data: {
                "LoginForm[username]": servers[$("#serverAddress option:selected").val()].USERNAME,
                "LoginForm[password]": servers[$("#serverAddress option:selected").val()].PASSWORD,
                "data": $("#sendJson").val()
              },
              success: function(msg) {
                finished++;
                msg = "Processed " + finished + " / " + total + " interviews: " + msg;
                $(".progress-bar").width((finished / total * 100) + "%");
                $("#sendError").hide();
                $("#sendNotice").show();
                $("#sendNotice").html($("#sendNotice").html() + "<br>" + msg);
              },
              error: function(XMLHttpRequest, textStatus, errorThrown) {
                $("#sendNotice").hide();
                $("#sendError").show();
                $("#sendError").html("Failed");
              }
            }).then(function() {
            return batchPromiseRecursive();
          });
            // Do something after each batch finishes.
            // Update a progress bar is probably a good idea.
          })
          .fail(function(e) {
            // if a batch fails, say server returns 500,
            // do something here.
          })
          .then(function() {
           // return batchPromiseRecursive();
          });
      }

      batchPromiseRecursive().then(function() {
        console.log(studies);
        $("#sendSync").prop("disabled", false);
      });

    }

    // Batch export
    function exportEgo() {
      var finished = 0;
      $(".progress-bar").width(0);
      $("#sendError").hide();
      $("#sendNotice").show();
      $("#exportNotice").html("Preparing data to export..");
      $("#sendExport").prop("disabled", true);
      var total = $("#export-interviews .export:checked").length;
      var batchSize = 1;
      var interviews = $("#export-interviews .export:checked");
      if (interviews.length == 0) {
        var x = document.createElement("INPUT");
        interviews = [x];
        total = 0;
        console.log(interviews.length)
      }
      var batchPromiseRecursive = function() {
        if (interviews.length == 0) {
          return;
        }
        var thisInt = interviews.splice(0, batchSize);
        console.log("exporting", $(thisInt).val());
        if(total != 0){
              msg = "Processing " + (finished + 1) + " / " + total + " interviews: ";
        }else{
          msg = "Exporting study without interviews: ";
          total = 1;
        }
        $("#exportNotice").html($("#exportNotice").html() + "<br>" + msg);
        return $.ajax({
          type: "POST",
          url: rootUrl + '/importexport/ajaxexport/',
          data: {
            "interviewId": $(thisInt).val(),
            "YII_CSRF_TOKEN": $("input[name='YII_CSRF_TOKEN']").val()
          }}).success( function(msg) {
            finished++;
            $("#export-panel .progress-bar").width((finished / total * 100) + "%");
            $("#exportError").hide();
            $("#exportNotice").show();
            $("#exportNotice").html($("#exportNotice").html() + msg);
          }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            $("#exportNotice").hide();
            $("#exportError").show();
            $("#exportError").html(errorThrown);
          }).then(function() {
            return batchPromiseRecursive();
          });
      }
      batchPromiseRecursive().then(function() {
        console.log(studies);
        $("#sendExport").prop("disabled", false);
        $("#export").submit();
      });
    }

    function deleteServer(id) {
      if (confirm("Do you want to delete this server?")) {
        $.post("<?= Url::to(['importexport/deleteserver']) ?>", {
          "serverId": id,
          "YII_CSRF_TOKEN": $("[name*='YII_CSRF_TOKEN']").val()
        }, function(data) {
          location.reload();
        });
      }
    }
  </script>