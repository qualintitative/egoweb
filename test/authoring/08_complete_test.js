const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

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
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
  });
  describe('finish questions', function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    var i = 1;
    for (index = 2; index < studyTest.questions.length; index++) {
      it("create " + studyTest.questions[index].title, async function (q) {
        i++;
        var qId = '0';
        var nltVals = ["NLT_LITERAL","NLT_PREVQUES","NLT_NONE"];
        await $("button=" + studyTest.questions[i-1].title).waitForExist(egoOpts.waitTime);

        btnNewQ = await $('button=Create New Question');
        //expect(btnNewQ).toBeExisting();
        console.log(studyTest.questions[i].title)
        await btnNewQ.click();
        browser.pause(1000);

        await $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[i].title);
        await AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[i].prompt);
        await $('//*[@id="form-0"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[i].answerType)
        await $('//*[@id="form-0"]').$('[name="Question[subjectType]"]').selectByVisibleText(studyTest.questions[i].questionType)

        if (typeof studyTest.questions[i].params.dontKnowButton != "undefined"){
          await $('//*[@id="form-0"]').$('//label[@for="' + qId + '_dontKnowButton"]').click()
          browser.pause(1000);
        }
        if (typeof studyTest.questions[i].params.refuseButton != "undefined")
          await $('//*[@id="form-0"]').$('//label[@for="' + qId + '_refuseButton"]').click()
        if (typeof studyTest.questions[i].params.askingStyleList != "undefined")
          await $('//*[@id="form-0"]').$('//label[@for="' + qId + '_askingStyleList"]').click()
        if (typeof studyTest.questions[i].params.allButton != "undefined") 
          await $('//*[@id="form-0"]').$("//label[@for='" + qId + "_allButton']").click();

        if (typeof studyTest.questions[i].params.minLimitType != "undefined")
          await $('//*[@id="form-0"]').$('//label[@for="' + qId + '_minLimitType_' + nltVals.indexOf(studyTest.questions[i].params.minLimitType) + '"]').click()
        if (typeof studyTest.questions[i].params.maxLimitType != "undefined")
          await $('//*[@id="form-0"]').$('//label[@for="' + qId + '_maxLimitType_' + nltVals.indexOf(studyTest.questions[i].params.maxLimitType) + '"]').click()
        if (typeof studyTest.questions[i].params.minLiteral != "undefined")
          await $('//*[@id="form-0"]').$('[name="Question[minLiteral]"]').setValue(studyTest.questions[i].params.minLiteral);
        if (typeof studyTest.questions[i].params.maxLiteral != "undefined")
          await $('//*[@id="form-0"]').$('[name="Question[maxLiteral]"]').setValue(studyTest.questions[i].params.maxLiteral);
        if (typeof studyTest.questions[i].params.minCheckableBoxes != "undefined")
          await $('//*[@id="form-0"]').$('[name="Question[minCheckableBoxes]"]').setValue(studyTest.questions[i].params.minCheckableBoxes);
        if (typeof studyTest.questions[i].params.maxCheckableBoxes != "undefined")
          await $('//*[@id="form-0"]').$('[name="Question[maxCheckableBoxes]"]').setValue(studyTest.questions[i].params.maxCheckableBoxes);



        if (typeof studyTest.questions[i].params.timeUnits != "undefined") {
          var timeUnits = studyTest.questions[i].params.timeUnits;
          if (timeBits(timeUnits, "YEAR"))
            await $('//*[@id="form-0"]').$('//label[contains(text(),"Years")]').click();
          if (timeBits(timeUnits, "MONTH"))
            await $('//*[@id="form-0"]').$('//label[contains(text(),"Months")]').click();
          if (timeBits(timeUnits, "WEEK"))
            await $('//*[@id="form-0"]').$('//label[contains(text(),"Weeks")]').click();
          if (timeBits(timeUnits, "DAY"))
            await $('//label[@for="0-time-DAY"]').click();
          if (timeBits(timeUnits, "HOUR"))
            await $('//label[@for="0-time-HOUR"]').click();
          if (timeBits(timeUnits, "MINUTE"))
            await $('//label[@for="0-time-MINUTE"]').click();
            //browser.pause(500);
        }
        
        if (studyTest.questions[i].options.length > 0) {
          await $('//*[@id="form-0"]').$('button=Create').click();
          await $("button=" + studyTest.questions[i].title).waitForExist(egoOpts.waitTime);
          btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[i].title + "')]")
          let qId = await btnNewQ.getAttribute("aria-controls");
          qId = qId.replace("accordion-","");
          //console.log("third click")
          await btnNewQ.click();
          //console.log("fourth click")
          await btnNewQ.scrollIntoView();
          //await browser.pause(1000);


          for (let j = 0; j < studyTest.questions[i].options.length; j++) {
            //$('//*[@id="form-' + qId + '"]').$('//input[@name="' + qId + '_QuestionOption_name"]').waitForExist(egoOpts.waitTime);
            await $('//*[@id="form-' + qId + '"]').$('//input[@name="' + qId + '_QuestionOption_name"]').setValue(studyTest.questions[i].options[j].name);
            await $('//*[@id="form-' + qId + '"]').$('//input[@name="' + qId + '_QuestionOption_value"]').setValue(studyTest.questions[i].options[j].value)
            if (typeof studyTest.questions[i].options[j].otherSpecify != "undefined" && studyTest.questions[i].options[j].otherSpecify == true)
              await $('//*[@id="form-' + qId + '"]').$('//label[@for="' + qId + '_QuestionOption_otherSpecify"]').click()
            await $('//*[@id="form-' + qId + '"]').$('button=Add').click();
            await browser.pause(1000);
          }

        } else if (studyTest.questions[i].questionType == "NAME_GENERATOR") {
          if (typeof studyTest.questions[i].params.min != "undefined")
            await $('//*[@id="form-0"]').$('//*[@id="0_minLiteral"]').setValue(studyTest.questions[i].params.min);
          if (typeof studyTest.questions[i].params.max != "undefined")
            await $('//*[@id="form-0"]').$('//*[@id="0_maxLiteral"]').setValue(studyTest.questions[i].params.max);
          await $('//*[@id="form-0"]').$('button=Create').click();
          //$("//button[contains(text(),'" + studyTest.questions[i].title + "')]").waitForExist(egoOpts.waitTime);
          await $("button=" + studyTest.questions[i].title).waitForExist(egoOpts.waitTime);

          btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[i].title + "')]")
          let qId = await btnNewQ.getAttribute("aria-controls");
          qId = qId.replace("accordion-","");
          await btnNewQ.click();
          //await browser.pause(1000);
          //await btnNewQ.scrollIntoView();

          if (typeof studyTest.questions[i].params.alterPrompts != "undefined") {
            var alterPrompts = studyTest.questions[i].params.alterPrompts;
            for (a in alterPrompts) {
              await $('//*[@id="form-' + qId + '"]').$('//input[@name="AlterPrompt[afterAltersEntered]"]').setValue(a);
              await $('//*[@id="form-' + qId + '"]').$('//input[@name="AlterPrompt[display]"]').setValue(alterPrompts[a]);
              await $('//*[@id="form-' + qId + '"]').$('button=Add').click()
              await browser.pause(500)
            }
          }
        } else {

          let createBtn = await $('//*[@id="form-0"]').$('button=Create')
          if(i > 2)
          btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[i-3].title + "')]")

         // btnNewQ = await $("//button[contains(text(),'Create New Question')]")
          await btnNewQ.scrollIntoView();
         // await createBtn.scrollIntoView();
          //await browser.pause(1000);
          await createBtn.click();
          //$("//button[contains(text(),'" + studyTest.questions[i].title + "')]").waitForExist(egoOpts.waitTime);
          //await browser.pause(1000)
        }
      });
    }
  })
});

