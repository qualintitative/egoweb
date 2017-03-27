<script src="/js/levenshtein.js" type="text/javascript"></script>
<script src="/js/doublemetaphone.js" type="text/javascript"></script>
<script>
alters1 = <?php echo json_encode($alters1); ?>;
alters2 = <?php echo json_encode($alters2); ?>;
answers = <?php echo json_encode($answers); ?>;
prompts = <?php echo json_encode($prompts); ?>;
studyId = <?php echo $study->id; ?>;
interviewIds = [<?php echo $interview1->id; ?>,<?php echo $interview2->id; ?>];
altersD = new Object;
altersL = new Object;
altersLId = new Object;
altersDId = new Object;

dm = new DoubleMetaphone;
dm.maxCodeLen = 64;
for(j in alters1){
    altersL[j] = 999;
    altersD[j] = 999;
    for(k in alters2){
        name1 = alters1[j].toLowerCase().split(" ");
        name2 = alters2[k].toLowerCase().split(" ");
        last1 = false;
        last2 = false;
        if(name1.length > 1)
            last1 = name1[name1.length-1].charAt(0).toLowerCase();
        if(name2.length > 1)
            last2 = name2[name2.length-1].charAt(0).toLowerCase();

        ls = new Levenshtein(name1[0], name2[0]);
        if(ls.distance < altersL[j]){
            if(!last1 || !last2 || last1 == last2){
                altersL[j] = ls.distance;
                altersLId[j] = k;
            }
        }
        d1 = dm.doubleMetaphone(name1[0]).primary;
        d2 = dm.doubleMetaphone(name2[0]).primary;
        ls = new Levenshtein(d1, d2);
        if(ls.distance < altersD[j]){
            if(!last1 || !last2 || last1 == last2){
                altersD[j] = ls.distance;
                altersDId[j] = k;
            }
        }
    }

}
function autoMatch(){
    $(".aMatch").each(function(){
        var id = $(this).attr("id");
        var lTol = altersL[id];
        var dTol = altersD[id];
        var lId = altersLId[id];
        var dId = altersDId[id];
        if($(".unMatch-" + id).length == 0){
            if(lTol <= $("#lTol").val() && dTol <= $("#dTol").val()){
                $(this).val(dId);
                //$("#"  + id + "-name").val(alters2[dId]);
                //$(this).parent().next().attr("alterId",$(this).val());
            }else{
                $(this).val("");
                //$("#"  + id + "-name").val("");
                //$(this).parent().next().attr("alterId",$(this).val());
                $(this).parent().next().html("");
            }
            $(this).change();
        }
    });
    loadR($("#question").val());
}
function loadR(questionId){
    if(!questionId)
        return false;
    $(".responses").each(function(){
        if(typeof answers[questionId][$(this).attr("alterId")] != "undefined")
            $(this).html(answers[questionId][$(this).attr("alterId")]);
    });
}

function matchUp(s){
    var id = $(s).attr("id");
    var id2 = $(s).val();
    if($(s).val() != ""){
        $("#" + id + "-name").show();
        $("#" + id + "-name").val($("option:selected", s).text());
        $("#" + id + "-buttons").html("<button class='btn btn-xs btn-success btn-xs' onclick='save(" + studyId + "," +id + "," + id2 +")'>save</button>");;
    }else{
        $("#" + id + "-alter2").html("");
        $("#" + id + "-name").hide();
        $("#" + id + "-buttons").html("");
    }
    $(s).parent().next().attr("alterId",$(s).val());
    loadR($("#question").val());

}
function save(sId, id1, id2){
    var alterName = $("#" + id1 + "-name").val();
    $.post("/data/savematch", {studyId:sId, alterId1:id1, alterId2:id2, matchedName: alterName, userId: <?php echo Yii::app()->user->id; ?>, <?php echo Yii::app()->request->csrfTokenName . ':"' . Yii::app()->request->csrfToken . '"' ?>, interviewId1:<?php echo $interview1->id; ?>, interviewId2:<?php echo $interview2->id; ?>}, function(data){
        if(id1 == "0")
            document.location.href = "/data/study/" + sId; //$("#markMatch").html(data);
        else
            $("#" + id1 + "-buttons").html(data);
    })
}

