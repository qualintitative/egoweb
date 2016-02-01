function Question()
{
	this.ID = undefined;
	this.ACTIVE = undefined;
	this.TITLE = undefined;
	this.PROMPT = undefined;
	this.PREFACE = undefined;
	this.CITATION = undefined;
	this.SUBJECTTYPE = undefined;
	this.ANSWERTYPE = undefined;
	this.ASKINGSTYLELIST = undefined;
	this.ORDERING = undefined;
	this.OTHERSPECIFY = undefined;
	this.NONEBUTTON = undefined;
	this.ALLBUTTON = undefined;
	this.PAGELEVELDONTKNOWBUTTON = undefined;
	this.PAGELEVELREFUSEBUTTON = undefined;
	this.DONTKNOWBUTTON = undefined;
	this.REFUSEBUTTON = undefined;
	this.ALLOPTIONSTRING = undefined;
	this.USELFEXPRESSION = undefined;
	this.MINLIMITTYPE = undefined;
	this.MINLITERAL = undefined;
	this.MINPREVQUES = undefined;
	this.MAXLIMITTYPE = undefined;
	this.MAXLITERAL = undefined;
	this.MAXPREVQUES = undefined;
	this.MINCHECKABLEBOXES = undefined;
	this.MAXCHECKABLEBOXES = undefined;
	this.WITHLISTRANGE = undefined;
	this.LISTRANGESTRING = undefined;
	this.MINLISTRANGE = undefined;
	this.MAXLISTRANGE = undefined;
	this.TIMEUNITS = undefined;
	this.SYMMETRIC = undefined;
	this.KEEPONSAMEPAGE = undefined;
	this.STUDYID = undefined;
	this.ANSWERREASONEXPRESSIONID = undefined;
	this.NETWORKRELATIONSHIPEXPRID = undefined;
	this.NETWORKNSHAPEQID = undefined;
	this.NETWORKNCOLORQID = undefined;
	this.NETWORKNSIZEQID = undefined;
	this.NETWORKECOLORQID = undefined;
	this.NETWORKESIZEQID = undefined;
	this.USEALTERLISTFIELD = undefined;
};

function Answer(){
	this.ID = undefined;
	this.ACTIVE = undefined;
	this.QUESTIONID = undefined;
	this.INTERVIEWID = undefined;
	this.ALTERID1 = undefined;
	this.ALTERID2 = undefined;
	this.VALUE = '';
	this.OTHERSPECIFYTEXT = undefined;
	this.SKIPREASON = 'NONE';
	this.STUDYID = undefined;
	this.QUESTIONTYPE = undefined;
	this.ANSWERTYPE = undefined;
}

function ErrorModel(){
	this.errors = new Object;
	this.getErrors = function(){
		return this.errors;
	}
	this.getError = function(array_id){
		return this.errors[array_id];
	}
	this.addError = function(array_id, error){
		this.errors[array_id] = error;
	}
}