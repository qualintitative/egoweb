const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Study', function () {

  describe('create super admin', function () {
    it('create super admin if need to', function () {
      AuthoringPage.open();
      header = $('//h1[@id="form-header"]');
      if(header.getText() == "Create Admin User") {
        $("#signupform-name").setValue(egoOpts.loginAdmin.username)
        $("#signupform-email").setValue(egoOpts.loginAdmin.username)
        $("#signupform-password").setValue(egoOpts.loginAdmin.password)
        $("button=Create").click();
        expect(AuthoringPage.loggedIn).toBeExisting();
        browser.url("site/logout");
      }else if(header.getText() == "Log In") {
        $("#loginform-username").setValue(egoOpts.loginAdmin.username)
        $("#loginform-password").setValue(egoOpts.loginAdmin.password)
        $("//button[@name='login-button']").click();
      }
    });
  });
  describe('login', function () {
    it('should login with valid credentials', function () {
      AuthoringPage.open();
      AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
      expect(AuthoringPage.loggedIn).toBeExisting();
    });
  });

  describe('create study', function () {
    it('Go to authoring page and create study', function () {
      AuthoringPage.open();
      expect(AuthoringPage.pageTitle).toHaveTextContaining(
        'EgoWeb 2.0');
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]/a[text()="Authoring"]')

      if (!studyLink.isExisting()) {
        expect(AuthoringPage.inputCreate).toBeExisting();
        expect(AuthoringPage.btnCreate).toBeExisting();
        AuthoringPage.createStudy(studyTest.settings.title);
      } else {
        studyUrl = studyLink.getAttribute("href");
        browser.url(studyUrl);
      }
      expect(AuthoringPage.pageTitle).toHaveTextContaining(
        studyTest.settings.title);
      AuthoringPage.updateNoteField("#Study_introduction", studyTest.settings.introduction);
      //AuthoringPage.updateNoteField("#Study_egoIdPrompt", studyTest.settings.egoIdPrompt);
      AuthoringPage.btnSaveStudy.click();
    });
    it('changes should be saved', () => {
      AuthoringPage.open();
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      expect(studyLink).toBeExisting();
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl)
      expect(AuthoringPage.studyIntro).toHaveTextContaining(
        studyTest.settings.introduction);
     // expect(AuthoringPage.studyEgoId).toHaveTextContaining(
       // studyTest.settings.egoIdPrompt);
    });;
  })

});

describe('Array', function () {
  describe('#indexOf()', function () {
    it('should return -1 when the value is not present', function () {
      assert.equal([1, 2, 3].indexOf(4), -1);
    });
  });
});


