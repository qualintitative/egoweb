const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Regular Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('create text question', function () {
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
    it('Create textual_1', function () {
      var qId = '99999999999';
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "')]")
      if(typeof btnNewQ.error != "undefined")
        btnNewQ = $('//*[@id="ui-id-1"]');
      else 
        qId = btnNewQ.$('..').getAttribute("id");
      expect(btnNewQ).toBeExisting();
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").waitForExist(egoOpts.waitTime);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[0].title);
      AuthoringPage.updateNoteField("#prompt" + qId, studyTest.questions[0].prompt);
      if(qId == '99999999999'){
        $('//*[@id="new"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[0].answerType)
        $('//*[@id="new"]').$('input[value="Create"]').click();
      }else{
        $('//*[@id="' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[0].answerType)
        $('//*[@id="' + qId + '"]').$('input[value="Save"]').click();
      }
    });
    it('check saved changes to textual_1', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
    });;
  })
});

