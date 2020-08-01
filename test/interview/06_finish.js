var IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");
const { Browser } = require('selenium-webdriver');

describe('Finish Interview', function () {
    before(function () {
        // login
        IwPage.login(egoOpts.loginInterviewer.username, egoOpts.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_STUDY");

    });

    it("should finish the interview", function () {
        IwPage.goToQuestion('finish');
        IwPage.finish();
        browser.pause(2000);
        expect(IwPage.questionTitle.getText()).toBe("CONCLUSION");
    });
});