const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../../.env");

describe('Delete Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Delete regular question', function () {
    it('Go to question list page', function () {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//*[@id="content"]//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Delete', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Delete"]').click();
      browser.pause(1000);
    });
    it('check to see if deleted', function () {
      browser.url(browserUrl)
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      assert.equal(btnNewQ.isExisting(), false);
    });
    it('Delete more', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[2].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Delete"]').click();
    });
  });
  describe('Delete Ego ID question', function () {
    it('Go to question list page', function () {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//*[@id="content"]//a[text()="Ego ID Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Delete', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Delete"]').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      assert.equal(btnNewQ.isExisting(), false);
    });
  });
});

