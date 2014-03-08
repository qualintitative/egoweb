study = [];
ego_id_questions = new Object;
ego_questions =  new Object;
alter_questions = new Object;
alter_pair_questions = new Object;
network_questions = new Object;
expressions = new Object;

function getInterviewIds(intId){
	var egoValue = db.queryValue("SELECT VALUE FROM answer WHERE CONCAT(',', interviewId, ',') LIKE '%," + intId + ",%' AND questionID = " + study.MULTISESSIONEGOID);
	console.log("egovalue:" + egoValue);
	column = db.queryObjects("SELECT ID FROM question WHERE title = (SELECT q.title FROM question q WHERE q.ID = " + study.MULTISESSIONEGOID + ")").data;
	var multiIds = [];
	for (var k in column){
		multiIds.push(column[k].ID)
	}
	column = db.queryObjects("SELECT INTERVIEWID FROM answer WHERE questionId in (" + multiIds.join(",") + ") AND value = '"  + egoValue + "'" ).data;
	var interviewIds = [];
	for (var k in column){
		interviewIds.push(column[k].INTERVIEWID)
	}
	return interviewIds;
}

function loadStudy(id, intId){
	study = db.queryRowObject("SELECT * FROM study WHERE id = " + id);
	ego_id_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'EGO_ID' AND studyId = " + id + " ORDER BY ORDERING").data;
	ego_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'EGO' AND studyId = " + id + " ORDER BY ORDERING").data;
	alter_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'ALTER' AND studyId = " + id + " ORDER BY ORDERING").data;
	alter_pair_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'ALTER_PAIR' AND studyId = " + id + " ORDER BY ORDERING").data;
	network_questions = db.queryObjects("SELECT * FROM question WHERE subjectType = 'NETWORK' AND studyId = " + id + " ORDER BY ORDERING").data;
	options = db.queryObjects("SELECT * FROM questionOption WHERE studyId = " + id + " ORDER BY ORDERING").data;
	expressions = db.queryObjects("SELECT * FROM expression WHERE studyId = " + id).data;

	if(intId == null){
		loadFirst(id, 0, null);
	}else{
		interviewId = intId;
		page = db.queryValue("SELECT completed FROM interview WHERE id = " + intId);
		if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
			var interviewIds = getInterviewIds(intId);
			answers = db.queryObjects("SELECT * FROM answer WHERE interviewId in (" + interviewIds.join(",") + ")").data;
			console.log(answers);
		}else{
			answers = db.queryObjects("SELECT * FROM answer WHERE interviewId = " + intId).data;
		}
		for (k in answers){
			if(answers[k].QUESTIONTYPE == "ALTER")
				array_id = answers[k].QUESTIONID + "-" + answers[k].ALTERID1;
			else if(answers[k].QUESTIONTYPE == "ALTER_PAIR")
				array_id = answers[k].QUESTIONID + "-" + answers[k].ALTERID1 + "and" + answers[k].ALTERID2;
			else
				array_id = answers[k].QUESTIONID;
			answers[k].ID = parseInt(answers[k].ID);
			console.log(answers[k]);

			model[array_id] = answers[k];
		}
		if(page == -1)
			page = 0;
		loadFirst(id, page, intId);
	}
}

function getInterviewName(studyId){
	var whole_name = db.queryValue("SELECT name FROM study WHERE id = " + studyId) + " (" + db.queryValue("SELECT modified FROM study WHERE id = " + studyId) + ")";
	return whole_name;
}

function getEgoIdValue(interviewId){
	var studyId = db.queryValue("SELECT studyID FROM interview WHERE id = " + interviewId);
	var firstId = db.queryValue("SELECT ID FROM question WHERE studyId = " + studyId + " AND subjectType = 'EGO_ID' AND ORDERING = 0");
	return db.queryValue("SELECT value FROM answer WHERE questionId = " + firstId + " AND interviewId = " + interviewId);
}
