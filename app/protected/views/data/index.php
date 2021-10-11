<?php
use yii\helpers\Html;
use app\models\MatchedAlters;
use app\models\User;
use yii\helpers\Url;
?>
<script>
function exportEgoLevel() {
    var total = $("input[type='checkbox'][name*='export']:checked").length;
    var finished = 0;
    var batchSize = 1;
    var interviews = $("input[type='checkbox'][name*='export']:checked");
    $(".progress-bar").width(0);
    var batchPromiseRecursive = function() {
        if (interviews.length == 0) {
            return;
        }
        var thisInt = interviews.splice(0, batchSize);
        var interviewId = $(thisInt).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        return $.ajax({
            type: "POST",
            url: rootUrl + "/data/exportegolevel",
            data: {
                studyId: $("#studyId").val(),
                interviewId: interviewId,
                expressionId: $("#expressionId").val(),
                YII_CSRF_TOKEN: $("input[name='YII_CSRF_TOKEN']").val()
            },
            success: function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%");
            }
        }).then(function() {
            return batchPromiseRecursive();
        });
    }
    batchPromiseRecursive().then(function() {
        $("#status").html("Done!");
        $('#analysis').attr('action', rootUrl + '/data/exportegolevelall');
        let interviewIds = [];
        $("input[type='checkbox'][name*='export']:checked").each(function() {
            interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
        });
        $('#analysis #interviewIds').val(interviewIds.join(","));
        $('#analysis').submit();
    });
}

function exportEgo() {
    var total = $("input[type='checkbox'][name*='export']:checked").length;
    var finished = 0;
    withAlters = 0;
    var batchSize = 1;
    var interviews = $("input[type='checkbox'][name*='export']:checked");
    if ($("#withAlters1").prop("checked") == true)
        withAlters = 1;
    $("#withAlters").val(withAlters);
    $(".progress-bar").width(0);
    var batchPromiseRecursive = function() {
        if (interviews.length == 0) {
            return;
        }
        var thisInt = interviews.splice(0, batchSize);
        var interviewId = $(thisInt).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        return $.ajax({
            type: "POST",
            url: rootUrl + "/data/exportegoalter",
            data: {
                studyId: $("#studyId").val(),
                interviewId: interviewId,
                withAlters: withAlters,
                expressionId: $("#expressionId").val(),
                YII_CSRF_TOKEN: $("input[name='YII_CSRF_TOKEN']").val()
            },
            success: function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%");
            }
        }).then(function() {
            return batchPromiseRecursive();
        });
    }
    batchPromiseRecursive().then(function() {
        $("#status").html("Done!");
        $('#analysis').attr('action', rootUrl + '/data/exportegoalterall');
        let interviewIds = [];
        $("input[type='checkbox'][name*='export']:checked").each(function() {
            interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
        });
        $('#analysis #interviewIds').val(interviewIds.join(","));
        $('#analysis').submit();
    });
}

function exportAlterPair() {
    var total = $("input[type='checkbox'][name*='export']:checked").length;
    var finished = 0;
    var batchSize = 1;
    var interviews = $("input[type='checkbox'][name*='export']:checked");
    withAlters = 0;
    if ($("#withAlters1").prop("checked") == true)
        withAlters = 1;
    $("#withAlters").val(withAlters);
    $(".progress-bar").width(0);
    var batchPromiseRecursive = function() {
        if (interviews.length == 0) {
            return;
        }
        var thisInt = interviews.splice(0, batchSize);
        var interviewId = $(thisInt).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        return $.ajax({
            type: "POST",
            url: rootUrl + "/data/exportalterpair",
            data: {
                studyId: $("#studyId").val(),
                interviewId: interviewId,
                withAlters: withAlters,
                expressionId: $("#expressionId").val(),
                YII_CSRF_TOKEN: $("input[name='YII_CSRF_TOKEN']").val()
            },
            success: function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%");
            }
        }).then(function() {
            return batchPromiseRecursive();
        });

    }
    batchPromiseRecursive().then(function() {
        $("#status").html("Done!");
        $('#analysis').attr('action', rootUrl + '/data/exportalterpairall');
        let interviewIds = [];
        $("input[type='checkbox'][name*='export']:checked").each(function() {
            interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
        });
        $('#analysis #interviewIds').val(interviewIds.join(","));
        $('#analysis').submit();
    });
}

function exportOther() {
    var total = $("input[type='checkbox'][name*='export']:checked").length;
    var finished = 0;
    var batchSize = 1;
    var interviews = $("input[type='checkbox'][name*='export']:checked");
    withAlters = 0;
    if ($("#withAlters1").prop("checked") == true)
        withAlters = 1;
    $("#withAlters").val(withAlters);
    $(".progress-bar").width(0);
    var batchPromiseRecursive = function() {
        if (interviews.length == 0) {
            return;
        }
        var thisInt = interviews.splice(0, batchSize);
        var interviewId = $(thisInt).attr("id").match(/\d+/g)[0];
        var d = new Date();
        start = d.getTime();
        return $.ajax({
            type: "POST",
            url: rootUrl + "/data/exportother",
            data: {
                studyId: $("#studyId").val(),
                interviewId: interviewId,
                YII_CSRF_TOKEN: $("input[name='YII_CSRF_TOKEN']").val()
            },
            success: function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%");
            }
        }).then(function() {
            return batchPromiseRecursive();
        });

    }
    batchPromiseRecursive().then(function() {
        $("#status").html("Done!");
        $('#analysis').attr('action', rootUrl + '/data/exportotherall');
        let interviewIds = [];
        $("input[type='checkbox'][name*='export']:checked").each(function() {
            interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
        });
        $('#analysis #interviewIds').val(interviewIds.join(","));
        $('#analysis').submit();
    });
}

