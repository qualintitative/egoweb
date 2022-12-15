function elementInViewport(el) {
  var top = el.offsetTop;
  var left = el.offsetLeft;
  var width = el.offsetWidth;
  var height = el.offsetHeight;

  while(el.offsetParent) {
    el = el.offsetParent;
    top += el.offsetTop;
    left += el.offsetLeft;
  }

  return (
    top < (window.pageYOffset + window.innerHeight) &&
    left < (window.pageXOffset + window.innerWidth) &&
    (top + height) > window.pageYOffset &&
    (left + width) > window.pageXOffset
  );
}
$(function(){
    setTimeout(function(){
        if(typeof $(".answerInput")[0] != "undefined")
            $(".answerInput")[0].focus();
    }, 100);
})
$(document).keydown(function(e) {
    if(typeof  $("#qTable")[0] != "undefined")
        columns = $("#qTable")[0].rows[0].cells.length - 1;
	if($("textarea").length == 1 &&  e.keyCode == 13){
        e.preventDefault();
		if($("#alterFormBox").length != 0 && $(".alterSubmit").length != 0)
			$('.alterSubmit')[0].click();
		else
			$('#next').click();
    }
    if($("textarea").length  == 1){
        if(alterPromptPage == false){
            if (e.keyCode == 37){
                e.preventDefault();
                $(".answerInput:focus").parent().prev().find(".answerInput").focus();
            }
            if (e.keyCode == 39){
                e.preventDefault();
                $(".answerInput:focus").parent().next().find(".answerInput").focus();
            }
            if (e.keyCode == 38){
                e.preventDefault();
                $(".answerInput").each(function(index){
                    if($(this).is(":focus")){
                        if(typeof $(".answerInput")[index-columns] != "undefined"){
                        if($($(".answerInput")[index-columns]).offset().top < $("#floatHeader").offset().top + $("#floatHeader").height()){
                            window.scrollBy(0, -112);
                            }
                            $(".answerInput")[index-columns].focus();
                        }
                        //else
                        //    $(".answerInput:focus").parent().prev().find(".answerInput").focus();
                        return false;
                    }
                });
            }
            if (e.keyCode == 40){
                e.preventDefault();
                $(".answerInput").each(function(index){
                    if($(this).is(":focus")){
                        if(typeof $(".answerInput")[index+columns] != "undefined"){
                            if(!elementInViewport($(".answerInput")[index+columns])){
                            window.scrollBy(0, 112);
                            }
                            $(".answerInput")[index+columns].focus();
                        }
                        //else
                        //    $(".answerInput:focus").parent().next().find(".answerInput").focus();
                        return false;
                    }
                });
            }
        }
    }
});

function redraw(params){
	url = rootUrl + "/data/deletegraph?id=" + $("#Graph_id").val();
	$.get(url, function(data){
		document.location.reload();
	});
}

function save(questions, page, url, scope, goingBack){
    if(typeof s != "undefined" && typeof s.isForceAtlas2Running != "undefined" && s.isForceAtlas2Running()){
        s.stopForceAtlas2();
        saveNodes();
    }
    var saveUrl = rootUrl + "/interview/save";
    if(interview.COMPLETED == -1){
        var nextUrl = rootUrl + "/interview/" + study.ID + "/" + interviewId + "#/page/" + (parseInt(page) + 1);
        if(typeof hashKey != "undefined")
            nextUrl = nextUrl + "/" + hashKey;
        document.location = nextUrl;
        return;
    }
    if(typeof questions[0] == "undefined"){
        if(scope.answerForm.$pristine == false || scope.conclusion == true){
            $.post(saveUrl, $('#answerForm').serialize(), function(data){
                if(!data.match("error")){
                    data = JSON.parse(data);
                    answers = data.answers;
                    interview = data.interview;
                    interviewId = interview.ID;
                    console.log(answers);
                    console.log(interview);
                    evalQuestions();
                    var reloading = false;
                    page = parseInt(interview.COMPLETED);
                    for(k in questions){
                        if(questions[k].SUBJECTTYPE == "MERGE_ALTER"){
                            if(scope.answers[questions[k].array_id].VALUE == "MATCH"){
                                alters[questions[k].ALTERID2] = prevAlters[questions[k].ALTERID2];
                                delete prevAlters[questions[k].ALTERID2];
                                delete alters[questions[k].ALTERID1];
                                masterList = [];
                                reloading = true;
                                document.location.reload();
                            }else{
                                reloading = true;
                                alters[questions[k].ALTERID1].ALTERLISTID =  prevAlters[questions[k].ALTERID2].INTERVIEWID;
                                document.location.reload();
                            }
                        }
                    }
                    if(reloading == false){
                        var nextUrl = rootUrl + "/interview/" + study.ID + "/" + interviewId + "#/page/" + (parseInt(page) + 1);
                        if(typeof hashKey != "undefined")
                            nextUrl = nextUrl + "/" + hashKey;
                        if(goingBack == null)
                            document.location = nextUrl;
                    }
                }else{
                    errorMsg = JSON.parse(data);
                    scope.errors[0] = errorMsg.error;
                    scope.$apply();
                }
            });
        }else{
            var nextUrl =  rootUrl + "/interview/" + study.ID + "/" + interviewId + "#/page/" + (parseInt(page) + 1);
            if(typeof hashKey != "undefined")
                nextUrl = nextUrl + "/" + hashKey;
            if(goingBack == null)
                document.location = nextUrl;
        }
    }else if(questions[0].ANSWERTYPE == "CONCLUSION"){
        $.post(saveUrl, $('#answerForm').serialize(), function (data) {
            if (typeof redirect !== 'undefined' && redirect){
                if(redirect.indexOf("ipsos") != -1)
                    redirect = redirect + "&ext_st=1&Termpoint8=&intlen=" + Math.round((Math.round(Date.now() /1000) - interview.START_DATE) / 60);
                document.location = redirect;
            } else {
                document.location =  rootUrl + "/admin";
            }
        });
    }else{
        if(questions[0].ANSWERTYPE == "NAME_GENERATOR"){
            $.post(saveUrl, $('#answerForm').serialize(), function (data) {
                //console.log(data);
            });
            buildList();
        }
        var nextUrl = url + "/page/" + (parseInt(page) + 1);
        if(typeof hashKey != "undefined")
            nextUrl = nextUrl + "/" + hashKey;
        if(goingBack == null)
            document.location = nextUrl;
    }
}

