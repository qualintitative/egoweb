const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Ego Id Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('create ego id', function () {
    it('Go to ego id page', function () {
     // AuthoringPage.open();
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//main//a[text()="Ego ID"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Create Ego Id question', function () {
      var qId = '0';
      btnNewQ = $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      if(typeof btnNewQ.error != "undefined")
        btnNewQ = $('button=Create New Question');
      else 
        qId = btnNewQ.getAttribute("aria-controls").replace("accordion-","");      
      expect(btnNewQ).toBeExisting();
      btnNewQ.click();
      browser.pause(1000);
      $('//*[@id="form-0"]').$('button=Create').waitForExist(egoOpts.waitTime);
      $("//*[@id='" + qId + "_title']").setValue(studyTest.idQuestions[0].title);
      AuthoringPage.updateNoteField("#" + qId + "_prompt", studyTest.idQuestions[0].prompt);
      if(qId == '0')
        $('//*[@id="form-0"]').$('button=Create').click();
      else
        $('//*[@id="form-' + qId + '"]').$('button=Save').click();    
    });
    it('changes should be saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//button[contains(text(),'" + studyTest.idQuestions[0].title + "')]")
      expect(btnNewQ).toBeExisting();
    });;
  })
});

