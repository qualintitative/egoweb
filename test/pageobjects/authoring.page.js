const Page = require('./page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class AuthoringPage extends Page {
    /**
     * define selectors using getter methods
     */
    get inputCreate () { return $('#Study_name') }
    get btnCreate () { return $('button[type="submit"]') }
    get btnSaveStudy () { return $('//*[@id="saveStudy"]') }
    get studyIntro () { return $('//main/div/form/div[2]/div[1]/div/div[3]/div[2]') }
    get studyEgoId () { return $('//*[@id="study-form"]/div[2]/div[2]/div/div[6]') }
    get btnCreateQ () { return $('button=Create') }
    get expressionName () { return $('//*[@id="Expression_name"]') }
    get expressionOperator () { return $('//*[@name="Expression[operator]"]') }
    get expressionValue () { return $('//*[@id="Expression_value"]') }
    get expressionQuestion () { return $('//*[@id="Expression_questionId"]') }
    get expressionTimes () { return $('//*[@id="times"]') }
    get expressionUnanswered () { return $('//*[@name="Expression[resultForUnanswered]"]') }
    get expressionSave () { return $('button=Create') }
    get expressionSelect () { return $('//select[@id="form-type"]') }
    get expressionId () { return $('//*[@id="expressionId"]') }
    get expressionCompare () { return $('//*[@id="compare"]') }

    createStudy (studyName) {
        this.inputCreate.setValue(studyName);
        this.btnCreate.click();
    }

    open () {
        return super.open('/admin');
    }
}

module.exports = new AuthoringPage();
