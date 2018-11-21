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
discardNames = ["i", "ii", "iii", "iv", "v", "jr", "sr"]
dm.maxCodeLen = 64;
for(j in alters1){
    altersL[j] = 999;
    altersD[j] = 999;
    for(k in alters2){
        name1 = alters1[j].toLowerCase().replace(/\./g,' ').trim().split(" ");
        name2 = alters2[k].toLowerCase().replace(/\./g,' ').trim().split(" ");

        last1 = false;
        last2 = false;
        first1 = name1[0].charAt(0).toLowerCase();
        first2 = name2[0].charAt(0).toLowerCase();

        if(discardNames.includes(name1[name1.length-1]))
          name1.pop();
        if(discardNames.includes(name2[name2.length-1]))
          name2.pop();

        if(name1.length > 1){
            last1 = name1[name1.length-1].charAt(0).toLowerCase();
        }
        if(name2.length > 1){
            last2 = name2[name2.length-1].charAt(0).toLowerCase();
        }


        d1 = dm.doubleMetaphone(name1[0]).primary;
        d2 = dm.doubleMetaphone(name2[0]).primary;
        ds = new Levenshtein(d1, d2);
        if(ds.distance < altersD[j]){

            if(!last1 || !last2 || last1 == last2){
              console.log("first match", ds.distance, name1[0],d1,name2[0],d2, " list dist ", altersL[j]);

                altersD[j] = ds.distance;
                altersDId[j] = k;
            }
        }
        if(last1 && last2){
          l1 = dm.doubleMetaphone(name1[name1.length-1]).primary;
          l2 = dm.doubleMetaphone(name2[name2.length-1]).primary;
          ls = new Levenshtein(l1, l2);

          if(ls.distance < altersL[j]){
              console.log("last dist", ls.distance, l1,l2);
              if(first1 == first2){
                  altersL[j] = ls.distance;
                  if(altersDId[j] != k){
                    if(altersD[j] <= altersL[j]){
                      altersLId[j] = altersDId[j];
                    } else{
                      altersLId[j] = k;
                      altersDId[j] = k;
                    }
                  }else{
                    altersLId[j] = k;
                  }
              }
            }
        }

        else if(name1.length > 1 && name2.length  == 1 && altersD[j] == 0 && altersL[j] != 0){
          console.log("replaced last with first " + alters2[k])
          altersLId[j] = altersDId[j];
          altersL[j] = altersD[j];
        }


    }
    if(altersD[j] <= 1 && altersL[j] == 999){
      console.log(alters1[j])
      altersL[j] = 0;
      altersLId[j] = altersDId[j];
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
    var matchId = $(s).attr("matchId");
    if($(s).val() != ""){
        $("#" + id + "-name").show();
        $("#" + id + "-name").val($("option:selected", s).text());
        $("#" + id + "-buttons").html("<button class='btn btn-xs btn-success btn-xs' onclick='save(" + studyId + "," +id + "," + id2 +","+ matchId+")'>save</button>");;
    }else{
        $("#" + id + "-alter2").html("");
        $("#" + id + "-name").hide();
        $("#" + id + "-buttons").html("");
    }
    $(s).parent().next().attr("alterId",$(s).val());
    loadR($("#question").val());

}
function save(sId, id1, id2, matchId){
    var alterName = $("#" + id1 + "-name").val();
    if(typeof alterName != "undefined" && alterName.trim() == ""){
        alert ("Please enter a name!");
    }else{
        $.post("/data/savematch", {id:matchId, studyId:sId, alterId1:id1, alterId2:id2, matchedName: alterName, userId: <?php echo Yii::app()->user->id; ?>, <?php echo Yii::app()->request->csrfTokenName . ':"' . Yii::app()->request->csrfToken . '"' ?>, interviewId1:<?php echo $interview1->id; ?>, interviewId2:<?php echo $interview2->id; ?>}, function(data){
            if(id1 == "0")
                document.location.href = "/data/study/" + sId; //$("#markMatch").html(data);
            else
                $("#" + id1 + "-buttons").html(data);
        })
    }
}

function unMatch(sId, id1, id2){
    $.post("/data/unmatch", {studyId:sId, alterId1:id1, alterId2:id2, <?php echo Yii::app()->request->csrfTokenName . ':"' . Yii::app()->request->csrfToken . '"' ?>}, function(data){
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
    			'condition'=>"(interviewId1 = $interview1->id OR interviewId2 = $interview1->id) AND alterId1 = 0 AND alterId2 = 0",
    		);
    		$marked = MatchedAlters::model()->find($criteria);
?>
<div class="panel panel-success">
    <div class="panel-heading">
        Automatic Matching
    </div>

    <div class="panel-body">
        <div class="form-group">
            <label class="control-label col-lg-1">First Name Tolerance</label>
            <div class="col-lg-3">
            <input class="form-control" id="dTol" type="number" value="2">
            </div>
            <label class="control-label col-lg-1">Last Name Tolerance</label>
            <div class="col-lg-3">
                <input class="form-control" id="lTol" type="number" value="1">
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
        CHtml::listData(Question::model()->findAllByAttributes(array('subjectType'=>"ALTER", "studyId"=>$study->id), array( 'order'=>"ordering")), 'id', 'title'),
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
    </tr>
    <?php if($alters1):?>
    <?php foreach($alters1 as $alterId=>$alter): ?>

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
                $matchId = $match->id;
            }else{
                $selected = "";
                $selectedName = "";
                $matchId = "";
            }
                    if(count($alters2) > 0){
                        echo CHtml::dropdownlist(
                            'alterId2',
                            $selected,
                            $alters2,
                            array('empty' => 'No Match', "class"=>"aMatch", "id"=>$alterId, "matchId"=>$matchId, "onChange"=>'matchUp(this)')
                        );
                    }
                ?></td>
        <td id="<?php echo $alterId; ?>-alter2" class="responses" alterId=<?php echo $selected; ?>></td>
        <td><?php echo CHtml::textField("name",$selectedName ,array("id"=>$alterId."-name", "style"=>($selectedName == "" ? "display:none;": ""))); ?></td>
        <td id="<?php echo $alterId; ?>-buttons">
            <?php
                if(isset($match))
                    echo "<button class='btn btn-xs btn-danger unMatch-$alterId' onclick='unMatch(studyId, $match->id, $selected)'>Unmatch</button>";
            ?>

        </td>
    </tr><?php endforeach; ?>
  <?php endif;?>
</table>
<div id="markMatch">
<?php if($marked): ?>
<button onclick="unMatch(studyId, '0', '0')" class="btn btn-danger btn-xs">Remove Mark</button>
<?php else: ?>
<button onclick="save(studyId, '0', '0')" class="btn btn-success btn-xs">Mark as matched</button>
<?php endif; ?>
</div>
