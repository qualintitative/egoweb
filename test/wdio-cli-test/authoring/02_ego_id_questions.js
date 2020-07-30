const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../../.env");

describe('Create Ego Id Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('create ego id', function () {
    it('Go to ego id page', function () {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//*[@id="content"]//a[text()="Ego ID Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Create Ego Id question', function () {
      var qId = '99999999999';
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      if(typeof btnNewQ.error != "undefined")
        btnNewQ = $('//*[@id="ui-id-1"]');
      else 
        qId = btnNewQ.$('..').getAttribute("id");
      expect(btnNewQ).toBeExisting();
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="new"]').$('input[value="Create"]').waitForExist(egoOpts.waitTime);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.idQuestions[0].title);
      AuthoringPage.updateNoteField("#prompt" + qId, studyTest.idQuestions[0].prompt);
      if(qId == '99999999999')
        $('//*[@id="new"]').$('input[value="Create"]').click();
      else
        $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();    });
    it('changes should be saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
    });;
  })
});

