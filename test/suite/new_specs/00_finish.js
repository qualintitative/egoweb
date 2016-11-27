var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Finish Interview', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO");

    });

    it("should finish the interview", function () {
        IwPage.goToQuestion('finish');
        IwPage.finish();
        browser.element("span=CONCLUSION").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.questionTitle.getText()).toBe("CONCLUSION");
    });
});