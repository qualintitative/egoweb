const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");
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
      //AuthoringPage.open();
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//main//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    var i = 1;
    for (index = 2; index < studyTest.questions.length; index++) {
      it("create " + studyTest.questions[index].title, function (q) {
        i++;
        var qId = '0';
        var nltVals = ["NLT_LITERAL","NLT_PREVQUES","NLT_NONE"];
        idQLink = $('//main//a[text()="Questions"]')
        btnNewQ = $('button=Create New Question');
        //expect(btnNewQ).toBeExisting();
        browser.pause(1000);
        btnNewQ.click();
        btnNewQ.scrollIntoView();
        browser.pause(1000);
        $("//*[@id='" + qId + "_title']").waitForExist(egoOpts.waitTime)
        $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[i].title);
        AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[i].prompt);
        $('//*[@id="form-0"]').$('[name="Question[answerType]"]').waitForExist(egoOpts.waitTime)
        $('//*[@id="form-0"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[i].answerType)
        //browser.pause(5000);
        //console.log( $('//*[@id="form-0"]').$('[name="Question[subjectType]"]').getValue())
        $('//*[@id="form-0"]').$('[name="Question[subjectType]"]').selectByVisibleText(studyTest.questions[i].questionType)
        browser.pause(2000);

        if (typeof studyTest.questions[i].params.dontKnowButton != "undefined")
          $('//*[@id="form-0"]').$('//label[@for="' + qId + '_dontKnowButton"]').click()
        if (typeof studyTest.questions[i].params.refuseButton != "undefined")
          $('//*[@id="form-0"]').$('//label[@for="' + qId + '_refuseButton"]').click()
        if (typeof studyTest.questions[i].params.askingStyleList != "undefined")
          $('//*[@id="form-0"]').$('//label[@for="' + qId + '_askingStyleList"]').click()
        if (typeof studyTest.questions[i].params.allButton != "undefined") 
          $('//*[@id="form-0"]').$("//label[@for='" + qId + "_allButton']").click();

        if (typeof studyTest.questions[i].params.minLimitType != "undefined")
          $('//*[@id="form-0"]').$('//label[@for="' + qId + '_minLimitType_' + nltVals.indexOf(studyTest.questions[i].params.minLimitType) + '"]').click()
        if (typeof studyTest.questions[i].params.maxLimitType != "undefined")
          $('//*[@id="form-0"]').$('//label[@for="' + qId + '_maxLimitType_' + nltVals.indexOf(studyTest.questions[i].params.maxLimitType) + '"]').click()
        if (typeof studyTest.questions[i].params.minLiteral != "undefined")
          $('//*[@id="form-0"]').$('[name="Question[minLiteral]"]').setValue(studyTest.questions[i].params.minLiteral);
        if (typeof studyTest.questions[i].params.maxLiteral != "undefined")
          $('//*[@id="form-0"]').$('[name="Question[maxLiteral]"]').setValue(studyTest.questions[i].params.maxLiteral);
        if (typeof studyTest.questions[i].params.minCheckableBoxes != "undefined")
          $('//*[@id="form-0"]').$('[name="Question[minCheckableBoxes]"]').setValue(studyTest.questions[i].params.minCheckableBoxes);
        if (typeof studyTest.questions[i].params.maxCheckableBoxes != "undefined")
          $('//*[@id="form-0"]').$('[name="Question[maxCheckableBoxes]"]').setValue(studyTest.questions[i].params.maxCheckableBoxes);



        if (typeof studyTest.questions[i].params.timeUnits != "undefined") {
          $('//*[@id="form-0"]').$('//label[contains(text(),"Hours")]').waitForExist(egoOpts.waitTime)

          var timeUnits = studyTest.questions[i].params.timeUnits;
          if (timeBits(timeUnits, "YEAR"))
            $('//*[@id="form-0"]').$('//label[contains(text(),"Years")]').click();
          if (timeBits(timeUnits, "MONTH"))
            $('//*[@id="form-0"]').$('//label[contains(text(),"Months")]').click();
          if (timeBits(timeUnits, "WEEK"))
            $('//*[@id="form-0"]').$('//label[contains(text(),"Weeks")]').click();
          if (timeBits(timeUnits, "DAY"))
          $('//label[@for="0-time-DAY"]').click();
          if (timeBits(timeUnits, "HOUR"))
            $('//label[@for="0-time-HOUR"]').click();
          if (timeBits(timeUnits, "MINUTE"))
            $('//label[@for="0-time-MINUTE"]').click();
            browser.pause(500);
        }
        if (studyTest.questions[i].options.length > 0) {
          $('//*[@id="form-0"]').$('button=Create').click();
          browser.pause(3000);
          btnNewQ = $("//button[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
          btnNewQ.click();
          btnNewQ.scrollIntoView();
          browser.pause(1000);



          // $('//*[@id="form-' + qId + '"]').$('[name="Question[minCheckableBoxes]"]').scrollIntoView();
/*          
            browser.execute(function (qId) {
              qId = $($(".items > div")[$(".items > div").length - 1]).attr("id");
              $("#" + qId + "_allButton[type='checkbox']").click();
              $("#data-" + qId + " #question-form .form").scrollTop(600);
            })*/
            //$('//*[@id="form-' + qId + '"]').$('//input[@type="checkbox" and @name="Question[allButton]"]').scrollIntoView();
            //$('//*[@id="form-' + qId + '"]').$('//input[@type="checkbox" and @name="Question[allButton]"]').click()
         
          //btnNewQ.$('.optionLink').click();

          for (let j = 0; j < studyTest.questions[i].options.length; j++) {
            $('//*[@id="form-' + qId + '"]').$('//input[@name="' + qId + '_QuestionOption_name"]').waitForExist(egoOpts.waitTime);
            $('//*[@id="form-' + qId + '"]').$('//input[@name="' + qId + '_QuestionOption_name"]').setValue(studyTest.questions[i].options[j].name);
            $('//*[@id="form-' + qId + '"]').$('//input[@name="' + qId + '_QuestionOption_value"]').setValue(studyTest.questions[i].options[j].value)
            if (typeof studyTest.questions[i].options[j].otherSpecify != "undefined" && studyTest.questions[i].options[j].otherSpecify == true)
              $('//*[@id="form-' + qId + '"]').$('//label[@for="' + qId + '_QuestionOption_otherSpecify"]').click()
            $('//*[@id="form-' + qId + '"]').$('button=Add').click();
            browser.pause(2000);
          }
          for (let j = 0; j < studyTest.questions[i].options.length; j++) {

          }
        } else if (studyTest.questions[i].questionType == "NAME_GENERATOR") {
          if (typeof studyTest.questions[i].params.min != "undefined")
            $('//*[@id="form-0"]').$('//*[@id="' + qId + '_minLiteral"]').setValue(studyTest.questions[i].params.min);
          if (typeof studyTest.questions[i].params.max != "undefined")
            $('//*[@id="form-0"]').$('//*[@id="' + qId + '_maxLiteral"]').setValue(studyTest.questions[i].params.max);
          $('//*[@id="form-0"]').$('button=Create').click();
          //$("//button[contains(text(),'" + studyTest.questions[i].title + "')]").waitForExist(egoOpts.waitTime);
          browser.pause(3000);

          btnNewQ = $("//button[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
          btnNewQ.click();
          btnNewQ.scrollIntoView();
          browser.pause(1000);

          if (typeof studyTest.questions[i].params.alterPrompts != "undefined") {
            var alterPrompts = studyTest.questions[i].params.alterPrompts;
            for (a in alterPrompts) {
              $('//*[@id="form-' + qId + '"]').$('//input[@name="AlterPrompt[afterAltersEntered]"]').setValue(a);
              $('//*[@id="form-' + qId + '"]').$('//input[@name="AlterPrompt[display]"]').setValue(alterPrompts[a]);
              $('//*[@id="form-' + qId + '"]').$('button=Add').click()
              browser.pause(1000)
            }
          }
        } else {
          $('//*[@id="form-0"]').$('button=Create').click();
          //$("//button[contains(text(),'" + studyTest.questions[i].title + "')]").waitForExist(egoOpts.waitTime);
          browser.pause(3000)
        }
      });
    }
  })
});

