const Page = require('./page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthoringPage extends Page {
    /**
     * define selectors using getter methods
     */
    get inputCreate () { return $('#Study_name') }
    get btnCreate () { return $('input[type="submit"]') }
    get btnSaveStudy () { return $('input[value="Save"]') }
    get studyIntro () { return $('//*[@id="study-form"]/div[2]/div[1]/div/div[6]') }
    get studyEgoId () { return $('//*[@id="study-form"]/div[2]/div[2]/div/div[6]') }
    get btnCreateQ () { return $('input[value="Create"]') }
    get expressionName () { return $('//*[@id="Expression_name"]') }
    get expressionOperator () { return $('//*[@id="Expression_operator"]') }
    get expressionValue () { return $('//*[@id="Expression_value"]') }
    get expressionQuestion () { return $('//*[@id="questionId"]') }
    get expressionTimes () { return $('//*[@id="times"]') }
    get expressionUnanswered () { return $('//*[@id="Expression_resultForUnanswered"]') }
    get expressionSave () { return $('//*[@id="Expression"]/div/input') }
    get expressionSelect () { return $('//select[@id="form"]') }
    get expressionNew () { return $('//input[@value="New Expression"]') }
    get expressionId () { return $('//*[@id="expressionId"]') }
    get expressionCompare () { return $('//*[@id="compare"]') }

    createStudy (studyName) {
        this.inputCreate.setValue(studyName);
        this.btnCreate.click();
    }

    open () {
        return super.open('authoring');
    }
}

module.exports = new AuthoringPage();
