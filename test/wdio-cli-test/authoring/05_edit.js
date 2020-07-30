const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../../.env");

describe('Edit Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Edit questions', function () {
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
    it('Edit 1', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[1].title);
      AuthoringPage.updateNoteField("#prompt" + qId, studyTest.questions[1].prompt);
      $('//*[@id="' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[1].answerType)
      $('//*[@id="' + qId + '"]').$('input[value="Save"]').scrollIntoView(false);
      $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
    });
    it('check to see if changes are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[1].title + "')]");
      expect(btnNewQ).toBeExisting();
    });
    it('Duplicate', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[1].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').scrollIntoView(false);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[1].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
    });
    it('Edit 2', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[1].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[2].title);
      AuthoringPage.updateNoteField("#prompt" + qId, studyTest.questions[2].prompt);
      $('//*[@id="' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[2].answerType)
      $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
    });
    it('check to see if changes are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[2].title + "')]");
      expect(btnNewQ).toBeExisting();
    });
  });
  describe('Edit Ego ID question', function () {
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
    it('Edit Ego ID', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.idQuestions[1].title);
      AuthoringPage.updateNoteField("#prompt" + qId, studyTest.idQuestions[1].prompt);
      $('//*[@id="' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.idQuestions[1].answerType)
      $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
    });
    it('check to see if changes are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[1].title + "')]")
      expect(btnNewQ).toBeExisting();
    });
  });
});

