const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Delete Questions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
  });
  describe('Delete regular question', function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Delete', async function () {
      let btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      await expect(btnNewQ).toBeExisting();
      let qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await $('//*[@id="form-' + qId + '"]').$('button=Delete').click();
    });
    it('check to see if deleted', async function () {
      await browser.url(browserUrl)
      let btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      assert.strictEqual(await btnNewQ.isExisting(), false);
      await browser.pause(5000);
    });
    it('Delete more', async function () {
      let btnNewQ = await $("//button[contains(text(),'" + studyTest.questions[2].title + "')]")
      await expect(btnNewQ).toBeExisting();
      let qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await browser.pause(5000);

      console.log("interact " + qId);
      btnDeleteQ = await $('//*[@id="form-' + qId + '"]').$('button=Delete');
      await btnNewQ.scrollIntoView();
      assert.strictEqual(await btnDeleteQ.isExisting(), true);
      await btnDeleteQ.click();
    });
  });
  describe('Delete Ego ID question', function () {
    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.egoIdLink.getAttribute("href");
      await browser.url(browserUrl);
    });
    it('Delete', async function () {
      let btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      await expect(btnNewQ).toBeExisting();
      let qId = await btnNewQ.getAttribute("aria-controls");
      qId = qId.replace("accordion-","");
      await btnNewQ.click();
      await $('//*[@id="form-' + qId + '"]').$('button=Delete').click();
    });
    it('check to see if dupicate exists', async function () {
      await browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      assert.strictEqual(await btnNewQ.isExisting(), false);
    });
  });
});