function saveSkip(interviewId, questionId, alterId1, alterId2, arrayId)
{
    if(typeof answers[arrayId] != "undefined" && answers[arrayId].VALUE == study.VALUELOGICALSKIP)
        return;
    var skipAnswer = {
        "Answer":{
            0:{
                "value":study.VALUELOGICALSKIP,
                "otherSpecifyText":"",
                "skipReason":"NONE",
                "questionId":questionId,
                "questionType":questions[questionId].SUBJECTTYPE,
                "studyId":study.ID,
                "answerType":questions[questionId].ANSWERTYPE,
                "alterId1":alterId1,
                "alterId2":alterId2,
                "interviewId":interviewId,
                "id":(typeof answers[arrayId] != "undefined" ? answers[arrayId].ID : "")
            }
        },
        "YII_CSRF_TOKEN":csrf,
        "studyId":study.ID
    }

    var saveUrl = document.location.protocol + "//" + document.location.host + "/interview/save";
    $.post(saveUrl, skipAnswer, function(data){
        data = JSON.parse(data);
        answers = data.answers;
        interview = data.interview;
        interviewId = interview.ID;
        console.log("saving skip value");
    });
}

function saveNodes(sg)
{
	var nodes = {};
    //for(var sg in s){
        var graphNodes = s[sg].graph.nodes();
        for(var k in graphNodes){
            nodes[graphNodes[k].id] = graphNodes[k];
        }
    //}
    $("#Graph_nodes").val(JSON.stringify(nodes));
    console.log('Graph[expressionId]'+sg+expressionIds[sg])
    if($('#graph-form').length > 0)
	    var data = $('#graph-form').serialize();
    else
        var data = {'Graph[nodes]':JSON.stringify(nodes), 'Graph[interviewId]':interviewId, 'Graph[expressionId]':expressionIds[sg], 'Graph[params]':networkParams[sg]};
	$.post( "/data/savegraph", data, function( data ) {
    	//graphs[expressionId].NODES = JSON.stringify(nodes);
        graphs = JSON.parse(data);
		console.log("nodes saved");
	});
}

function getNote(node){
    var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + graphExpressionId + "&alterId=" + node.id;
    $.get(url, function(data){
        $("#left-container").html(data);

    });
}

function saveNote(){
    var noteContent = $("#Note_notes").val();
    $.post("/data/savenote", $("#note-form").serialize(), function(nodeId){
        var node = s.graph.nodes(nodeId);
        if(node && !node.id.match(/graphNote/) && !node.label.match("�"))
            node.label = node.label + " �";
        s.refresh();
        var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + graphExpressionId + "&alterId=" + nodeId;
        $.get(url, function(data){
            notes[nodeId] = noteContent;
            $("#left-container").html(data);
        });
    });
}

function deleteNote(){
    $.post("/data/deletenote", $("#note-form").serialize(), function(data){
        if(!isNaN(data)){
            var node = s.graph.nodes(data);
            node.label = node.label.replace(" �","");
            delete notes[data];
            s.refresh();
            $("#left-container").html("");
        }else{
            delete notes[data];
            s.graph.dropNode(data);
            saveNodes();
            $("#left-container").html("");
            graphNotes = 0;
            for(k in notes){
                if(k.match(/graphNote/)){
                    noteId = parseInt(k.match(/graphNote-(\d+)/)[1]);
                    if(noteId > graphNotes)
                        graphNotes = noteId;
                }
            }
            s.refresh();
        }
    });
}

app.factory("saveAlter", function($http, $q) {
   var getAlters = function() {
       var saveUrl = document.location.protocol + "//" + document.location.host + "/interview/alter";
       return $.post(saveUrl, $("#alterForm").serialize(), function(data) {
           return data;
       });
    }
   return {
       getAlters : getAlters
   }
});

app.factory("deleteAlter", function($http, $q) {
   var getAlters = function() {
       var saveUrl = document.location.protocol + "//" + document.location.host + "/interview/deletealter";
       return $.post(saveUrl, $("#deleteAlterForm").serialize(), function(data) {
           return data;
       });
    }
   return {
       getAlters : getAlters
   }
});

isMobile = false;
