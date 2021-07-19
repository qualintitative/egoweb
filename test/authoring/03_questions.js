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
      //AuthoringPage.open();
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//main//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Create textual_1', function () {
      var qId = '0';
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[0].title + "')]")
      if(typeof btnNewQ.error != "undefined")
        btnNewQ = $('button=Create New Question');
      else 
        qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");
      expect(btnNewQ).toBeExisting();
      btnNewQ.click();
      browser.pause(1000);
      $("//*[@id='" + qId + "_title']").waitForExist(egoOpts.waitTime);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.questions[0].title);
      AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.questions[0].prompt);
      if(qId == '0'){
        $('//*[@id="form-0"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[0].answerType)
        $('//*[@id="form-0"]').$('button=Create').click();
      }else{
        $('//*[@id="form-' + qId + '"]').$('[name="Question[answerType]"]').selectByVisibleText(studyTest.questions[0].answerType)
        $('//*[@id="form-' + qId + '"]').$('button=Save').click();
      }
    });
    it('check saved changes to textual_1', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.questions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
    });;
  })
});

