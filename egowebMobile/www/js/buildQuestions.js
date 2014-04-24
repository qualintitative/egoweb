function buildQuestions(id, pageNumber, interviewId){
	var page = [];
	i = 0;
	page[i] = new Object;
	if(study.INTRODUCTION != ""){
		if(i == pageNumber){
			introduction = new Question();
			introduction.ANSWERTYPE = "INTRODUCTION";
			introduction.PROMPT = study.INTRODUCTION;
			page[i][0] = introduction;
			return page[i];
		}
		i++;
		page[i] = new Object;
	}
	if(pageNumber == i){
		for(j in ego_id_questions){
			page[i][ego_id_questions[j].ID] = ego_id_questions[j];
		}
		return page[i];
	}
	if(interviewId != null){
		i++;
		page[i] = new Object;
		ego_question_list = new Object;
		prompt = "";
		for(j in ego_questions){
			console.log('eval:'+ego_questions[j].TITLE + ":"+ ego_questions[j].ANSWERREASONEXPRESSIONID+":"+evalExpression(ego_questions[j].ANSWERREASONEXPRESSIONID, interviewId));
			if(Object.keys(ego_question_list).length > 0 && prompt != ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
				if(pageNumber == i){
					page[i] = ego_question_list;
					return page[i];
				}
				ego_question_list = new Object;
				prompt = "";
				i++;
				page[i] = new Object;
			}

			if(evalExpression(ego_questions[j].ANSWERREASONEXPRESSIONID, interviewId) != true)
				continue;

			if(ego_questions[j].PREFACE != ""){
				if(pageNumber == i){
					preface = new Question();
					preface.ANSWERTYPE = "PREFACE";
					preface.PROMPT = ego_questions[j].PREFACE;
					page[i][0] = preface;
					return page[i];
				}
				i++;
				page[i] = new Object;
			}
			if(parseInt(ego_questions[j].ASKINGSTYLELIST)){
			    //console.log(prompt + ":" +ego_questions[j].PROMPT);
			    if(prompt == "" || prompt == ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"")){
			    	//console.log('list type question');
			    	prompt = ego_questions[j].PROMPT.replace(/<\/*[^>]*>/gm, '').replace(/(\r\n|\n|\r)/gm,"");
			    	ego_question_list[ego_questions[j].ID]=ego_questions[j];
			    }
			}else{
			    //console.log('going to next page');
			    if(pageNumber == i){
		    		page[i][ego_questions[j].ID] = ego_questions[j];
			    	return page[i];
			    }
			    i++;
			    page[i] = new Object;
			}
		}
		if(Object.keys(ego_question_list).length > 0){
			if(pageNumber == i){
				page[i] = ego_question_list;
				return page[i];
			}
			i++;
			page[i] = new Object;
		}

		if(pageNumber == i){
			alter_prompt = new Question();
			alter_prompt.ANSWERTYPE = "ALTER_PROMPT";
			alter_prompt.PROMPT = study.ALTERPROMPT;
			alter_prompt.studyId = study.ID;
			page[i][0] = alter_prompt;
			return page[i];
		}
		i++;
		page[i] = new Object;
		var alters = db.queryObjects("SELECT * FROM alters WHERE CONCAT(',', interviewId, ',') LIKE '%," + interviewId + ",%'").data;
		if(alters.length > 0){
			for(j in alter_questions){
				alter_question_list = new Object;
				for(k in alters){
					if(evalExpression(alter_questions[j].ANSWERREASONEXPRESSIONID, alters[k].INTERVIEWID, alters[k].ID) != true)
						continue;

					question = $.extend(true,{}, alter_questions[j]);
					question.PROMPT = question.PROMPT.replace("$$", alters[k].NAME);
					question.ALTERID1 = alters[k].ID;

					if(alter_questions[j].ASKINGSTYLELIST == 1){
						alter_question_list[question.ID + '-' + question.ALTERID1] = question;
					}else{
						if(alter_questions[j].PREFACE != ""){
							if(i == pageNumber){
								preface = new Question;
								preface.ANSWERTYPE = "PREFACE";
								preface.PROMPT = alter_questions[j].PREFACE;
								page[i][0] = preface;
								return page[i];
							}
							alter_questions[j].PREFACE = "";
							i++;
							page[i] = new Object;
						}
						if(i == pageNumber){
							page[i][question.ID + '-' + question.ALTERID1] = question;
							return page[i];
						}else {
							i++;
							page[i] = new Object;
						}
					}
				}
				if(alter_questions[j].ASKINGSTYLELIST == 1){
					if(Object.keys(alter_question_list).length > 0){
						if(alter_questions[j].PREFACE != ""){
							if(i == pageNumber){
								preface = new Question;
								preface.ANSWERTYPE = "PREFACE";
								preface.PROMPT = alter_questions[j].PREFACE;
								page[i][0] = preface;
								return page[i];
							}
							i++;
							page[i] = new Object;
						}
						if(i == pageNumber){
							page[i] = alter_question_list;
							return page[i];
						}
						i++;
						page[i] = new Object;
					}
				}
			}

			for(j in alter_pair_questions){
				alters2 = alters.slice(0);
				preface = new Question;
				preface.ANSWERTYPE = "PREFACE";
				preface.PROMPT = alter_pair_questions[j].PREFACE;
				for(k in alters){
					if(alter_pair_questions[j].SYMMETRIC)
						alters2.shift();
					alter_pair_question_list = new Object;
					for(l in alters2){
						if(alters[k].ID == alters2[l].ID)
							continue;
						if(evalExpression(alter_pair_questions[j].ANSWERREASONEXPRESSIONID, interviewId, alters[k].ID, alters2[l].ID) != true)
							continue;
						question = $.extend(true,{}, alter_pair_questions[j]);
						question.PROMPT = question.PROMPT.replace('$$1', alters[k].NAME);
						question.PROMPT = question.PROMPT.replace('$$2', alters2[l].NAME);
						question.ALTERID1 = alters[k].ID;
						question.ALTERID2 = alters2[l].ID;
						if(alter_pair_questions[j].ASKINGSTYLELIST){
							alter_pair_question_list[question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2] = question;
						}else{
							if(preface.PROMPT != ""){
								if(i == pageNumber){
									page[i][0] = preface;
									return page[i];
								}
								preface.PROMPT = "";
								i++;
								page[i] = new Object;
							}
							if(i == pageNumber){
								page[i][question.ID + '-' + question.ALTERID1 + 'and' + question.ALTERID2] = question;
								return page[i];
							}else{
								i++;
								page[i] = new Object;
							}
						}
					}
					if(alter_pair_questions[j].ASKINGSTYLELIST){
						if(Object.keys(alter_pair_question_list).length > 0){
							if(preface.PROMPT != ""){
								if(i == pageNumber){
									page[i][0] = preface;
									return page[i];
								}
								preface.PROMPT = "";
								i++;
								page[i] = new Object;
							}
							if(i == pageNumber){
								page[i] = alter_pair_question_list;
								return page[i];
							}
							i++;
							page[i] = new Object;
						}
					}
				}
			}
		}
		conclusion = new Question;
		conclusion.ANSWERTYPE = "CONCLUSION";
		conclusion.PROMPT = study.CONCLUSION;
		page[i][0] = conclusion;
		return page[i];

	}
	return false;
}