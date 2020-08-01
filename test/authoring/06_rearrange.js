const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Rearrange Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Rearrange', function () {

    it('Go to question list page', function () {
      AuthoringPage.open();
      studyLink = $('//*[@id="content"]//a[text()="' + studyTest.settings.title + '"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//*[@id="content"]//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
      browser.pause(1000);
    });
    it('Rearange', function () {
      btnQ0 = $("//h3[contains(text(),'" + studyTest.questions[0].title + "')]")
      var label0 = btnQ0.getText();
      btnQ1 = $("//h3[contains(text(),'" + studyTest.questions[1].title + "')]")
      btnQ2 = $("//h3[contains(text(),'" + studyTest.questions[2].title + "')]")
      var label2 = btnQ2.getText();
      btnQ2.dragAndDrop(btnQ0);
      btnQ1.dragAndDrop(btnQ0);
      browser.pause(1000);
      browser.url(browserUrl)
      browser.pause(1000);
      place0 = $$("//h3")[1].getText();
      place1 = $$("//h3")[2].getText();
      place2 = $$("//h3")[3].getText();
      assert.equal(place0, label2);
      assert.equal(place2, label0);
  })
});
});