const IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");

describe('Finish Interview', function () {
    before(async function () {
        await IwPage.open();
        await IwPage.inputUsername.setValue(egoOpts.loginAdmin.username)
        await IwPage.inputPassword.setValue(egoOpts.loginAdmin.password)
        await IwPage.login();
    
        // start test1 interview
        await IwPage.openInterview("TEST_STUDY");
    });

    it("should finish the interview", async function () {
        await IwPage.goToQuestion('finish');
        await IwPage.finish();
        await browser.pause(2000);
        var qTitle = await IwPage.questionTitle;
        await expect(await qTitle.getText()).toBe("CONCLUSION");
    });
});