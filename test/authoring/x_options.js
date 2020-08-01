const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Create Optoins', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('create options', function () {
    it('Go to question option page', function () {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//*[@id="content"]//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
    });
    it('Create options', function () {
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[1].title + "')]")
      var qId = btnNewQ.$('..').getAttribute("id");
      expect(btnNewQ).toBeExisting();
      btnNewQ.$('.optionLink').click();
      browser.pause(1000);
      for(let i = 0; i < studyTest.questions[1].options.length; i++){
        $('//*[@id="' + qId + '"]').$('#QuestionOption_name').setValue(studyTest.questions[1].options[i].name);
        $('//*[@id="' + qId + '"]').$('#QuestionOption_value').setValue(studyTest.questions[1].options[i].value)
        $('//*[@id="' + qId + '"]').$('input[value="Add Option"]').click();
      }
    });
    it('check to see options are saved', function () {
      browser.url(browserUrl);
      btnNewQ = $("//h3[contains(text(),'" + studyTest.questions[1].title + "')]")
      var qId = btnNewQ.$('..').getAttribute("id");
      expect(btnNewQ).toBeExisting();
      btnNewQ.$('.optionLink').click();
      browser.pause(1000);
      for(let i = 0; i < studyTest.questions[1].options.length; i++){
        expect($('//*[@id="' + qId + '"]').$('//label[text()="' + studyTest.questions[1].options[i].name + '"]')).toBeExisting();
      }
    });;
  })
});

