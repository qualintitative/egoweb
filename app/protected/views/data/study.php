<script>
    function exportEgoStudy() {
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
                url: rootUrl + "/data/exportegostudy",
                data: {
                    studyId: $("#studyId").val(),
                    interviewId: interviewId,
                    expressionId: $("#expressionId").val(),
                    YII_CSRF_TOKEN: $("input[name='YII_CSRF_TOKEN']").val()
                }
            }).success(function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%");
            }).then(function(){
                return batchPromiseRecursive();
            });
        }
        batchPromiseRecursive().then(function() {
            $("#status").html("Done!");
            $('#analysis').attr('action', rootUrl + '/data/exportegostudyall');
            let interviewIds = [];
            $("input[type='checkbox'][name*='export']:checked").each(function(){
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
                }
            }).success(function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%");
            }).then(function(){
                return batchPromiseRecursive();
            });
        }
        batchPromiseRecursive().then(function() {
            $("#status").html("Done!");
            $('#analysis').attr('action', rootUrl + '/data/exportegoalterall');
            let interviewIds = [];
            $("input[type='checkbox'][name*='export']:checked").each(function(){
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
                }
            }).success(function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%"); 
            }).then(function(){
                return batchPromiseRecursive();
            });
            
        }
        batchPromiseRecursive().then(function() {
            $("#status").html("Done!");
            $('#analysis').attr('action', rootUrl + '/data/exportalterpairall');
            let interviewIds = [];
            $("input[type='checkbox'][name*='export']:checked").each(function(){
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
                }
            }).success(function(data) {
                finished++;
                $("#status").html(
                    "Processed " + finished + " / " + total + " interviews"
                );
                $(".progress-bar").width((finished / total * 100) + "%"); 
            }).then(function(){
                return batchPromiseRecursive();
            });
            
        }
        batchPromiseRecursive().then(function() {
            $("#status").html("Done!");
            $('#analysis').attr('action', rootUrl + '/data/exportotherall');
            let interviewIds = [];
            $("input[type='checkbox'][name*='export']:checked").each(function(){
                interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
            });
            $('#analysis #interviewIds').val(interviewIds.join(","));
            $('#analysis').submit();
        });
    }
/*
    function exportOther() {
        $('#analysis').attr('action', rootUrl + '/data/exportother');
        let interviewIds = [];
        $("input[type='checkbox'][name*='export']:checked").each(function(){
            interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
        });
        $('#analysis #interviewIds').val(interviewIds.join(","));
        $('#analysis').submit();
    }
*/
    function exportOtherLegacy() {
        $('#analysis').attr('action', rootUrl + '/data/legacyexportother');
        $('#analysis').submit();
    }

    function exportAlterList() {
        $('#analysis').attr('action', rootUrl + '/data/exportalterlist');
        $('#analysis').submit();
    }

    function matchAlters() {
        $('#analysis').attr('action', rootUrl + '/data/matching');
        $('#analysis').submit();
    }

    function deleteInterviews() {
        if (confirm("Are you sure you want to DELETE these interviews?  The data will not be retrievable.")) {
            let interviewIds = [];
            $("input[type='checkbox'][name*='export']:checked").each(function(){
                interviewIds.push($(this).attr("id").match(/\d+/g)[0]);
            });
            $('#analysis #interviewIds').val(interviewIds.join(","));
            $('#analysis').attr('action', rootUrl + '/data/deleteinterviews');
            $('#analysis').submit();
        }
    }
