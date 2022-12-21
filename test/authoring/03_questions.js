const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Regular Questions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
  });
  describe('create text question', function () {
    it('Go to question list page', async function () {      
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Create textual_1', async function () {
      var qId = '0';
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "')]")
      if(typeof btnNewQ.error != "undefined"){
        btnNewQ = $('button=Create New Question');
      } else {
        qId = await btnNewQ.getAttribute("aria-controls");
        qId = qId.replace("accordion-","");
      }
      await expect(btnNewQ).toBeExisting();
      await btnNewQ.click();
      //$("//*[@id='" + qId + "_title']").waitForExist(egoOpts.waitTime);
      await $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[0].title);
      await AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[0].prompt);
      await $('//*[@id="form-' + qId + '"]').$('[name="Question[subjectType]"]').selectByVisibleText(studyTest.questions[0].questionType)
      await $('//*[@id="form-0"]').$('button=Create').click();
      await $("button=" + studyTest.questions[0].title).waitForExist(egoOpts.waitTime);
    });
    it('check saved changes to textual_1', async function () {
      await AuthoringPage.open(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "')]")
      await expect(btnNewQ).toBeExisting();
    });;
  })
});

