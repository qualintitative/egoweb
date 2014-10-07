justUploaded = [];

function createSurveyJSON(studyId){
	data = new Object;
	serverStudyId = db.queryValue("SELECT serverstudyid FROM serverstudy WHERE id = " + studyId);
	data['study'] = db.queryRowObject("SELECT * FROM study WHERE id = " + studyId);
	if(data['study']){
		data['study'].ID = serverStudyId;
		var questions = db.queryObjects("SELECT * FROM question WHERE studyId = " + studyId).data;
		for(a in questions){
			questions[a].STUDYID = serverStudyId;
		}
		data['questions'] = questions;
		var options = db.queryObjects("SELECT * FROM questionOption WHERE studyId = " + studyId).data;
		for(b in options){
			options[b].STUDYID = serverStudyId;
		}
		data['questionOptions'] = options;
		var expressions = db.queryObjects("SELECT * FROM expression WHERE studyId = " + studyId).data;
		for(c in expressions){
			expressions[c].STUDYID = serverStudyId;
		}
		data['expressions'] = expressions;
		console.log(data['expressions']);
		data['alters'] = [];
		data['answers'] = [];
		var interviews = db.queryObjects("SELECT * FROM interview WHERE completed = -1 AND studyId = " + studyId).data;
		console.log(interviews);
		for(k in interviews){
			var alters = db.queryObjects("SELECT * FROM alters WHERE interviewId = " + interviews[k].ID).data;
			for (g in alters){
				data['alters'].push(alters[g]);
			}
			var answers = db.queryObjects("SELECT * FROM answer WHERE interviewId = " + interviews[k].ID).data;
			for(f in answers){
				answers[f].STUDYID = serverStudyId;
				data['answers'].push(answers[f]);
			}
			interviews[k].STUDYID = serverStudyId;
		}
		console.log(data['alters']);
		data['interviews'] = interviews;
		console.log(data['interviews']);

	}
	return JSON.stringify(data);
}


function upload(studyId){
	data = new Object;
	$('#status').html('Uploading...');
	$("#uploader-" + studyId).prop('disabled', true);

	var serverAddress = db.queryValue("SELECT address FROM serverstudy WHERE id = " + studyId);
	url = "http://" + serverAddress + "/mobile/uploadData";
	$('#data').val(createSurveyJSON(studyId));
	console.log($('#data').val());
	$.ajax({
		type:'POST',
		url:url,
        crossDomain: true,
		data:$('#hiddenForm').serialize(),
		success:function(data){
			$('#status').html(data);
			justUploaded.push(studyId);
			$('#uploader-' + studyId).hide();
			deleteInterviews(studyId);
			$("#uploader-" + studyId).prop('disabled', false);
		},
		error:function(xhr, ajaxOptions, thrownError){
			$('#status').html('Error: ' + xhr.status);
			$("#uploader-" + studyId).prop('disabled', false);
		}
	});
}

function deleteStudy(id){
	var interviews = db.queryObjects("SELECT id FROM interview WHERE completed = -1 AND studyId = " + id).data.length;
	if(justUploaded.indexOf(id) != -1 || interviews == 0){
		console.log("Deleting study " + id);
		var serverStudy = db.queryRow("SELECT * FROM serverStudy where id = " + id);
		var server = db.queryRowObject("SELECT * FROM server WHERE address = '" + serverStudy[1] + "'");
		var rowdel = db.queryRow("SELECT * FROM study WHERE id = " + id);
		db.catalog.getTable("serverstudy").deleteRow(serverStudy);
		db.catalog.getTable("study").deleteRow(rowdel);
		var questions = db.query("SELECT * FROM question WHERE studyId = " + id).data;
		for(q in questions){
			db.catalog.getTable("question").deleteRow(questions[q]);
		}
		var options = db.query("SELECT * FROM questionOption WHERE studyId = " + id).data;
		for(r in options){
			db.catalog.getTable("questionOption").deleteRow(options[r]);
		}
		var expressions = db.query("SELECT * FROM expression WHERE studyId = " + id).data;
		for(t in expressions){
			db.catalog.getTable("expression").deleteRow(expressions[t]);
		}
		db.commit();
		deleteInterviews(id, true);
		getStudyList(server);
	}else{
		alert("you must upload data before you can delete");
	}
}

function deleteInterviews(id, all){
	if(typeof all != "undefined")
		all = "";
	else
		all = "completed = -1 AND";
	var interviews = db.query("SELECT * FROM interview WHERE " + all + " studyId = " + id).data;
	console.log(interviews);
	for(u in interviews){
	    var alters = db.query("SELECT * FROM alters WHERE interviewId = " + interviews[u][0]).data;
	    for(v in alters){
	    	db.catalog.getTable("alters").deleteRow(alters[v]);
	    }
		var answers = db.query("SELECT * FROM answer WHERE interviewId = " + interviews[u][0]).data;
		for(w in answers){
	    	db.catalog.getTable("answer").deleteRow(answers[w]);
		}
	    db.catalog.getTable("interview").deleteRow(interviews[u]);
	}
	db.commit();
}