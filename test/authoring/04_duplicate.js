const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Duplicate Questions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
  });
  describe('Duplicate question', function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Duplicate', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "')]");
      qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      //browser.pause(1000);
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').click();
    });
    it('check to see if dupicate exists', async function () {
      browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY')]");
      //await expect(btnNewQ).toBeExisting();
    });
  });
  
  describe('Duplicate question again', function () {
    it('Duplicate question', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY')]")
      //expect(btnNewQ).toBeExisting();
      qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      //browser.pause(1000);
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').scrollIntoView(false);
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').click();
    });
    it('check to see if dupicate exists', async function () {
      browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      await expect(btnNewQ).toBeExisting();
    });
  });
  describe('Duplicate Ego ID question', function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.egoIdLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Duplicate', async function () {
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      await expect(btnNewQ).toBeExisting();
      qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      //browser.pause(1000);
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').scrollIntoView(false);
      await $('//*[@id="form-' + qId + '"]').$('button=Duplicate').click();
    });
    it('check to see if dupicate exists', async function () {
      browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      await expect(btnNewQ).toBeExisting();
    });
  });
});

