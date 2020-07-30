const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../../.env");

describe('Create Expressions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
    AuthoringPage.open();
    studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
    studyUrl = studyLink.getAttribute("href");
    browser.url(studyUrl);
    idQLink = $('//*[@id="content"]//a[text()="Expressions"]')
    expect(idQLink).toBeExisting();
    browserUrl = idQLink.getAttribute("href");
    browser.url(browserUrl)
  });
  
  describe('create', function () {
    var i = -1;
    for (index = 0; index < studyTest.expressions.length; index++) {
      it('expression ' + studyTest.expressions[index].name, function () {
        i++;
        var expressionType = "Simple";
        if (studyTest.expressions[i].type != "Number" && studyTest.expressions[i].type != "Selection" && studyTest.expressions[i].type != "Text")
          expressionType = studyTest.expressions[i].type;
        AuthoringPage.expressionSelect.selectByVisibleText(expressionType);
        AuthoringPage.expressionNew.click()
        browser.pause(1000);
        if (expressionType == "Simple")
          AuthoringPage.expressionQuestion.selectByVisibleText(studyTest.settings.title + ":" + studyTest.expressions[i].question);
        browser.pause(500);
        AuthoringPage.expressionName.scrollIntoView(false)
        AuthoringPage.expressionName.setValue(studyTest.expressions[i].name);
        AuthoringPage.expressionOperator.selectByVisibleText(studyTest.expressions[i].operator);
        if (studyTest.expressions[i].type == "Number") {
          AuthoringPage.expressionValue.setValue(studyTest.expressions[i].value);
        } else if (studyTest.expressions[i].type == "Selection") {
          if (studyTest.expressions[i].value.match(",") == -1)
            var options = [studyTest.expressions[i].value];
          else
            var options = studyTest.expressions[i].value.split(",");
          if (options.length > 0) {
            for (let j = 0; j < options.length; j++) {
              var optionInput = $('//*[@id="valueList"]//label[text()="' + options[j] + '"]/preceding-sibling::input');
              optionInput.click();
            }
          }
        } else if (studyTest.expressions[i].type == "Counting") {
          let parts = studyTest.expressions[i].value.split(":");
          let times = parts[0];
          AuthoringPage.expressionTimes.setValue(times);
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
              let optionInput = $('//*[@id="expressionList"]//label[text()="' + expressions[j] + '"]/preceding-sibling::input');
              optionInput.click();
            }
          }
          if (questions.length > 0) {
            for (let j = 0; j < questions.length; j++) {
              let optionInput = $('//*[@id="questionList"]//label[text()="' + questions[j] + '"]/preceding-sibling::input');
              optionInput.click();
            }
          }
        } else if (studyTest.expressions[i].type == "Comparison") {
          let parts = studyTest.expressions[i].value.split(":");
          let compare = parts[0];
          let expression = parts[1];
          AuthoringPage.expressionCompare.setValue(compare);
          AuthoringPage.expressionId.selectByVisibleText(expression);
        } else if (studyTest.expressions[i].type == "Compound") {
          let expressions = [];
          if (studyTest.expressions[i].value.match(",") != -1) {
            expressions = studyTest.expressions[i].value.split(",");
          } else {
            expressions = [studyTest.expressions[i].value];
          }
          for (let j = 0; j < expressions.length; j++) {
            let optionInput = $('//*[@id="expressionList"]//label[text()="' + expressions[j] + '"]/preceding-sibling::input');
            optionInput.click();
          }
        }
        if (expressionType == "Simple")
          AuthoringPage.expressionUnanswered.selectByVisibleText(studyTest.expressions[i].resultForUnanswered);
        AuthoringPage.expressionSave.click()
      });
    }
  });

  describe('update', function () {
    it('go back to questions', function () {
      idQLink = $('//*[@id="content"]//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    var i = -1;
    for (index = 0; index < studyTest.questions.length; index++) {
      it('update expressions for ' + studyTest.questions[index].title, function () {
        i++;
        if (studyTest.questions[i].answerReasonExpressionId != "") {
          btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = btnNewQ.$('..').getAttribute("id");
          btnNewQ.click();
          browser.pause(500);
          $('//*[@id="' + qId + '_answerReasonExpressionId"]').waitForExist(egoOpts.waitTime);
          $('//*[@id="' + qId + '_answerReasonExpressionId"]').selectByVisibleText(studyTest.questions[i].answerReasonExpressionId);
          $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
          browser.pause(1000);
        }
        if (typeof studyTest.questions[i].params.networkRelationshipExprId != "undefined") {
          btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[i].title + "')]")
          qId = btnNewQ.$('..').getAttribute("id");
          btnNewQ.click();
          browser.pause(500);
          $('//*[@id="' + qId + '"]').$('//*[@id="Question_networkRelationshipExprId"]').selectByVisibleText(studyTest.questions[i].params.networkRelationshipExprId);
          $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
          browser.pause(1000);
        }

      });

    }
   
  });
 
});

