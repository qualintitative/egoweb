	// CORE FUNCTION
	function interpretTags(string, interviewId, alterId1, alterId2)
	{

		// parse out and replace variables
		vars = string.match(/<VAR (.+?) \/>/g);
		for(k in vars){
			thisVar = vars[k].match(/<VAR (.+?) \/>/)[1];
			question = db.queryRowObject("SELECT * FROM question WHERE title = '" + thisVar + "' AND studyId = '" + studyId + "'");
			if(question){
				if(interviewId != null){
					end = " AND interviewId = " + interviewId;
				}else{
					end = "";
				}
				lastAnswer = db.queryValue("SELECT value FROM answer WHERE questionId = " + question.ID + end + ' ORDER BY id DESC');
			}
			if(typeof lastAnswer != 'undefined'){
				if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
					option = db.queryValue("SELECT NAME FROM questionOption WHERE id = " + lastAnswer);
					if(option){
						lastAnswer = option;
					}else{
						lastAnswer = "";
					}
				}
				string = string.replace('<VAR ' + thisVar + ' />', lastAnswer);
			}else{
				string = string.replace('<VAR ' + thisVar + ' />', '');
			}
		}

		// performs calculations on questions
		calcs = string.match(/<CALC (.+?) \/>/g);
		for(j in calcs){
			calc = calcs[j].match(/<CALC (.+?) \/>/)[1];
			vars = calc.match(/(\w+)/g);
			for(k in vars){
				question = "";
				if(vars[k].match(/<VAR (.+?) \/>/)){
					thisVar = vars[k].match(/<VAR (.+?) \/>/)[1];
					question = db.queryRowObject('SELECT * FROM question WHERE title = "' + thisVar + '" AND studyId = ' +studyId);
				}
				if(question){
					if(interviewId != null){
						end = " AND interviewId = ". interviewId;
					}else{
						end = "";
					}
					lastAnswer = db.queryValue("SELECT VALUE FROM answer WHERE questionId = " + question.ID + end + ' ORDER BY id DESC');
				}
				if(typeof lastAnswer != 'undefined'){
					if(question.ANSWERTYPE == "MULTIPLE_SELECTION"){
						option = db.queryValue("SELECT NAME FROM questionOption WHERE id = " + lastAnswer);
						if(option){
							lastAnswer = option;
						}else{
							lastAnswer = "";
						}
					}
					logic =  calc.replace(thisVar, lastAnswer);
				}else{
					logic =  calc.replace(thisVar, '0');
				}
			}
			try{
				calculation = eval(logic);
			}catch(err){
				calculation = "";
			}
			string = string.replace("<CALC " + calc + " />", calculation);
		}

		// counts numbers of times question is answered with string
		counts = string.match(/<COUNT (.+?) \/>/g);
		for(k in counts){
			count = counts[k].match(/<COUNT (.+?) \/>/)[1];
			parts = count.split(' ');
			qTitle = parts[0];
			answer = aprts[1];
			answer = answer.replace ('"', '');
			question = db.queryRowObject("SELECT * FROM question WHERE title = '" + qTitle + "' AND studyId = " + studyId);
			if(!question)
				continue;
			if(question.ANSWERTYPE == "SELECTION" || question.ANSWERTYPE == "MULTIPLE_SELECTION"){
				option = db.queryRowObject("SELECT * FROM questionOption WHERE name = '" + answer + "' AND questionId = " + question.ID);
				if(!option)
					continue;
				if(interviewId != null){
					end = " AND interviewId = " + interviewId;
				}else{
					end = "";
				}
				answers = db.queryObjects("SELECT * FROM answer WHERE questionId = " +  question.id + " AND FIND_IN_SET(" + option.ID  + ' ,value)' + end).data;
			}else{
				answers = db.queryObjects('SELECT * FROM answer WHERE value = "' +  answer + '"' + end).data;
			}
			string =  string.replace("<COUNT " + count +" />", answers.length);
		}

		// same as count, but limited to specific alter / alter pair questions
		containers  = string.match(/<CONTAINS (.+?) \/>/g);
		for(k in containers){
			contains = containers[k].match(/<CONTAINS (.+?) \/>/)[1];
			parts = contains.split(/\s/);
			qTitle = parts[0];
			answer = aprts[1];
			answer = answer.replace ('"', '');
			question = db.queryRowObject("SELECT * FROM question WHERE title = '" + qTitle + "' AND studyId = " + studyId);
			if(!question)
				continue;
			if(interviewId != null){
				end = " AND interviewId = " + interviewId;
				if(is_numeric(alterId1))
					end += " AND alterId1 = " + alterId1;
				if(is_numeric(alterId2))
					end += " AND alterId2 = " + alterId2;
			}else{
				end = "";
			}
			if(question.answerType == "SELECTION" || question.answerType == "MULTIPLE_SELECTION"){
				option = db.queryRowObject("SELECT * FROM questionOption WHERE name = '" + answer + "' AND questionId = " + question.ID);
				if(!option)
					continue;
				answers = db.queryObjects("SELECT * FROM answer WHERE questionId = " +  question.id + " AND FIND_IN_SET(" + option.ID  + ' ,value)' + end).data;
			}else{
				answers = db.queryObjects('SELECT * FROM answer WHERE value = "' +  answer + '"' + end).data;
			}
			string =  string.replace("<CONTAINS "+contains+" />", answers.length);
		}

		// parse out and show logics
		showlogics = string.match(/<IF (.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\" \/>/g);
		for(k in showlogics){
			showlogic = showlogics[k];
			exp = showlogic.match(/\<IF (.+?) (==|!=|<|>|<=|>=)+ (.+?) \"(.+?)\"/);
			if(exp.length > 1){
				for(i = 1; i < 3; i++){
					if(i == 2 || !isNaN(parseInt(exp[i])))
						continue;
					if(exp[i].match("/>")){
						exp[i] = interpretTags(exp[i]);
					}else{
						question = db.queryRowObject("SELECT * FROM question WHERE title = '" + exp[i] + "' AND studyId = " + studyId);
						if(interviewId != null){
							end = " AND interviewId = " + interviewId;
						}else{
							end = "";
						}
						if(question)
							lastAnswer = db.queryValue("SELECT VALUE FROM answer WHERE questionId = " + question.ID + end + ' ORDER BY id DESC');
						else
							return false;
						exp[i] = lastAnswer;
					}
				}
				logic = exp[1] + ' ' + exp[2] + ' ' + exp[3];
				show = eval(logic);
				if(show){
					string =  string.replace(showlogic, exp[4]);
				}else{
					string =  string.replace(showlogic, "");
				}
			}
		}
		return string;
	}