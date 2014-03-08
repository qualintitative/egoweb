expression = [];

function evalExpression(id, interviewId, alterId1, alterId2)
{

	var array_id;
    if(!id || id == 0)
        return true;
    if(typeof expression[id] == "undefined")
    	expression[id] = db.queryRowObject("SELECT * FROM expression WHERE id = " + id);
    questionId = "";
    subjectType = "";
	if(expression[id] && expression[id].QUESTIONID){
		if(typeof study.MULTISESSIONEGOID != "undefined" && parseInt(study.MULTISESSIONEGOID) != 0){
			var interviewIds = getInterviewIds(interviewId);
			for(k in interviewIds){
				var studyId = db.queryValue("SELECT studyId FROM interview WHERE id = " + interviewIds[k]);
				if(db.queryValue("SELECT id FROM question WHERE id = "  + id + "and studyId = " + studyId))
					interviewId = interviewIds[k];
			}
		}
		row = db.queryRow("SELECT id,subjectType FROM question WHERE id = " + expression[id].QUESTIONID);
		if(row){
			questionId = row[0];
    		subjectType = row[1];
		}
	}

    comparers = {
    	'Greater':'>',
    	'GreaterOrEqual':'>=',
    	'Equals':'==',
    	'LessOrEqual':'<=',
    	'Less':'<'
    };

    if(questionId)
    	array_id = questionId;
    if(typeof alterId1 != 'undefined' && subjectType == 'ALTER'){

    	array_id += "-" + alterId1;
    }else if(typeof alterId2 != 'undefined' && subjectType == 'ALTER_PAIR')
    	array_id += 'and' + alterId2;
    if(typeof model[array_id] != "undefined")
		answer = model[array_id].VALUE;
    else
    	answer = "";
	console.log(expression[id].NAME + ":" + answer);
    if(expression[id].TYPE == "Text"){
    	if(!answer)
    		return expression[id].RESULTFORUNANSWERED;
    	if(expression[id].OPERATOR == "Contains"){
    		if(answer.indexOf(expression[id].VALUE) != -1)
    			return true;
    	}else if(expression[id].OPERATOR == "Equals"){
    		if(answer == expression[id].VALUE)
    			return true;
    	}
    }
    if(expression[id].TYPE == "Number"){
    	if(!answer)
    		return expression[id].RESULTFORUNANSWERED;
    	logic = answer + " " + comparers[expression[id].OPERATOR] + " " + expression[id].VALUE;
    	result = eval(logic);
    	return result;
    }
    if(expression[id].TYPE == "Selection"){
    	if(!answer)
    		return expression[id].RESULTFORUNANSWERED;
    	selectedOptions = answer.split(',');
    	options = expression[id].VALUE.split(',');
    	trues = 0;
    	for (var k in selectedOptions) {
    		if(expression[id].OPERATOR == "Some" && options.indexOf(selectedOptions[k]) != -1)
    			return true;
    		if(expression[id].OPERATOR == "None" && options.indexOf(selectedOptions[k]) != -1)
    			return false;
    		if(options.indexOf(selectedOptions[k]) != -1)
    			trues++;
    	}
    	if(expression[id].OPERATOR == "None" || (expression[id].OPERATOR == "All" && trues >= options.length))
    		return true;
    }
    if(expression[id].TYPE == "Counting"){
    	countingSplit = expression[id].VALUE.split(':');
		times = countingSplit[0];
		expressionIds = countingSplit[1];
		questionIds = countingSplit[2];

    	count = 0;
    	if(expressionIds != ""){
    		expressionIds = expressionIds.split(',');
    		for (var k in expressionIds) {
    			count = count + countExpression(expressionIds[k], interviewId, alterId1, alterId2);
    		}
    	}
    	if(questionIds != ""){
    		questionIds = questionIds.split(',');
    		for (var k in questionIds) {
    			count = count + countQuestion(questionIds[k], interviewId, expression[id].OPERATOR);
    		}
    	}
    	return (times * count);
    }
    if(expression[id].TYPE == "Comparison"){
    	compSplit =  expression[id].VALUE.split(':');
    	value = parseInt(compSplit[0]);
    	expressionId = parseInt(compSplit[1]);
    	result = evalExpression(expressionId, interviewId, alterId1, alterId2);
    	logic = result + " " + comparers[expression[id].OPERATOR] + " " + value;
    	result = eval(logic);
    	return result;
    }
    if(expression[id].TYPE == "Compound"){
	    console.log( expression[id].NAME + ":" + expression[id].VALUE);
    	subexpressions = expression[id].VALUE.split(',');
    	var trues = 0;
    	for (var k in subexpressions) {
    		// prevent infinite loops!
    		console.log(expression[id].NAME +":subexpression:"+ k +":");
    		if(parseInt(subexpressions[k]) == id)
    			continue;
    		isTrue = evalExpression(parseInt(subexpressions[k]), interviewId, alterId1, alterId2);
    		if(expression[id].OPERATOR == "Some" && isTrue){
    			return true;
    		}
    		if(isTrue)
    			trues++;
    	}
    	if(expression[id].OPERATOR == "None" && trues == 0)
    		return true;
    	else if (expression[id].OPERATOR == "All" && trues == subexpressions.length)
    		return true;
    }
    return false;

}

function countExpression(id, interviewId)
{
    if(evalExpression(id, interviewId))
    	return 1;
    else
    	return 0;
}

function countQuestion(questionId, interviewId, operator, alterId1, alterId2)
{
    if(questionId)
    	array_id = questionId;
    if(typeof alterId1 != 'undefined' && subjectType == 'ALTER')
    	array_id += "-" + alterId1;
    else if(typeof alterId2 != 'undefined' && subjectType == 'ALTER_PAIR')
    	array_id += 'and' + alterId2;
    if(typeof model[array_id] != "undefined")
		answer = model[array_id].VALUE;
    else
    	answer = "";

    if(!answer){
    	return 0;
    }else{
    	if(operator == "Sum")
    		return answer;
    	else
    		return 1;
    }
}