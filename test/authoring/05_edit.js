const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Edit Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Edit questions', function () {
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
    it('Edit 1', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[1].title);
      AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[1].prompt);
      $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[1].answerType)
      $('//*[@id="form-' + qId + '"]').$('button=Save').scrollIntoView(false);
      $('//*[@id="form-' + qId + '"]').$('button=Save').click();
    });
    it('check to see if changes are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[1].title + "')]");
      expect(btnNewQ).toBeExisting();
    });
    it('Duplicate', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[1].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="form-' + qId + '"]').$('button=Duplicate').scrollIntoView(false);
      $('//*[@id="form-' + qId + '"]').$('button=Duplicate').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[1].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
    });
    it('Edit 2', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[1].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[2].title);
      AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[2].prompt);
      $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[2].answerType)
      $('//*[@id="form-' + qId + '"]').$('button=Save').click();
    });
    it('check to see if changes are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[2].title + "')]");
      expect(btnNewQ).toBeExisting();
    });
  });
  describe('Edit Ego ID question', function () {
    it('Go to question list page', function () {
      AuthoringPage.open();
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//main//a[text()="Ego ID"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Edit Ego ID', function () {
      btnNewQ = $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.idQuestions[1].title);
      AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.idQuestions[1].prompt);
      $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.idQuestions[1].answerType)
      $('//*[@id="form-' + qId + '"]').$('button=Save').click();
    });
    it('check to see if changes are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.idQuestions[1].title + "')]")
      expect(btnNewQ).toBeExisting();
    });
  });
});

