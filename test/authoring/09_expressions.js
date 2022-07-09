const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Expressions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
    browserUrl = await AuthoringPage.expressionsLink.getAttribute("href");
    await browser.url(browserUrl);
  });
  
  describe('create', function () {
    var i = -1;
    for (index = 0; index < studyTest.expressions.length; index++) {

      it('expression ' + studyTest.expressions[index].name, async function () {
        await browser.url(browserUrl + "#/0")

        i++;
        var expressionType = "Simple";

        if (studyTest.expressions[i].type != "Number" && studyTest.expressions[i].type != "Selection" && studyTest.expressions[i].type != "Text")
          expressionType = studyTest.expressions[i].type;
        await AuthoringPage.expressionSelect.selectByVisibleText(expressionType);
        //AuthoringPage.expressionNew.click()
        await browser.pause(2000);
        console.log(studyTest.expressions[i].name + ":" + studyTest.expressions[i].question)
        if (expressionType == "Simple")
          await AuthoringPage.expressionQuestion.selectByVisibleText(studyTest.expressions[i].question);

       //   AuthoringPage.expressionQuestion.selectByVisibleText(studyTest.settings.title + ":" + studyTest.expressions[i].question);
        await browser.pause(500);
        await AuthoringPage.expressionName.scrollIntoView(false)
        await AuthoringPage.expressionName.setValue(studyTest.expressions[i].name);
        await AuthoringPage.expressionOperator.selectByVisibleText(studyTest.expressions[i].operator);
        if (studyTest.expressions[i].type == "Number") {
          await AuthoringPage.expressionValue.setValue(studyTest.expressions[i].value);
        } else if (studyTest.expressions[i].type == "Selection") {
          if (studyTest.expressions[i].value.match(",") == -1)
            var options = [studyTest.expressions[i].value];
          else
            var options = studyTest.expressions[i].value.split(",");
          if (options.length > 0) {
            for (let j = 0; j < options.length; j++) {
              var optionInput = await $('//span[contains(text(),"' + options[j] + '")]');
              await optionInput.click();
            }
          }
        } else if (studyTest.expressions[i].type == "Counting") {
          let parts = studyTest.expressions[i].value.split(":");
          let times = parts[0];
          await AuthoringPage.expressionTimes.setValue(times);
          let expressions = [];
          let questions = [];
          //console.log(parts);
          if (parts[1] != "") {
            if (parts[1].match(",") != -1) {
              expressions = parts[1].split(",");
            } else {
              expressions = [parts[1]];
            }
          }
          if (parts[2] != "") {
            if (parts[2].match(",") != -1) {
              questions = parts[2].split(",");
            } else {
              questions = [parts[2]];
            }
          }
          if (expressions.length > 0) {
            for (let j = 0; j < expressions.length; j++) {
              let optionInput = await $('span=' + expressions[j]);
              await optionInput.click();
            }
          }
          if (questions.length > 0) {
            for (let j = 0; j < questions.length; j++) {
              let optionInput = await $('span=' + questions[j]);
              await optionInput.click();
            }
          }
        } else if (studyTest.expressions[i].type == "Comparison") {
          let parts = studyTest.expressions[i].value.split(":");
          let compare = parts[0];
          let expression = parts[1];
          await AuthoringPage.expressionCompare.setValue(compare);
          await AuthoringPage.expressionId.selectByVisibleText(expression);
        } else if (studyTest.expressions[i].type == "Compound") {
          let expressions = [];
          if (studyTest.expressions[i].value.match(",") != -1) {
            expressions = studyTest.expressions[i].value.split(",");
          } else {
            expressions = [studyTest.expressions[i].value];
          }
          for (let j = 0; j < expressions.length; j++) {
            let optionInput = await $('//span[contains(text(),"' + expressions[j] + '")]');
            await optionInput.click();
          }
        }
        if (expressionType == "Simple")
          await AuthoringPage.expressionUnanswered.selectByVisibleText(studyTest.expressions[i].resultForUnanswered);
        await AuthoringPage.expressionSave.click()
      });
    }
  });

  describe('update', function () {
    it('go back to questions', async function () {
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    var i = -1;
    for (index = 0; index < studyTest.questions.length; index++) {
      it('update expressions for ' + studyTest.questions[index].title, async function () {
        i++;
        if (studyTest.questions[i].answerReasonExpressionId != "") {
          btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = await btnNewQ.getAttribute("aria-controls");
          qId = qId.replace("accordion-","");
          await btnNewQ.click();
          await browser.pause(500);
          //$('//*[@id="' + qId + '_answerReasonExpressionId"]').waitForExist(egoOpts.waitTime);
          await $('//*[@id="' + qId + '_answerReasonExpressionId"]').selectByVisibleText(studyTest.questions[i].answerReasonExpressionId);
          await $('//*[@id="form-' + qId + '"]').$('button=Save').click();
          await browser.pause(1000);
        }
        if (typeof studyTest.questions[i].params.networkRelationshipExprId != "undefined") {
          btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = await btnNewQ.getAttribute("aria-controls");
          qId = qId.replace("accordion-","");
          await btnNewQ.click();
          await browser.pause(500);
          await $('//*[@id="form-' + qId + '"]').$('//*[@id="Question_networkRelationshipExprId"]').selectByVisibleText(studyTest.questions[i].params.networkRelationshipExprId);
          await $('//*[@id="form-' + qId + '"]').$('button=Save').click();
          await browser.pause(1000);
        }

      });

    }
   
  });
 
});

