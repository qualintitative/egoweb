var LoginPage = require('../pageobjects/login.page');

describe('login', function () {
    it('should show error for invalid login', function() {
        LoginPage.open();
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password + "XXX");
        LoginPage.errorMessage.waitForExist(browser.options.egoweb.waitTime);
    });

    it('should succeed for valid login', function() {
        LoginPage.open();
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);
        LoginPage.logoutLink.waitForExist(browser.options.egoweb.waitTime);
    });

    it('should login and logout', function() {
        // login
        LoginPage.open();
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // logout
        let logoutlink = LoginPage.logoutLink;
        logoutlink.waitForExist(browser.options.egoweb.waitTime);
        LoginPage.logout();

        // verify that "login" link is displayed after logging out
        LoginPage.loginLink.waitForExist(browser.options.egoweb.waitTime);

        // TODO: check cookie?
    });
});