</script>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo $study->name; ?>
    </div>

    <div class="panel-body">
        <div id="status"></div>
        <div class="progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="form">
        <div class="form-group">
        <input type="checkbox" id="withAlters1"> Include Alter Names 
        </div>
        <div class="form-group">
        Network Statistics
        <?php echo CHtml::dropdownlist(
            'adjacencyExpressionId',
            "",
            $expressions,
            array(
                'empty' => '(none)',
                'onchange' => '$("#expressionId").val($(this).val())'
            )
        );
        ?>
        </div>
    </div>
        <button onclick='exportEgoStudy()' class='authorButton'>Export Ego Level Data</button>
        <button onclick='exportEgo()' class='authorButton'>Export Ego Alter Data</button>
        <button onclick='exportAlterPair()' class='authorButton'>Export Alter Pair Data</button>
        <button onclick='exportOther()' class='authorButton'>Export Other Specify Data</button>
        <button onclick='exportOtherLegacy()' class='authorButton'>Export Legacy Other Specify Data</button>
        <button onclick='deleteInterviews()' class='authorButton btn-danger pull-right'>Delete Interviews</button>
    </div>
</div>


<table class="table table-striped table-bordered table-list">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="$('input[type=checkbox]').prop('checked', $(this).prop('checked'))" data-toggle="tooltip" data-placement="top" title="Select All"></th>
            <th>Ego ID</th>
            <th class="hidden-xs">Started</th>
            <th class="hidden-xs">Completed</th>
            <th class="hidden-xs"># of Alters</th>
            <th class="hidden-xs">Dyad Match ID</th>
            <th class="hidden-xs">Match User</th>
            <th><em class="fa fa-cog"></em></th>

        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($interviews as $interview) {
            if ($interview->completed == -1)
                $completed = "<span style='color:#0B0'>" . date("Y-m-d H:i:s", $interview->complete_date) . "</span>";
            else
                $completed = "";
            $mark = "";
            $matchId = "";
            $matchUser = "";
            /*
            $criteria = array(
                'condition'=>"FIND_IN_SET(" . $interview->id . ", interviewId)",
            );
            $alters = Alters::model()->findAll($criteria); 
            */
            if ($interview->hasMatches) {
                $mark = "class='success'";
                $criteria = array(
                    'condition' => "interviewId1 = $interview->id OR interviewId2 = $interview->id",
                );
                $match = MatchedAlters::model()->find($criteria);
                if ($interview->id == $match->interviewId1)
                    $matchInt = Interview::model()->findByPk($match->interviewId2);
                else
                    $matchInt = Interview::model()->findByPk($match->interviewId1);
                $matchId = $match->getMatchId();
                $matchUser = User::getName($match->userId);
            }
         //   if(count($alters) != 0)
         //       continue;
            echo "<tr $mark>";
            echo "<td>" . CHtml::checkbox('export[' . $interview['id'] . ']') . "</td><td>" . Interview::getEgoId($interview->id) . "</td>";
            echo "<td class='hidden-xs'>" . date("Y-m-d H:i:s", $interview->start_date) . "</td>";
            echo "<td class='hidden-xs'>" . $completed . "</td>";
           // echo "<td class='hidden-xs'>" . count($alters) . "</td>";
            echo "<td class='hidden-xs'>" . $matchId . "</td>";
            echo "<td class='hidden-xs'>" . $matchUser . "</td>";
            echo "<td>";
            if ($interview->completed == -1) {
                echo "<a class='btn btn-success btn-xs' href='" . $this->createUrl('/data/edit/' . $interview->id) ."'>Edit</a>";
                echo "&nbsp;&nbsp;";
            }
            echo "<a class='btn btn-info btn-xs' href='" . $this->createUrl('/interview/' . $study->id . '/' . $interview->id . '/#/page/0') ."'>Review</a>";
            echo "&nbsp;&nbsp;";
            echo "<a class='btn btn-info btn-xs' href='" . $this->createUrl('/data/visualize?expressionId=&interviewId=' . $interview->id) ."'>Visualize</a>";
            echo "</tr>";
        }
        ?>

    </tbody>
</table>
<?php 
$this->widget('CLinkPager', array(
    'pages' => $pages,
)) ?>
<?php
echo CHtml::form('', 'post', array('id' => 'analysis'));
echo CHtml::hiddenField('studyId', $study->id);
echo CHtml::hiddenField('interviewIds');
echo CHtml::hiddenField('expressionId', "");
echo CHtml::hiddenField('withAlters', "", array('id' => 'withAlters'));
?>
</form>