function exportAlterList() {
    $('#analysis').attr('action', rootUrl + '/data/exportalterlist');
    $('#analysis').submit();
}

function deleteInterviews() {
    if (confirm("Are you sure you want to DELETE these interviews?  The data will not be retrievable.")) {
        let interviewIds = [];
        $("input[type='checkbox'][name*='export']:checked").each(function() {
            interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
        });
        $('#analysis #interviewIds').val(interviewIds.join(","));
        $('#analysis').attr('action', rootUrl + '/data/deleteinterviews');
        $('#analysis').submit();
    }
}
</script>

<div class="card">
    <div class="card-body">
        <div>
            <div class="col-sm-4 float-right">
                <a class="btn btn-sm btn-info float-right" href="/authoring/<?php echo $study->id; ?>">Authoring</a>
            </div>
            <div class="col-sm-8 float-left mb-3">
                <input type="checkbox" id="withAlters1" checked> Include Alter Names
      
                Network Statistics
                <?php echo Html::dropDownList('expressionId', '', $expressions, ['prompt' => '(none)',
                'onchange' => '$("#expressionId").val($(this).val())']);
                ?>
            </div>

        </div>
        <div id="status"></div>
        <div class="progress row mb-3">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40"
                aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <button onclick='exportEgoLevel()' class='authorButton'>Export Ego Level Data</button>
        <button onclick='exportEgo()' class='authorButton'>Export Ego Alter Data</button>
        <button onclick='exportAlterPair()' class='authorButton'>Export Alter Pair Data</button>
        <button onclick='exportOther()' class='authorButton'>Export Other Specify Data</button>
        <button onclick='deleteInterviews()' class='authorButton btn-danger pull-right'>Delete Interviews</button>

    </div>
</div>


<table class="table table-striped table-bordered table-list">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="$('input[type=checkbox]').prop('checked', $(this).prop('checked'))"
                    data-toggle="tooltip" data-placement="top" title="Select All"></th>
            <th>Ego ID</th>
            <th class="d-none d-sm-table-cell">Started</th>
            <th class="d-none d-sm-table-cell">Completed</th>
            <th class="d-none d-sm-table-cell">Dyad Match ID</th>
            <th class="d-none d-sm-table-cell">Match User</th>
            <th><em class="fa fa-cog"></em></th>

        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($study->interviews as $interview) {
            if ($interview->completed == -1) {
                $completed = "<span style='color:#0B0'>" . date("Y-m-d H:i:s", $interview->complete_date) . "</span>";
            } else {
                $completed = "";
            }
            $mark = "";
            $matchId = "";
            $matchUser = "";
            $match = MatchedAlters::find()
            ->where(new \yii\db\Expression("interviewId1 = $interview->id OR interviewId2 = $interview->id"))
            ->one();
            if ($match) {
                $mark = "class='success'";
                $matchId = $match->getMatchId();
                $matchU = User::findOne($match->userId);
                $matchUser = $matchU->name;
            }
            echo "<tr $mark>";
            echo "<td>" . Html::checkbox('export[' . $interview->id . ']', false, ['id'=>'export_' . $interview->id  ]) . "</td><td>" . $interview->egoId . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . \Yii::$app->formatter->asDate($interview->start_date, "php:Y-m-d H:i:s") . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . $completed . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . $matchId . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . $matchUser . "</td>";
            echo "<td>";
            if ($interview->completed == -1) {
                echo "<a class='btn btn-success btn-xs' href='" . Url::to(['/data/edit/' . $interview->id]) ."'>Edit</a>";
                echo "&nbsp;&nbsp;";
            }
            echo "<a class='btn btn-info btn-xs' href='" . Url::to(['/interview/' . $study->id . '/' . $interview->id . '#/page/0']) ."'>Review</a>";
            echo "&nbsp;&nbsp;";
            echo "<a class='btn btn-info btn-xs' href='" . Url::to(['/data/visualize/' . $interview->id]) ."'>Visualize</a>";
            echo "</tr>";
        }
        ?>

    </tbody>
</table>

<?= Html::beginForm([''], 'post', [ 'id'=>'analysis']) ?>
<?php
echo Html::hiddenInput('studyId', $study->id, [ 'id'=>'studyId']);
echo Html::hiddenInput('interviewIds', '', [ 'id'=>'interviewIds']);
echo Html::hiddenInput('expressionId', '', [ 'id'=>'expressionId']);
echo Html::hiddenInput('withAlters', "1", array('id' => 'withAlters'));
?>
<?= Html::endForm() ?>