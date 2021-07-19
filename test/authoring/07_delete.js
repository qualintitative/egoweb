const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Delete Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Delete regular question', function () {
    it('Go to question list page', function () {
      //AuthoringPage.open();
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//main//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Delete', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="form-' + qId + '"]').$('button=Delete').click();
      browser.pause(1000);
    });
    it('check to see if deleted', function () {
      browser.url(browserUrl)
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      assert.strictEqual(btnNewQ.isExisting(), false);
    });
    it('Delete more', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[2].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="form-' + qId + '"]').$('button=Delete').click();
    });
  });
  describe('Delete Ego ID question', function () {
    it('Go to question list page', function () {
      //AuthoringPage.open();
      //studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      //studyUrl = studyLink.getAttribute("href");
      //browser.url(studyUrl);
      idQLink = $('//main//a[text()="Ego ID"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Delete', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="form-' + qId + '"]').$('button=Delete').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      assert.strictEqual(btnNewQ.isExisting(), false);
    });
  });
});

