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
    get studyEgoId () { return $('//main/div/div[1]/div/div[3]/div[2]') }
    get settingsLink () { return  $('//main//a[text()="Settings"]') }
    get egoIdLink () { return  $('//main//a[text()="Ego ID"]') }
    get questionsLink () { return  $('//main//a[text()="Questions"]') }
    get expressionsLink () { return  $('//main//a[text()="Expressions"]') }
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
    get studyLink () { return $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]') }


    async open (url) {
        if(url == null)
            url = '/admin'
        await super.open(url);
    }
}

module.exports = new AuthoringPage();
