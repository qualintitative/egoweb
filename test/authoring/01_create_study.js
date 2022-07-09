const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Study', function () {

  describe('create super admin', function () {
    it('create super admin if need to', async function () {
      AuthoringPage.open();
      const header = await $('//h1[@id="form-header"]');
      const headerText = await header.getText();
      if(headerText == "Create Admin User") {
        await $("#signupform-name").setValue(egoOpts.loginAdmin.username)
        await $("#signupform-email").setValue(egoOpts.loginAdmin.username)
        await $("#signupform-password").setValue(egoOpts.loginAdmin.password)
        await $("button=Create").click();
        await expect(AuthoringPage.loggedIn).toBeExisting();
        await browser.url("site/logout");
      }
    });
  });
  
  describe('login', function () {
    it('should login with valid credentials', async function () {
      AuthoringPage.open();
      await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
      await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
      await AuthoringPage.login();
      await expect(AuthoringPage.loggedIn).toBeExisting();
    });
  });

  describe('create study', async function () {
    it('Go to authoring page and create study', async function () {
      await AuthoringPage.open();
      await expect(AuthoringPage.pageTitle).toHaveTextContaining(
        'EgoWeb 2.0');
      const studyLink = await $('//div[@aria-label="' + studyTest.settings.title + '"]/a[text()="Authoring"]')

      if (await studyLink.isExisting() == false) {
        await expect(AuthoringPage.inputCreate).toBeExisting();
        await expect(AuthoringPage.btnCreate).toBeExisting();
        await AuthoringPage.inputCreate.setValue(studyTest.settings.title);
        await AuthoringPage.btnCreate.click();
      } else {
        const studyUrl = await studyLink.getAttribute("href");
        await browser.url(studyUrl);
      }
      await expect(AuthoringPage.pageTitle).toHaveTextContaining(
        studyTest.settings.title);
      await AuthoringPage.updateNoteField("#Study_introduction", studyTest.settings.introduction);
      await AuthoringPage.btnSaveStudy.click();
    });
    it('changes should be saved', async () => {
      await AuthoringPage.open();
      const studyLink = await $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      await expect(studyLink).toBeExisting();
      studyUrl = await studyLink.getAttribute("href");
      await browser.url(studyUrl)
      await expect(AuthoringPage.studyIntro).toHaveTextContaining(
        studyTest.settings.introduction);

    });;
  })

});


