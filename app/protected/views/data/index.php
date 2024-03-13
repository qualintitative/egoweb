<?php

use yii\helpers\Html;
use app\models\MatchedAlters;
use app\models\Alters;
use app\models\Interview;
use app\models\User;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\bootstrap4\LinkPager;
?>
<script>
    interviewStudyIds = <?php echo json_encode($interviewStudyIds); ?>;
    multiStudyIds = <?php echo json_encode($multiStudyIds); ?>;

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
        multiSesh = 0;
        var interviews = $("input[type='checkbox'][name*='export']:checked");
        if ($("#withAlters1").prop("checked") == true)
            withAlters = 1;
        if ($("#multiSession1").prop("checked") == true)
            multiSesh = 1;
        $("#withAlters").val(withAlters);
        $("#multiSession").val(multiSesh);
        $(".progress-bar").width(0);
        var batchPromiseRecursive = function() {
            if (interviews.length == 0) {
                return;
            }
            var thisInt = interviews.splice(0, batchSize);
            var interviewId = $(thisInt).attr("id").match(/\d+/g)[0];
            var d = new Date();
            start = d.getTime();
            var edata = {
                studyId: interviewStudyIds[interviewId],
                interviewId: interviewId,
                withAlters: withAlters,
                multiSession: multiSesh,
                studyOrder: $('#studyOrder').val(),
                expressionId: $("#" + interviewStudyIds[interviewId] + "_expId").val(),
                YII_CSRF_TOKEN: $("input[name='YII_CSRF_TOKEN']").val()
            }
            for (let i = 0; i < $(".expId").length; i++) {
                edata[$(".expId")[i].id] = $(".expId")[i].value;
            }

            return $.ajax({
                type: "POST",
                url: rootUrl + "/data/exportegoalter",
                data: edata,
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
        multiSesh = 0;
        if ($("#withAlters1").prop("checked") == true)
            withAlters = 1;
        if ($("#multiSession1").prop("checked") == true)
            multiSesh = 1;
        $("#withAlters").val(withAlters);
        $("#multiSession").val(multiSesh);
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
                    multiSession: multiSesh,
                    studyOrder: $('#studyOrder').val(),
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

    function exportCompletion() {
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
                url: rootUrl + "/data/exportcompletion",
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
            $('#analysis').attr('action', rootUrl + '/data/exportcompletionall');
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

    function exportCodebook() {
        document.location = rootUrl + "/data/codebook/<?php echo $study->id; ?>";
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
<div id="data-app" class="card">
    <div class="p-3 row">
        <div class="col-sm-8 p-2">
            <div class='mr-1'>
                <input type="checkbox" id="multiSession1" v-model='multiSesh'> Include Multisession data
            </div>
            <input type="checkbox" id="withAlters1" checked> Include Alter Names
        </div>
        <div class="col-sm-4">
            <a class="btn btn-sm btn-info float-right" href="/authoring/<?php echo $study->id; ?>">Authoring</a>
        </div>
    </div>
    <div class="card-body" v-sortable.div="{ onUpdate: reorderStudy, chosenClass: 'is-selected'}">
        <div v-for="(studyId,index) in multiStudyIds" :key="studyId" class="col-sm-12 float-left row mb-2">
            <label v-if="multiSesh" class="col-sm-2">
                {{studyOrder.indexOf(studyId) + 1}}_<a :href="'/data/' + studyId">{{all_studies[studyId ]}}</a>
            </label>
            <label v-if="!multiSesh && studyId == <?php echo $study->id; ?>" class="col-sm-2">
                Network Expression
            </label>
            <div v-if="multiSesh || (!multiSesh && studyId == <?php echo $study->id; ?>)" class="col-sm-10">
                <b-form-select v-on:change="getSelectedItem($event, studyId)" v-model="expression[studyId]" :options="expressions[studyId]" :name="studyId + '_expId'" :id="studyId + '_expId'">
                    <template #first>
                        <b-form-select-option value="">(select network expression)</b-form-select-option>
                    </template>
                </b-form-select>
            </div>
        </div>
        <div v-if="multiSesh" class="col-sm-12 mb-1 row">
            <label class="col-sm-2">Filename</label>
            <div class="col-sm-6">
                <input id="filename" class="form-control" onchange="$('#realFilename').val($(this).val())">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="status"></div>
        <div class="progress row mb-3">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <button onclick='exportEgoLevel()' class='authorButton'>Export Ego Level Data</button>
        <button onclick='exportEgo()' class='authorButton'>Export Ego Alter Data</button>
        <button onclick='exportAlterPair()' class='authorButton'>Export Alter Pair Data</button>
        <button onclick='exportOther()' class='authorButton'>Export Other Specify Data</button>
        <button onclick='exportCompletion()' class='authorButton'>Export Completion Time Data</button>
        <button onclick='exportCodebook()' class='authorButton'>Export Codebook</button>
        <button onclick='deleteInterviews()' class='authorButton btn-danger pull-right'>Delete Interviews</button>

    </div>
</div>


<table id="dTable" class="table table-striped table-bordered table-list">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="$('input[type=checkbox]').prop('checked', $(this).prop('checked'))" data-toggle="tooltip" data-placement="top" title="Select All"></th>
            <?php if ($study->multiSessionEgoId) : ?>
                <th class="d-none d-sm-table-cell">Study</th>
            <?php endif; ?>
            <th>Ego ID</th>
            <th class="d-none d-sm-table-cell">Started</th>
            <th class="d-none d-sm-table-cell">Completed</th>
            <th class="d-none d-sm-table-cell">Interview Length</th>
            <th class="d-none d-sm-table-cell"># of Alters</th>
            <th><em class="fa fa-cog"></em></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $items = Interview::find()->where(["studyId" => $study->id])->all();
        foreach ($interviews as $interview) {

            if ($interview->completed == -1) {
                $completed = "<span style='color:#0B0'>" .  \Yii::$app->formatter->asDate($interview->complete_date, "php:Y-m-d H:i:s") . "</span>";
                $intlen = "<span style='color:#0B0'>" . round(($interview->complete_date - $interview->start_date) / 60) . "</span>";
            } else {
                $completed = "";
                $intlen = "";
            }
            echo "<tr>";
            echo "<td>" . Html::checkbox('export[' . $interview->id . ']', false, ['id' => 'export_' . $interview->id]) . "</td>";
            if ($study->multiSessionEgoId) {
                echo "<td>" . $all_studies[$interview->studyId] . "</td>";
            }
            echo "<td>" . $egoIds[$interview->id] . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . \Yii::$app->formatter->asDate($interview->start_date, "php:Y-m-d H:i:s") . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . $completed . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . $intlen . "</td>";
            echo "<td class='d-none d-sm-table-cell'>" . $alters[$interview->id] . "</td>";
            echo "<td>";
            if ($interview->completed == -1) {
                echo "<a class='btn btn-success btn-xs' href='" . Url::to(['/data/edit/' . $interview->id]) . "'>Edit</a>";
                echo "&nbsp;&nbsp;";
            }
            echo "<a class='btn btn-info btn-xs' href='" . Url::to(['/interview/' . $interviewStudyIds[$interview->id] . '/' . $interview->id . '#/page/0']) . "'>Review</a>";
            echo "&nbsp;&nbsp;";
            echo "<a class='btn btn-info btn-xs' href='" . Url::to(['/data/visualize/' . $interview->id]) . "'>Visualize</a>";
            echo "</tr>";
        }
        ?>

    </tbody>
</table>
<?= Html::beginForm([''], 'post', ['id' => 'analysis']) ?>
<?php
echo Html::hiddenInput('studyId', $study->id, ['id' => 'studyId']);
echo Html::hiddenInput('interviewIds', '', ['id' => 'interviewIds']);
foreach ($multiStudyIds as $studyId) {
    echo Html::hiddenInput($studyId . '_expressionId', '', ['id' => $studyId . '_expressionId', 'class' => 'expId']);
}
echo Html::hiddenInput('filename', "", array('id' => 'realFilename'));
echo Html::hiddenInput('withAlters', "1", array('id' => 'withAlters'));
echo Html::hiddenInput('multiSession', "1", array('id' => 'multiSession'));
echo Html::hiddenInput('studyOrder', "", array('id' => 'studyOrder'));

?>
<?= Html::endForm() ?>
<?php
$this->registerAssetBundle(\yii\web\JqueryAsset::class, \yii\web\View::POS_HEAD);

use app\assets\DataAsset;

DataAsset::register($this);
?>
<script>
    csrf = '<?php echo Yii::$app->request->getCsrfToken(); ?>';
    all_studies = <?php echo json_encode($all_studies, ENT_QUOTES); ?>;
    expressions = <?php echo json_encode($expressions, ENT_QUOTES); ?>;

    $(document).ready(function() {
        table = $('#dTable').DataTable({
            lengthMenu: [10, 50, 100, 500, 2500],
            "emptyTable": "No data available in table",
            //  "info":           "", //"Showing _START_ to _END_ of _TOTAL_ entries",
            //  "infoEmpty":      "", //"Showing 0 to 0 of 0 entries",
            //  "paging": false
        });
        filterTable("<?php echo $study->name; ?>")
    });
    filterTable = function(studyName) {
        if ($('#multiSession1').prop('checked'))
            studyName = ''
        table.columns(1).search(studyName, true, false).draw();
    }
    Vue.directive('sortable', {
        twoWay: true,
        deep: true,
        bind: function(el, binding, vnode) {
            var options = {
                ...binding.value,
                draggable: Object.keys(binding.modifiers)[0]
            };
            if (Object.keys(binding.modifiers)[0] == "tr")
                el._sortable = Sortable.create(el.querySelector("tbody"), options);
            else
                el._sortable = Sortable.create(el, options);
            el._sortable.option("onChoose", function(e) {
                el._sortable.oldOrder = el._sortable.toArray();
            });
            el._sortable.option("onUpdate", function(e) {
                if (typeof options.onUpdate != "undefined")
                    options.onUpdate(e);
                if (vnode.children == undefined) {
                    el._sortable.sort(el._sortable.oldOrder)
                }
            });

        },
        update: function(value) {}
    });


    data = new Vue({
        el: '#data-app',
        components: {},
        data() {
            return {
                all_studies: all_studies,
                expressions: expressions,
                multiStudyIds: multiStudyIds,
                multiSesh: false,
                expression: [],
                studyOrder: [],
            }
        },
        created() {
            for (m in multiStudyIds) {
                this.studyOrder[m] = multiStudyIds[m]
                this.expression[multiStudyIds[m]] = ''
            }
        },
        mounted() {
            var self = this;
        },
        methods: {
            reorderStudy(event) {
                console.log(event.newIndex, event.oldIndex)
                console.log(this.studyOrder)
                const new_place = this.studyOrder[event.newIndex];
                const old_place = this.studyOrder[event.oldIndex];
                console.log(new_place, old_place);
                this.studyOrder[event.newIndex] = old_place
                this.studyOrder[event.oldIndex] = new_place
                $("#studyOrder").val(this.studyOrder.join(","))
                this.$forceUpdate();
                self = this;
            },
            getSelectedItem(data, studyId) {
                $("#" + studyId + "_expressionId").val(data)
            },
        }
    })
</script>