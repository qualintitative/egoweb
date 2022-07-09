const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Edit Questions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
  });
  describe('Edit questions', async function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Edit 1', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY')]")
      await expect(btnNewQ).toBeExisting();
      qId = await btnNewQ.getAttribute("aria-controls")
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[1].title);
      await AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[1].prompt);
      await $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[1].answerType)
      await $('//*[@id="form-' + qId + '"]').$('button=Save').scrollIntoView(false);
      await $('//*[@id="form-' + qId + '"]').$('button=Save').click();
    });
    it('check to see if changes are saved', async function () {
      browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[1].title + "')]");
      await expect(btnNewQ).toBeExisting();
    });
    it('Duplicate', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[1].title + "')]")
      await expect(btnNewQ).toBeExisting();
      qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').scrollIntoView(false);
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').click();
    });
    it('check to see if dupicate exists', async function () {
      browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[1].title + "_COPY')]")
      await expect(btnNewQ).toBeExisting();
    });
    it('Edit 2', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[1].title + "_COPY')]")
      await expect(btnNewQ).toBeExisting();
      qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[2].title);
      await AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[2].prompt);
      await $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[2].answerType)
      await $('//*[@id="form-' + qId + '"]').$('button=Save').click();
    });
    it('check to see if changes are saved', async function () {
      browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[2].title + "')]");
      await expect(btnNewQ).toBeExisting();
    });
  });
  describe('Edit Ego ID question', function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.egoIdLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Edit Ego ID', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      await expect(btnNewQ).toBeExisting();
      qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await $("//*[@id='" + qId + "_title']").setValue(studyTest.idQuestions[1].title);
      await AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.idQuestions[1].prompt);
      await $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.idQuestions[1].answerType)
      await $('//*[@id="form-' + qId + '"]').$('button=Save').click();
    });
    it('check to see if changes are saved', async function () {
      await browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[1].title + "')]")
      await expect(btnNewQ).toBeExisting();
    });
  });
});