function unMatch(id1, id2){
    $.post("/data/unmatch", {alterId1:id1, alterId2:id2, <?php echo Yii::app()->request->csrfTokenName . ':"' . Yii::app()->request->csrfToken . '"' ?>}, function(data){
        if(id1 == 0){
            $("#markMatch").html("<button onclick='save(studyId, 0, 0)' class='btn btn-success'>Mark as matched</button>");
        }else{
            $("#" + id1 + "-buttons").html("");
            $("#" + id1 + "-name").val("");
            $("#" + id1).val("");
            $("#" + id1).change();
        }
    })
}

function exportMatches(){
    document.location = "/data/exportmatches?studyId=" + studyId + "&interviewIds=" + interviewIds.join(",");
}
</script>
<?php
    		$criteria = array(
    			'condition'=>"interviewId1 = $interview1->id OR interviewId2 = $interview1->id",
    		);
    		$marked = MatchedAlters::model()->find($criteria);
?>
<div class="panel panel-success">
    <div class="panel-heading">
        Automatic Matching
    </div>

    <div class="panel-body">
        <div class="form-group">
            <label class="control-label col-lg-1">Metaphone Tolerence</label>
            <div class="col-lg-3">
            <input class="form-control" id="dTol" type="number" value="1">
            </div>
            <label class="control-label col-lg-1">Levenshtein Tolerence</label>
            <div class="col-lg-3">
                <input class="form-control" id="lTol" type="number" value="2">
            </div>
            <div class="col-lg-4">
                <button class="btn btn-primary" onclick="autoMatch();">Match</button>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-warning">
<div class="panel-heading">
    <?php
    echo CHtml::dropdownlist(
        'question',
        '',
        $questions,
        array('empty' => 'Choose Question', "class"=>"pull-left","onChange"=>'loadR($(this).val());$("#prompt").html(prompts[$(this).val()])')
    );
    ?>
    <div id="prompt">Display Alter Question Response</div>
    </div>
</div>

<table class="table table-condensed">
    <tr>
        <th><?php echo Interview::getEgoId($interview1->id); ?></th>
        <th>Responses</th>

        <th><?php echo Interview::getEgoId($interview2->id); ?></th>
        <th>Responses</th>

        <th>Matched Alter name</th>
    </tr><?php foreach($alters1 as $alterId=>$alter): ?>

    <tr>
        <td><?php echo $alter; ?></td>
        <td class="responses" alterId=<?php echo $alterId; ?>></td>
        <td><?php
            foreach($alters2 as $aid=>$name)
                $alterIds2[] = $aid;

            $match = MatchedAlters::model()->findByAttributes(array("alterId1"=>$alterId),

            array("condition"=>"alterId2 IN (" . implode(",", $alterIds2). ")"));
            if($match){
                $selected = $match->alterId2;
                $selectedName = $match->matchedName;
            }else{
                $selected = "";
                $selectedName = "";
            }
                    if(count($alters2) > 0){
                        echo CHtml::dropdownlist(
                            'alterId2',
                            $selected,
                            $alters2,
                            array('empty' => 'No Match', "class"=>"aMatch", "id"=>$alterId, "onChange"=>'matchUp(this)')
                        );
                    }
                ?></td>
        <td id="<?php echo $alterId; ?>-alter2" class="responses" alterId=<?php echo $selected; ?>></td>
        <td><?php echo CHtml::textField("name",$selectedName ,array("id"=>$alterId."-name", "style"=>"display:none;")); ?></td>
        <td id="<?php echo $alterId; ?>-buttons">
            <?php
                if(isset($match))
                    echo "<button class='btn btn-xs btn-danger unMatch-$alterId' onclick='unMatch($alterId, $selected)'>Unmatch</button>";
            ?>

        </td>
    </tr><?php endforeach; ?>
</table>
<div id="markMatch">
<?php if($marked): ?>
<button onclick="unMatch('0', '0')" class="btn btn-danger btn-xs">Remove Mark</button>
<?php else: ?>
<button onclick="save(studyId, '0', '0')" class="btn btn-success btn-xs">Mark as matched</button>
<?php endif; ?>
</div>
