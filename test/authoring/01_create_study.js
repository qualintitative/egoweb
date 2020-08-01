const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Study', function () {

  describe('create super admin', function () {
    it('create super admin if need to', function () {
      browser.url("/");
      if($("h1=Create Admin User").isExisting()) {
        $("#User_name").setValue(egoOpts.loginAdmin.username)
        $("#User_email").setValue(egoOpts.loginAdmin.username)
        $("#User_password").setValue(egoOpts.loginAdmin.password)
        $("#User_confirm").setValue(egoOpts.loginAdmin.password)
        $("//input[@value='Create']").click();
        expect(AuthoringPage.loggedIn).toBeExisting();
        browser.url("site/logout");
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
        'Authoring');
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      if (typeof studyLink.error != "undefined") {
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
      AuthoringPage.updateNoteField("#Study_egoIdPrompt", studyTest.settings.egoIdPrompt);
      AuthoringPage.btnSaveStudy.click();
    });
    it('changes should be saved', () => {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      expect(studyLink).toBeExisting();
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl)
      expect(AuthoringPage.studyIntro).toHaveTextContaining(
        studyTest.settings.introduction);
      expect(AuthoringPage.studyEgoId).toHaveTextContaining(
        studyTest.settings.egoIdPrompt);
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


