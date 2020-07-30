const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../../.env");

describe('Duplicate Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Duplicate question', function () {
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
    it('Duplicate', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
    });
  });
  
  describe('Duplicate question again', function () {
    it('Duplicate question', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').scrollIntoView(false);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "_COPY_COPY')]")
      expect(btnNewQ).toBeExisting();
    });
  });
  describe('Duplicate Ego ID question', function () {
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
    it('Duplicate', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
      qId = btnNewQ.$('..').getAttribute("id");
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').scrollIntoView(false);
      $('//*[@id="' + qId + '"]').$('input[value="Duplicate"]').click();
    });
    it('check to see if dupicate exists', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "_COPY')]")
      expect(btnNewQ).toBeExisting();
    });
  });
});

