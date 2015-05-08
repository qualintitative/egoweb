<script src="/js/levenshtein.js" type="text/javascript"></script>
<script src="/js/doublemetaphone.js" type="text/javascript"></script>
<script>
alters1 = <?php echo json_encode($alters1); ?>;
alters2 = <?php echo json_encode($alters2); ?>;
answers = <?php echo json_encode($answers); ?>;

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
        ls = new Levenshtein(alters1[j], alters2[k]);
        if(ls.distance < altersL[j]){
            altersL[j] = ls.distance;
            altersLId[j] = k;
        }
        d1 = dm.doubleMetaphone(alters1[j]).primary;
        d2 = dm.doubleMetaphone(alters2[k]).primary;
        ls = new Levenshtein(d1, d2);
        if(ls.distance < altersD[j]){
            altersD[j] = ls.distance;
            altersDId[j] = k;
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

        if(lTol <= $("#lTol").val() && dTol <= $("#dTol").val()){
            $(this).val(dId);
            $("#"  + id + "-name").val(alters2[dId]);
            $(this).parent().next().attr("alterId",$(this).val());
        }else{
            $(this).val("");
            $("#"  + id + "-name").val("");
            $(this).parent().next().attr("alterId",$(this).val());
            $(this).parent().next().html("");
        }
    });
    loadR($("#question").val());
}
function loadR(questionId){
    if(!questionId)
        return false;
    $(".responses").each(function(){
        $(this).html(answers[questionId][$(this).attr("alterId")]);
    });
}
function save(){
    
}
</script>
<div class="panel panel-success">
    <div class="panel-heading">
        Automatic Matching
    </div>

    <div class="panel-body">
        <div class="form-group">
            <label class="control-label col-lg-1">Metaphone Tolerence</label>
            <div class="col-lg-3">
            <input class="form-control" id="dTol" type="number" value="2">
            </div>
            <label class="control-label col-lg-1">Levenshtein Tolerence</label>
            <div class="col-lg-3">
                <input class="form-control" id="lTol" type="number" value="5">
            </div>
            <div class="col-lg-4">
                <button class="btn btn-primary" onclick="autoMatch();">Match</button>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-warning">
<div class="panel-heading">

                Display Alter Question Response

<?php
                    echo CHtml::dropdownlist(
                        'question',
                        '',
                        $questions,
                        array('empty' => 'Choose Question', "onChange"=>'loadR($(this).val());')
                    );
?>
    </div>
</div>


<table class="table table-condensed">
    <tr>
        <th>Interview 1</th>
        <th>Responses</th>

        <th>Interview 2</th>
        <th>Responses</th>

        <th>Matched Alter name</th>
    </tr><?php foreach($alters1 as $alterId=>$alter): ?>

    <tr>
        <td><?php echo $alter; ?></td>
        <td class="responses" alterId=<?php echo $alterId; ?>></td>
        <td><?php
                    if(count($alters2) > 0){
                        echo CHtml::dropdownlist(
                            'alterId2',
                            '',
                            $alters2,
                            array('empty' => 'No Match', "class"=>"aMatch", "id"=>$alterId, "onChange"=>'$(this).parent().next().attr("alterId",$(this).val()); $("#" + $(this).attr("id") + "-name").val($("option:selected", this).text()); loadR($("#question").val());')
                        );
                    }
                ?></td>
        <td class="responses"></td>
        <td><?php echo CHtml::textField("name", "" ,array("id"=>$alterId."-name")); ?></td>
    </tr><?php endforeach; ?>
</table>
