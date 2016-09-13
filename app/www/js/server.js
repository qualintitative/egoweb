$(function(){
    setTimeout(function(){
        if(typeof $(".answerInput")[0] != "undefined")
            $(".answerInput")[0].focus();
    }, 100);
})
$(document).keydown(function(e) {
	if($("textarea").length == 0 &&  e.keyCode == 13){
    		e.preventDefault();
		if($("#alterFormBox").length != 0 && $("#alterFormBox").html() != "")
			$('.alterSubmit')[0].click();
		else
			$('.orangebutton')[0].click();
	}
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
                if(typeof $(".answerInput")[index-columns] != "undefined")
                    $(".answerInput")[index-columns].focus();
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
                if(typeof $(".answerInput")[index+columns] != "undefined")
                    $(".answerInput")[index+columns].focus();
                //else
                //    $(".answerInput:focus").parent().next().find(".answerInput").focus();
                return false;
            }
        });
	}
});

function redraw(params){
	url = "/data/deleteGraph?id=" + $("#Graph_id").val();
	$.get(url, function(data){
		document.location.reload();
	});
}

function save(questions, page, url, scope){
    if(typeof s != "undefined" && typeof s.isForceAtlas2Running != "undefined" && s.isForceAtlas2Running()){
        s.stopForceAtlas2();
        saveNodes();
    }
    var saveUrl = document.location.protocol + "//" + document.location.host + "/interview/save";
    if(typeof questions[0] == "undefined"){
        $.post(saveUrl, $('#answerForm').serialize(), function(data){
            if(data != "error"){
                answers = JSON.parse(data);
                console.log(answers);
                for(k in answers){
                    interviewId = answers[k].INTERVIEWID;
                    studyId = answers[k].STUDYID;
                    break;
                }
                document.location = document.location.protocol + "//" + document.location.host + "/interview/" + studyId + "/" + interviewId + "#/page/" + (parseInt(page) + 1);
            }else{
                scope.errors[0] = "Participant not found";
                scope.$apply();
            }
        });
    }else if(questions[0].ANSWERTYPE == "CONCLUSION"){
        $.post(saveUrl, $('#answerForm').serialize(), function (data) {
            if (typeof redirect !== 'undefined' && redirect){
                document.location = redirect;
            }
            else {
                document.location = document.location.protocol + "//" + document.location.host + "/admin";
            }
        });
    }else{
        document.location = url + "/page/" + (parseInt(page) + 1);
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
        "YII_CSRF_TOKEN":csrf
    }

    var saveUrl = document.location.protocol + "//" + document.location.host + "/interview/save";
    $.post(saveUrl, skipAnswer, function(data){
        answers = JSON.parse(data);
        console.log("saving skip value");
        console.log(answers);
    });
}

function saveNodes()
{
	var nodes = {};
	for(var k in s.graph.nodes()){
		nodes[s.graph.nodes()[k].id] = s.graph.nodes()[k];
	}
	$("#Graph_nodes").val(JSON.stringify(nodes));
	$.post( "/data/savegraph", $('#graph-form').serialize(), function( data ) {
    	//graphs[expressionId].NODES = JSON.stringify(nodes);
        graphs = JSON.parse(data);
		console.log("nodes saved");
	});
}

function getNote(node){
    var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + node.id;
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
        var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + nodeId;
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
