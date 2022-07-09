const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Ego Id Questions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
    //await AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('create ego id promt', function () {
    it('Go to ego id page', async function () {
      browserUrl = await AuthoringPage.egoIdLink.getAttribute("href");
      await browser.url(browserUrl);
      await AuthoringPage.updateNoteField("#Study_egoIdPrompt", studyTest.settings.egoIdPrompt);
      const saveEgo = await $('//*[@id="saveEgoId"]');
      await expect(saveEgo).toBeExisting();
      await saveEgo.click();
    });
  });
  describe('create ego id qs', function () {
    it('Check Ego Id Prompt', async function () {
      await expect(AuthoringPage.settingsLink).toBeExisting();
      const settingsUrl = await AuthoringPage.settingsLink.getAttribute("href");
      await browser.url(settingsUrl);
     // await browser.pause(500);
     // await expect(AuthoringPage.egoIdLink).toBeExisting();
      const egoIdUrl = await AuthoringPage.egoIdLink.getAttribute("href");
      await browser.url(egoIdUrl);
     // await browser.pause(500);
      await expect(AuthoringPage.studyEgoId).toHaveTextContaining(
        studyTest.settings.egoIdPrompt);
    });
    it('Create Ego Id question', async function () {
      var qId = '0';
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      if(typeof btnNewQ.error != "undefined")
        btnNewQ = await $('button=Create New Question');
      else 
        qId = await btnNewQ.getAttribute("aria-controls");      
      qId = qId.replace("accordion-","");
      await expect(btnNewQ).toBeExisting();
      await btnNewQ.click();
      createButton = await $('//*[@id="form-0"]').$('button=Create');
      await $("//*[@id='" + qId + "_title']").setValue(studyTest.idQuestions[0].title);
      await AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.idQuestions[0].prompt);
      if(qId == '0')
        await $('//*[@id="form-0"]').$('button=Create').click();
      else
        await $('//*[@id="form-' + qId + '"]').$('button=Save').click();    
    });
    it('changes should be saved', async function () {
      await expect(AuthoringPage.egoIdLink).toBeExisting();
      const browserUrl = await AuthoringPage.egoIdLink.getAttribute("href");
      await browser.url(browserUrl);
      btnNewQ = await $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      await expect(btnNewQ).toBeExisting();
    });;
  })
});

