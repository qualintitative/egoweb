
string = {};
string.repeat = function(string, count)
{
	return new Array(count+1).join(string);
}

string.count = function(string)
{
	var count = 0;

	for (var i=1; i<arguments.length; i++)
	{
		var results = string.match(new RegExp(arguments[i], 'g'));
		count += results ? results.length : 0;
	}

	return count;
}

array = {};
array.merge = function(arr1, arr2)
{
	for (var i in arr2)
	{
		if (arr1[i] && typeof arr1[i] == 'object' && typeof arr2[i] == 'object')
			arr1[i] = array.merge(arr1[i], arr2[i]);
		else
			arr1[i] = arr2[i]
	}

	return arr1;
}

array.print = function(obj)
{
	var arr = [];
	$.each(obj, function(key, val) {
		var next = key + ": ";
		next += $.isPlainObject(val) ? array.print(val) : val;
		arr.push( next );
	  });

	return "{ " +  arr.join(", ") + " }";
}

node = {};

node.objectify = function(node, params)
{
	if (!params)
		params = {};

	if (!params.selector)
		params.selector = "*";

	if (!params.key)
		params.key = "name";

	if (!params.value)
		params.value = "value";

	var o = {};
	var indexes = {};

	$(node).find(params.selector+"["+params.key+"]").each(function()
	{
		var name = $(this).attr(params.key),
			value = $(this).attr(params.value);
		console.log(params);
		var obj = $.parseJSON("{"+name.replace(/([^\[]*)/, function()
		{
			return '"'+arguments[1]+'"';
		}).replace(/\[(.*?)\]/gi, function()
		{
			if (arguments[1].length == 0)
			{
				var index = arguments[3].substring(0, arguments[2]);
				indexes[index] = indexes[index] !== undefined ? indexes[index]+1 : 0;

				return ':{"'+indexes[index]+'"';
			}
			else
				return ':{"'+escape(arguments[1])+'"';
		})+':"'+value.replace(/[\\"]/gi, function()
		{
			return "\\"+arguments[0];
		})+'"'+string.repeat('}', string.count(name, ']'))+"}");

		o = array.merge(o, obj);
	});

	return o;
}

function save(id, page){
	post = node.objectify($('.question form'), {});
	errorModel = new ErrorModel;
	answers = post.answer;
	errors = 0;
	for(var k in answers){
		answer = answers[k];

		if(answer.ANSWERTYPE == "INTRODUCTION" || answer.ANSWERTYPE == "PREFACE"){
			view(studyId, interviewId, page + 1);
			return false;
		}

		if(!answer.INTERVIEWID && !interviewId && answer.QUESTIONTYPE == "EGO_ID" && answer.VALUE != ""){
			var newIntId = db.queryValue("SELECT id FROM interview ORDER BY id DESC");
			if(!newIntId)
				newIntId = 0;
			newIntId = parseInt(newIntId) + 1;
			interview = [
				newIntId,
				1,
				studyId,
				0,
				Math.round(Date.now()/1000),
				''
			]
			db.catalog.getTable('interview').insertRow(interview);

			for(k in ego_questions){
				var a_id = ego_questions[k].ID;
				if(typeof model[a_id] == "undefined"){
				    var  newId = db.queryValue("SELECT id FROM answer ORDER BY id DESC");
				    if(!newId)
				    	newId = 0;
					newAnswer = [
						parseInt(newId) + 1,
						1,
						ego_questions[k].ID,
						newIntId,
						'',
						'',
						study.VALUENOTYETANSWERED,
						'',
						'NONE',
						study.ID,
						ego_questions[k].SUBJECTTYPE,
						ego_questions[k].ANSWERTYPE
					];
					db.catalog.getTable('answer').insertRow(newAnswer);
					db.commit();
				}
			}
			interviewId = newIntId;
		}

		if(answer.ANSWERTYPE == "CONCLUSION"){
			interview = db.queryRow("SELECT * FROM interview WHERE id = " + interviewId);
			interview = [
				interviewId,
				1,
				studyId,
				-1,
				interview[4],
				Math.round(Date.now()/1000)
			]
			db.catalog.getTable('interview').updateRow(interview);
			db.commit();
			window.open("interview.html",'_self');
		}

		if(answer.QUESTIONTYPE == "ALTER")
			array_id = answer.QUESTIONID + "-" + answer.ALTERID1;
		else if(answer.QUESTIONTYPE == "ALTER_PAIR")
			array_id = answer.QUESTIONID + "-" + answer.ALTERID1 + "and" + answer.ALTERID2;
		else
			array_id = answer.QUESTIONID;

		if(answer.ANSWERTYPE == "ALTER_PROMPT"){
			// no Answer to save, go to next page
			if(countAlters() < study.MINALTERS){
				errorModel.addError('0', 'Please list ' + study.MINALTERS + ' people');
				view(studyId, interviewId, page);
			}else{
				if(alter_questions.length > 0){
					var alters = db.queryObjects("SELECT * FROM alters WHERE interviewId = " + interviewId).data;
					testAlterQ = alter_questions.slice(0).shift();
					testAlter = alters.shift();
					var a_id = testAlterQ.ID + "-" + testAlter.ID;
					if(typeof model[a_id] == "undefined"){

					for(k in alter_questions){
						for(a in alters){
							a_id = alter_questions[k].ID + "-" + alters[a].ID;
							if(typeof model[a_id] == "undefined"){
								newId = db.queryValue("SELECT id FROM answer ORDER BY id DESC");
								newId = parseInt(newId) + 1;
								newAnswer = [
								    newId,
								    1,
								    alter_questions[k].ID,
								    interviewId,
								    alters[a].ID,
								    '',
								    study.VALUENOTYETANSWERED,
								    '',
								    'NONE',
								    study.ID,
								    alter_questions[k].SUBJECTTYPE,
								    alter_questions[k].ANSWERTYPE
								];
								db.catalog.getTable('answer').insertRow(newAnswer);
							}
						}
					}
					for(k in alter_pair_questions){
						for(a in alters){
							alters2 = alters.slice(0);
							if(alter_pair_questions[k].SYMMETRIC)
								alters2.shift();
							for(b in alters2){
								a_id = alter_pair_questions[k].ID + "-" + alters[a].ID + "and" + alters2[b].ID;
								if(typeof model[a_id] == "undefined"){
									newId = db.queryValue("SELECT id FROM answer ORDER BY id DESC");
									newId = parseInt(newId) + 1;
									newAnswer = [
									    newId,
									    1,
									    alter_pair_questions[k].ID,
									    interviewId,
									    alters[a].ID,
									    alters2[b].ID,
									    study.VALUENOTYETANSWERED,
									    '',
									    'NONE',
									    study.ID,
									    alter_pair_questions[k].SUBJECTTYPE,
									    alter_pair_questions[k].ANSWERTYPE
									];
									db.catalog.getTable('answer').insertRow(newAnswer);
								}
							}
						}
					}

					for(k in network_questions){
						a_id = network_questions[k].ID;

						if(typeof model[a_id] == "undefined"){
							newId = db.queryValue("SELECT id FROM answer ORDER BY id DESC");
							newId = parseInt(newId) + 1;
							newAnswer = [
								newId,
						        1,
						        network_questions[k].ID,
						        interviewId,
						        '',
						        '',
						        study.VALUENOTYETANSWERED,
						        '',
						        'NONE',
						        study.ID,
						        network_questions[k].SUBJECTTYPE,
						        network_questions[k].ANSWERTYPE
						    ];
						    db.catalog.getTable('answer').insertRow(newAnswer);
						}
					}
					}
					db.commit();
				}
				view(studyId, interviewId, page + 1);
			}
			return;
		}

		// check for list range limitations
		checks = 0;
		if(parseInt(questions[array_id].WITHLISTRANGE) != 0){
			for(i in answers){
					console.log(answers[i].VALUE + ":" + questions[array_id].LISTRANGESTRING);
				if(answers[i].VALUE.split(',').indexOf(questions[array_id].LISTRANGESTRING) != -1){
					checks++;
				}
			}
			if(checks < questions[array_id].MINLISTRANGE || checks > questions[array_id].MAXLISTRANGE){
				errorMsg = "";
				if(questions[array_id].MINLISTRANGE && questions[array_id].MAXLISTRANGE){
					if(questions[array_id].MINLISTRANGE != questions[array_id].MAXLISTRANGE)
						errorMsg = questions[array_id].MINLISTRANGE + " - " + questions[array_id].MAXLISTRANGE;
					else
						errorMsg = "just " + questions[array_id].MINLISTRANGE;
				}else if(!questions[array_id].MINLISTRANGE && !questions[array_id].MAXLISTRANGE){
						errorMsg = "up to " + questions[array_id].MAXLISTRANGE;
				}else{
						errorMsg = "at least " + questions[array_id].MINLISTRANGE;
				}
				errorModel.addError(array_id, "Please select "  + errorMsg + " response(s).  You selected " + checks);
			}
		}

		if(answer.VALUE == "" && answer.SKIPREASON == "NONE" && answer.ANSWERTYPE == "TEXTUAL"){
			errorModel.addError(array_id, 'Value cannot be blank');
			errors++;
		}

		if(answer.ANSWERTYPE == "DATE"){
			var date = answer.VALUE.match(/(January|February|March|April|May|June|July|August|September|October|November|December) (\d{1,2}) (\d{4})/);
			var time = answer.VALUE.match(/(\d+):(\d+) (AM|PM)/);

			if(typeof time != "undefined" && time && time.length > 2){
			    if(parseInt(time[1]) < 1 || parseInt(time[1]) > 12){
			    	errorMsg = 'Please enter 1 to 12 for HH';
				    errorModel.addError(array_id, errorMsg);
			    }
			    console.log(time);
			    if(parseInt(time[2]) < 0 || parseInt(time[2]) > 59){
			    	errorMsg = 'Please enter 0 to 59 for MM';
				    errorModel.addError(array_id, errorMsg);
			    }
			}else{
		    	errorMsg = 'Please enter the time of day';
			    errorModel.addError(array_id, errorMsg);
			}
			if(typeof date != "undefined" && date && date.length > 3){
			    if(parseInt(date[2]) < 1 || parseInt(date[2]) > 31){
			    	errorMsg = 'Please enter a different number for the day of month';
				    errorModel.addError(array_id, errorMsg);
			    }
			}
		}

		// Custom validators
		if(answer.ANSWERTYPE == "NUMERICAL"){
			min = ""; max = ""; numberErrors = 0; showError = false;
			if((answer.VALUE == "" && answer.SKIPREASON == "NONE") || (answer.VALUE != "" && isNaN(parseInt(answer.VALUE))))
				errorModel.addError(array_id, "Please enter a number");
			if(questions[array_id].MINLIMITTYPE == "NLT_LITERAL"){
				min = questions[array_id].MINLITERAL;
			}else if(questions[array_id].MINLIMITTYPE == "NLT_PREVQUES"){
				min = db.queryValue("SELECT value FROM answer WHERE interviewId = " + answer.INTERVIEWID + " AND QUESTIONID = " + questions[array_id].MINPREVQUES);
			}
			if(questions[array_id].MAXLIMITTYPE == "NLT_LITERAL"){
				max = questions[array_id].MAXLITERAL;
			}else if(questions[array_id].MAXLIMITTYPE == "NLT_PREVQUES"){
				max = db.queryValue("SELECT value FROM answer WHERE interviewId = " + answer.INTERVIEWID + " AND QUESTIONID = " + questions[array_id].MAXPREVQUES);
			}
			if(min !== "")
				numberErrors++;
			if(max !== "")
				numberErrors = numberErrors + 2;
			if(((max !== "" && parseInt(answer.VALUE) > parseInt(max))  ||  (min !== "" && parseInt(answer.VALUE) < parseInt(min))) && answer.SKIPREASON == "NONE")
				showError = true;

			if(numberErrors == 3 && showError)
				errorMsg = "The range of valid answers is " + min + " to " + max + ".";
			else if (numberErrors == 2 && showError)
				errorMsg = "The range of valid answers is " + max + " or fewer.";
			else if (numberErrors == 1 && showError)
				errorMsg = "The range of valid answers is " + min + " or greater.";

			if(showError)
				errorModel.addError(array_id, errorMsg);
		}

		if(answer.ANSWERTYPE == "MULTIPLE_SELECTION"){
			console.log(questions[array_id]);
			min = questions[array_id].MINCHECKABLEBOXES;
			max = questions[array_id].MAXCHECKABLEBOXES;
			numberErrors = 0; showError = false; errorMsg = "";
			if(min !== "")
				numberErrors++;
			if(max !== "")
				numberErrors = numberErrors + 2;

			checkedBoxes = answer.VALUE.split(',').length;
			if(!answer.VALUE)
				checkedBoxes = 0;
			console.log('min:' + min + ':max:' + max + ':checked:' + checkedBoxes);

			if ((answer.VALUE === "" || checkedBoxes < min || checkedBoxes > max) && answer.SKIPREASON == "NONE")
				showError = true;

			s='';
			if(max != 1)
				s = 's';
			if(parseInt(questions[array_id].ASKINGSTYLELIST) == 1)
				s += ' for each row';
			if(numberErrors == 3 && min == max && showError)
				errorMsg = "Select " + max  + " response" + s + " please.";
			else if(numberErrors == 3 && min != max && showError)
				errorMsg = "Select " + min + " to " + max + " response" + s + " please.";
			else if (numberErrors == 2 && showError)
				errorMsg = "You may select up to " + max + " response" + s + " please.";
			else if (numberErrors == 1 && showError)
				errorMsg = "You must select at least " + min + " response" + s + " please.";
			if(answer.OTHERSPECIFYTEXT && showError)
				showError = false;
			if(showError){
				errorModel.addError(array_id, errorMsg);
			}
		}

		if(interviewId){
			answer.INTERVIEWID = interviewId;
			console.log('answer');
			console.log(answer);
			if(!errorModel.getError(array_id)){
				model[array_id]= answer;
				console.log(model[array_id].ID);
				if(!answer.ID){
			    	newId = db.queryValue("SELECT id FROM answer ORDER BY id DESC");
			    	model[array_id].ID = newId + 1;
					db.catalog.getTable('answer').insertRow(objToArray(model[array_id]));
				}else{
					model[array_id].ID = parseInt(model[array_id].ID);
					db.catalog.getTable('answer').updateRow(objToArray(model[array_id]));
				}
				completed = page + 1;
			}else{
				completed = page;
				errors++;
			}
			if(parseInt(db.queryValue("SELECT completed FROM interview WHERE id = " + interviewId)) != -1){
				interview = db.queryRow("SELECT * FROM interview WHERE id = " + interviewId);
				interview = [
					interviewId,
					1,
					studyId,
					completed,
					interview[4],
					interview[5]
				]
				db.catalog.getTable('interview').updateRow(interview);
			}
			db.commit();
		}
	}

	if(errors == 0) {
		view(id, interviewId, page + 1);
	}else{
		console.log('there were errors');
		view(id, interviewId, page);
	}
}