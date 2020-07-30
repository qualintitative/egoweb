const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../../.env");
const { alertIsPresent } = require('selenium-webdriver/lib/until');

function timeBits(timeUnits, span) {
  timeArray = [];
  bitVals = {
    'BIT_YEAR': 1,
    'BIT_MONTH': 2,
    'BIT_WEEK': 4,
    'BIT_DAY': 8,
    'BIT_HOUR': 16,
    'BIT_MINUTE': 32,
  };
  for (var k in bitVals) {
    if (timeUnits & bitVals[k]) {
      timeArray.push(k);
    }
  }

  if (timeArray.indexOf("BIT_" + span) != -1)
    return true;
  else
    return false;
}

describe('Complete Test', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('finish questions', function () {
    it('Go to question list page', function () {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//*[@id="content"]//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    var i = 1;
    for (index = 2; index < studyTest.questions.length; index++) {
      it("create " + studyTest.questions[index].title, function (q) {
        i++;
        var qId = '99999999999';
        idQLink = $('//*[@id="content"]//a[text()="Questions"]')
        btnNewQ = $('//*[@id="ui-id-1"]');
        //expect(btnNewQ).toBeExisting();
        idQLink.scrollIntoView(true);
        btnNewQ.click();
        browser.pause(2000);
        $("//*[@id='" + qId + "_title']").waitForExist(egoOpts.waitTime)
        $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[i].title);
        AuthoringPage.updateNoteField("#prompt" + qId, studyTest.questions[i].prompt);
        $('//*[@id="new"]').$('[name="Question[answerType]"]').waitForExist(egoOpts.waitTime)
        $('//*[@id="new"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[i].answerType)
        //browser.pause(5000);
        //console.log( $('//*[@id="new"]').$('[name="Question[subjectType]"]').getValue())
        $('//*[@id="new"]').$('[name="Question[subjectType]"]').selectByVisibleText(studyTest.questions[i].questionType)

        if (typeof studyTest.questions[i].params.dontKnowButton != "undefined")
          $('//*[@id="new"]').$('//input[@type="checkbox" and @name="Question[dontKnowButton]"]').click()
        if (typeof studyTest.questions[i].params.refuseButton != "undefined")
          $('//*[@id="new"]').$('//input[@type="checkbox" and @name="Question[refuseButton]"]').click()

        if (typeof studyTest.questions[i].params.askingStyleList != "undefined")
          $('//*[@id="new"]').$('//input[@type="checkbox" and @name="Question[askingStyleList]"]').click()
        if (typeof studyTest.questions[i].params.minLimitType != "undefined")
          $('//*[@name="Question[minLimitType]" and @value="' + studyTest.questions[i].params.minLimitType + '"]').click()
        if (typeof studyTest.questions[i].params.maxLimitType != "undefined")
          $('//*[@name="Question[maxLimitType]" and @value="' + studyTest.questions[i].params.maxLimitType + '"]').click()
        if (typeof studyTest.questions[i].params.minLiteral != "undefined")
          $('//*[@id="new"]').$('[name="Question[minLiteral]"]').setValue(studyTest.questions[i].params.minLiteral);
        if (typeof studyTest.questions[i].params.maxLiteral != "undefined")
          $('//*[@id="new"]').$('[name="Question[maxLiteral]"]').setValue(studyTest.questions[i].params.maxLiteral);
        if (typeof studyTest.questions[i].params.minCheckableBoxes != "undefined")
          $('//*[@id="new"]').$('[name="Question[minCheckableBoxes]"]').setValue(studyTest.questions[i].params.minCheckableBoxes);
        if (typeof studyTest.questions[i].params.maxCheckableBoxes != "undefined")
          $('//*[@id="new"]').$('[name="Question[maxCheckableBoxes]"]').setValue(studyTest.questions[i].params.maxCheckableBoxes);



        if (typeof studyTest.questions[i].params.timeUnits != "undefined") {
          var timeUnits = studyTest.questions[i].params.timeUnits;
          if (timeBits(timeUnits, "YEAR"))
            $('//*[@id="new"]').$('//label[text()="Years"]/preceding-sibling::input').click();
          if (timeBits(timeUnits, "MONTH"))
            $('//*[@id="new"]').$('//label[text()="Months"]/preceding-sibling::input').click();
          if (timeBits(timeUnits, "WEEK"))
            $('//*[@id="new"]').$('//label[text()="Weeks"]/preceding-sibling::input').click();
          if (timeBits(timeUnits, "DAY"))
            $('//*[@id="new"]').$('//label[text()="Days"]/preceding-sibling::input').click();
          if (timeBits(timeUnits, "HOUR"))
            $('//*[@id="new"]').$('//label[text()="Hours"]/preceding-sibling::input').click();
          if (timeBits(timeUnits, "MINUTE"))
            $('//*[@id="new"]').$('//label[text()="Minutes"]/preceding-sibling::input').click();
        }
        if (studyTest.questions[i].options.length > 0) {
          $('//*[@id="new"]').$('input[value="Create"]').click();
          btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = btnNewQ.$('..').getAttribute("id");
          if (typeof studyTest.questions[i].params.allButton != "undefined") {
            btnNewQ.click();
            btnNewQ.scrollIntoView();
            $('//*[@id="' + qId + '"]').$('[name="Question[minCheckableBoxes]"]').scrollIntoView();            
            browser.execute(function (qId) {
              qId = $($(".items > div")[$(".items > div").length - 1]).attr("id");
              $("#" + qId + "_allButton[type='checkbox']").click();
              $("#data-" + qId + " #question-form .form").scrollTop(600);
            })
            //$('//*[@id="' + qId + '"]').$('//input[@type="checkbox" and @name="Question[allButton]"]').scrollIntoView();
            //$('//*[@id="' + qId + '"]').$('//input[@type="checkbox" and @name="Question[allButton]"]').click()
            $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
            browser.pause(1000);
          }
          btnNewQ.$('.optionLink').click();
          browser.pause(1000);
          for (let j = 0; j < studyTest.questions[i].options.length; j++) {
            $('//*[@id="' + qId + '"]').$('#QuestionOption_name').waitForExist(egoOpts.waitTime);
            $('//*[@id="' + qId + '"]').$('#QuestionOption_name').setValue(studyTest.questions[i].options[j].name);
            $('//*[@id="' + qId + '"]').$('#QuestionOption_value').setValue(studyTest.questions[i].options[j].value)
            $('//*[@id="' + qId + '"]').$('input[value="Add Option"]').click();
            if (typeof studyTest.questions[i].options[j].otherSpecify != "undefined" && studyTest.questions[i].options[j].otherSpecify == true) {
              $('//*[@id="' + qId + '"]').$('//label[text()="' + studyTest.questions[i].options[j].name + '"]/parent::td/parent::tr').$("input").click()
            }
            browser.pause(500);
          }
        } else if (studyTest.questions[i].questionType == "NAME_GENERATOR") {
          if (typeof studyTest.questions[i].params.min != "undefined")
            $('//*[@id="new"]').$('//*[@id="minAltrNum"]').setValue(studyTest.questions[i].params.min);
          if (typeof studyTest.questions[i].params.max != "undefined")
            $('//*[@id="new"]').$('//*[@id="maxAltrNum"]').setValue(studyTest.questions[i].params.max);
          $('//*[@id="new"]').$('input[value="Create"]').click();
          btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = btnNewQ.$('..').getAttribute("id");
          btnNewQ.click()
          browser.pause(1000);
          $('//*[@id="' + qId + '"]').$('input[value="Alter Prompts"]').click();
          browser.pause(1000);
          if (typeof studyTest.questions[i].params.alterPrompts != "undefined") {
            var alterPrompts = studyTest.questions[i].params.alterPrompts;
            for (a in alterPrompts) {
              $('//*[@id="' + qId + '"]').$('//*[@id="AlterPrompt_afterAltersEntered"]').setValue(a);
              $('//*[@id="' + qId + '"]').$('//*[@id="AlterPrompt_display"]').setValue(alterPrompts[a]);
              $('//*[@id="' + qId + '"]').$('//input[@value="Add"]').click()
              browser.pause(500)
            }
          }
        } else {
          $('//*[@id="new"]').$('input[value="Create"]').click();
        }
      });
    }
  })
});

