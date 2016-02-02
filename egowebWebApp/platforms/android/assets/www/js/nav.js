	function buildNav(pageNumber, interviewId){
		var i = 0;
		var pages = [];
		if(study.INTRODUCTION != ""){
			pages[i] = checkPage(i, pageNumber, "INTRODUCTION");
			i++;
		}
		pages[i] = checkPage(i, pageNumber, "EGO ID");
		i++;
		if(!interviewId){
			$('#navbox ul').html('');
			for(z in pages){
				$('#navbox ul').append("<li><a href='javascript:void(0)' onclick='view(" + studyId + "," + interviewId + ","  + z + ")'>" + z + ". " + pages[z] + "</a></li>");
			}
			return;
		}
		var prompt = "";
		var ego_question_list = '';
		for(j in ego_questions){
			if(evalExpression(ego_questions[j].ANSWERREASONEXPRESSIONID, interviewId) != true)
				continue;

			if((parseInt(ego_questions[j].ASKINGSTYLELIST) != 1 || prompt != ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")) && ego_question_list){
			    pages[i] = checkPage(i, pageNumber, ego_question_list.TITLE);
				prompt = "";
			    ego_question_list = '';
			    i++;
			}
			if(ego_questions[j].PREFACE != ""){
				pages[i] = checkPage(i, pageNumber, "PREFACE");
				i++;
			}
			if(parseInt(ego_questions[j].ASKINGSTYLELIST)){
			    prompt = ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"");
			    if(ego_question_list == '')
				    ego_question_list = ego_questions[j];
			}else{
			    pages[i] = checkPage(i, pageNumber, ego_questions[j].TITLE);
			    i++;
			}

		}
		if(ego_question_list){
			pages[i] = checkPage(i, pageNumber, ego_question_list.TITLE);
			ego_question_list = '';
			i++;
		}
		if(study.ALTERPROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
			pages[i] = checkPage(i, pageNumber, "ALTER_PROMPT");
			i++;
		}
		var alters = db.queryObjects("SELECT * FROM alters WHERE interviewId = " + interviewId).data;
		if(alters.length > 0){
			prompt = "";
			for(j in alter_questions){
				var alter_question_list = '';
				for(k in alters){
					if(evalExpression(alter_questions[j].ANSWERREASONEXPRESSIONID, interviewId, alters[k].ID) != true)
						continue;
					if(parseInt(alter_questions[j].ASKINGSTYLELIST)){
				    	alter_question_list = alter_questions[j];
				    }else{
						if(alter_questions[j].PREFACE != ""){
				    		pages[i] = checkPage(i, pageNumber, "PREFACE");
				    		i++;
				    	}
				    	pages[i] = checkPage(i, pageNumber, alter_questions[j].TITLE);
				    	i++;
				    }
				}
				if(parseInt(alter_questions[j].ASKINGSTYLELIST)){
				    if(alter_question_list){
				    	if(alter_questions[j].PREFACE != ""){
				    		pages[i] = checkPage(i, pageNumber, "PREFACE");
				    		i++;
				    	}
				    	pages[i] = checkPage(i, pageNumber, alter_question_list.TITLE);
				    	i++;
				    }
				}
			}
			prompt = "";
			for(j in alter_pair_questions){
				alters2 = alters.slice(0);
				preface = new Question;
				preface.ANSWERTYPE = "PREFACE";
				preface.PROMPT = alter_pair_questions[j].PREFACE;
				for(k in alters){
					if(alter_pair_questions[j].SYMMETRIC)
						alters2.shift();
					var alter_pair_question_list = '';
					for(l in alters2){
			    		if(alters[k].ID == alters2[l].ID)
			    			continue;
						if(evalExpression(alter_pair_questions[j].ANSWERREASONEXPRESSIONID, interviewId, alters[k].ID, alters2[l].ID) != true)
			    			continue;
			    		alter_pair_question_list = alter_pair_questions[j];
			    	}
			    	if(alter_pair_question_list){
						if(preface.PROMPT != ""){
				    		pages[i] = checkPage(i, pageNumber, "PREFACE");
							preface.PROMPT = "";
				    		i++;
				    	}
				    	pages[i] = checkPage(i, pageNumber, alter_pair_question_list.TITLE + " - " + alters[k].NAME);
				    	i++;
					}
				}
			}
			/*
			foreach(network_qs as question){
			    if(interviewId){
			    	expression = new Expression;
			    	if(!expression.evalExpression(question['answerReasonExpressionId'], interviewId))
			    		continue;
			    }
			    if(question['preface'] != ""){
			    	pages[i] = checkPage(i, pageNumber, "PREFACE");
			    	i++;
			    }
			    pages[i] = checkPage(i, pageNumber, question['title']);
			    i++;
			}*/
		}
		pages[i] = checkPage(i, pageNumber, "CONCLUSION");
		$('#navbox ul').html('');
		for(z in pages){
			$('#navbox ul').append("<li><a href='javascript:void(0)' onclick='view(" + studyId + "," + interviewId + ","  + z + ")'>" + z + ". " + pages[z] + "</a></li>");
		}
		return;
	}

	function checkPage(currentPage, pageNumber, text){
		if(currentPage == pageNumber)
			text = "<b>" + text + "</b>";
		return text;
	}