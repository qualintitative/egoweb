<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

?>

<div class="row">
  <div class="col-sm-6">
    <div class="card">
  
      <div class="card-header bg-success text-white">
        Import Study
      </div>
      <div class="card-body">
        <?= Html::beginForm(['/import-export/importstudy'], 'post', array('id' => 'importForm', 'enctype' => 'multipart/form-data')) ?>
        <div class="form-group">
          <div class="col-md-12">
            <input id="userfile" name="files[]" class="form-control" type="file" multiple accept=".study, .xml" />
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-12">
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
      <div class="card-header bg-info text-white">
        Export Study
      </div>
      <div id="export-panel" class="card-body">
        <?= Html::beginForm(['/import-export/exportstudy'], 'post', array('id' => 'export')) ?>
        <?= Html::dropDownList(
    'studyId',
    '',
    ArrayHelper::map($studies, 'id', 'name'),
    ['class' => 'form-control',
        'empty' => 'Select',
        'prompt'=>'Select',
        'onchange' => "js:getInterviews(\$(this), '#export-interviews')",
        ]
) ?>
    
        <div id="export-interviews"></div>
        <div id="exportNotice" class="col-sm-12 alert alert-success" style="display:none"></div>
        <div id="exportError" class="col-sm-12 alert alert-danger" style="display:none"></div>
        <div class="progress mb-3">
          <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="form-group">
          <div class="col-lg-4">
            <button  id="sendExport" class="btn btn-primary" onclick="exportEgo(); return false;">Export</button>
          </div>
        </div>
        <?= Html::endForm() ?>
      </div>

    </div>
  </div>
  <div class="col-sm-6">
    <div class="card">

      <div class="card-header bg-secondary text-white">
        Save External Server Credentials
      </div>
      <div class="card-body">
        <?= Html::beginForm([''], 'post', array('id' => 'sendForm')) ?>
        <div class="form-group row">
          <label class="col-sm-4">Server Address</label>
          <div class='col-sm-8'>
            <input class="form-control" name="Server[address]" id="sAddress">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-3">User Name</label>
          <div class='col-sm-3'>
            <input class="form-control" id="userName" name="Server[username]">
          </div>
          <label class="col-sm-3">Password</label>
          <div class='col-sm-3'>
            <input type="password" class="form-control" id="userPass" name="Server[password]">
          </div>
        </div>
        <div class="form-group row">
          <div class="col-sm-2">
            <button class="btn btn-success" onclick="authenticate(); return false;">Save</button>
          </div>
        </div>
        <?= Html::endForm() ?>
      </div>


      <div class="card-header bg-secondary text-white">
        Send Study to Server
      </div>
      <div class="card-body">
        <p>* Multi-session data will be lost.  Use export study function and import multiple study files to preserve multi-session data.</p>
        <?= Html::beginForm([''], 'post', array('id' => 'syncForm')) ?>
        <div class="row mb-3">
          <label class="col-sm-3">Server</label>
          <div class='col-sm-7'>
          <?= Html::dropDownList(
            'serverId',
            '',
            ArrayHelper::map($servers, 'ID', 'ADDRESS'),
            ['class' => 'form-control',
            'prompt'=>'Select Server (' . count($servers) . ')',
            'id' => 'serverAddress',
            'onchange' => "checkDelete()",

            ]
        ) ?>
          </div>
          <a id="deleteServer" style="display:none" class="btn btn-xs pull-right btn-danger" href="javascript:void(0);" onclick="deleteServer($('#serverAddress option:selected').val())">Delete</a>
        </div>
        <div class="form-group row">
          <label class="col-sm-3">Study</label>
          <div class='col-sm-9'>
            <?= Html::dropDownList(
                'studyId',
                '',
                ArrayHelper::map($studies, 'id', 'name'),
                ['class' => 'form-control',
            'prompt'=>'Select',
            'onchange' => "js:getInterviews(\$(this),'#send-interviews')",
            'id' => 'sendStudy',
            ]
            ) ?>
          </div>
          <div id="send-interviews"></div>

          <div id="sendNotice" class="col-sm-12 alert alert-success" style="display:none"></div>
          <div id="sendError" class="col-sm-12 alert alert-danger" style="display:none"></div>
          <div class="progress" style="clear:both">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <textarea id="sendJson" class="hidden"></textarea>
        </div>
        <?= Html::endForm() ?>
        <div class="col-sm-2" style="clear:both">
            <button id="sendSync" class="btn btn-primary" onclick="getData();return false;">Send</button>
          </div>
      </div>
    </div>
  </div>
</div>

  <script>
    servers = <?php echo json_encode($servers); ?>;

    function getInterviews(dropdown, container) {
      $.get('<?= Url::to(['/import-export/ajaxinterviews']); ?>' + "/" + dropdown.val(), function(data) {
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

        
        return $.post('<?= Url::to(['/import-export/send']); ?>' + "/" + $("#sendStudy option:selected").val(), {
            "YII_CSRF_TOKEN": $("input[name='YII_CSRF_TOKEN']").val(),
            "serverId": $("#serverAddress option:selected").val(),
            "export[]": $(thisInt).val()
          })
          .done(function(res) {
            $("#sendNotice").html($("#sendNotice").html() + "<br>" + "Prepared interview... ");
            if (!servers[$("#serverAddress option:selected").val()].ADDRESS.match("http"))
              servers[$("#serverAddress option:selected").val()].ADDRESS = 'http://' + servers[$("#serverAddress option:selected").val()].ADDRESS;
            console.log(servers[$("#serverAddress option:selected").val()].ADDRESS.charAt(servers[$("#serverAddress option:selected").val()].ADDRESS.length-1))
            if(servers[$("#serverAddress option:selected").val()].ADDRESS.charAt(servers[$("#serverAddress option:selected").val()].ADDRESS.length-1) == "/")
            servers[$("#serverAddress option:selected").val()].ADDRESS = servers[$("#serverAddress option:selected").val()].ADDRESS.slice(0,-1);
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
          url: rootUrl + '/import-export/ajaxexport/',
          data: {
            "interviewId": $(thisInt).val(),
            "YII_CSRF_TOKEN": $("input[name='YII_CSRF_TOKEN']").val()
          },
          success: function(msg) {
            finished++;
            $("#export-panel .progress-bar").width((finished / total * 100) + "%");
            $("#exportError").hide();
            $("#exportNotice").show();
            $("#exportNotice").html($("#exportNotice").html() + msg);
          },
          fail: function(XMLHttpRequest, textStatus, errorThrown) {
            $("#exportNotice").hide();
            $("#exportError").show();
            $("#exportError").html(errorThrown);
          }

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
      if(!id)
        return false;
      if (confirm("Do you want to delete this server?")) {
        $.post("<?= Url::to(['import-export/deleteserver']) ?>", {
          "serverId": id,
          "YII_CSRF_TOKEN": $("[name*='YII_CSRF_TOKEN']").val()
        }, function(data) {
          location.reload();
        });
      }
    }

    function checkDelete()
    {
      if($('#serverAddress option:selected').val())
        $("#deleteServer").show();
      else
        $("#deleteServer").hide();
    }
  </script>