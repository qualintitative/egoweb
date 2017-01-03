var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Basic Fields', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO_BASIC");

        // TODO: validate welcome page
        IwPage.nextButton.waitForExist(browser.options.egoweb.waitTime);
        IwPage.nextButton.click();

        // enter ego id
        let id = IwPage.inputField();
        id.waitForExist(browser.options.egoweb.waitTime);
        id.setValue(IwPage.ewid);
        IwPage.nextButton.click();

        // wait for first question
        let firsttitle = IwPage.specificQuestionTitle('first');
        firsttitle.waitForExist(browser.options.egoweb.waitTime);

        // set valid field values for moving forward through survey
        IwPage.fieldValues = {
            'num' : {
                type: 'input',
                value: '5'
            },
            'numdkrf' : {
                type: 'input',
                value: '5'
            },
            'num0to100' : {
                type: 'input',
                value: '5'
            }
        }

    });

    beforeEach(function () {
        // every test starts at first question in survey
        IwPage.goBackToQuestion("first");
    });

    it("should handle positive numbers in number field", function () {
        IwPage.goForwardToQuestion('num');
        let field = IwPage.fieldValues['num']['field'];

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        IwPage.inputField().setValue(55);
        IwPage.next();

        // numdkrf
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        expect(IwPage.inputField().getValue()).toBe("55");
    });

    it("should handle negative numbers in number field", function () {
        IwPage.goForwardToQuestion('num');
        let field = IwPage.fieldValues['num']['field'];

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        IwPage.inputField().setValue(-4);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        expect(IwPage.inputField().getValue()).toBe("-4");

    });

    it("should show error for letters in number field", function() {
        IwPage.goForwardToQuestion('num');
        let field = IwPage.fieldValues['num']['field'];

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        IwPage.inputField().setValue("abc");
        IwPage.next();

        // error message
        browser.element("div.alert=Please enter a number").waitForVisible(browser.options.egoweb.waitTime);

        // fix error
        IwPage.inputField().setValue("5");
        IwPage.next();

        // numdkrf
        expect(IwPage.questionTitle.getText()).not.toBe("num");
    });

    it("should handle DK and RF with number field", function() {
        IwPage.goForwardToQuestion('numdkrf');
        let field = IwPage.fieldValues['numdkrf']['field'];

        // numdkrf
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);

        IwPage.inputField().setValue("99");

        // dk should clear value
        IwPage.dkLabel.click();
        expect(IwPage.inputField().getValue()).toBe("");
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("numdkrf");
        IwPage.back();

        IwPage.inputField().setValue("44");

        //rf should clear value
        IwPage.rfLabel.click();
        expect(IwPage.inputField().getValue()).toBe("");
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("numdkrf");
    });

    it("should succeed if number value is within min/max range", function() {
        IwPage.goForwardToQuestion("num0to100");
        let field = IwPage.fieldValues['num0to100']['field'];

        // num0to100
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);

        // try min value
        IwPage.inputField().setValue("0");
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("num0to100");
        IwPage.back();

        // try max value
        IwPage.inputField().setValue("100");
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("num0to100");
        IwPage.back();

        // try a value inside range
        IwPage.inputField().setValue("73");
        IwPage.next();

        expect(IwPage.questionTitle.getText()).not.toBe("num0to100");
    });

    it("should show error if number value is outside min/max range", function() {
        IwPage.goForwardToQuestion("num0to100");
        let field = IwPage.fieldValues['num0to100']['field'];

        // num0to100
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);

        // try value below min
        IwPage.inputField().setValue("-1");
        IwPage.next();

        // error message
        browser.element("div.alert=The range of valid answers is 0 to 100.").waitForVisible(browser.options.egoweb.waitTime);

        // fix value
        IwPage.inputField().setValue("0");
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("num0to100");
        IwPage.back();

        // num0to100
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);

        // try value below min
        IwPage.inputField().setValue("101");
        IwPage.next();

        // error message
        browser.element("div.alert=The range of valid answers is 0 to 100.").waitForVisible(browser.options.egoweb.waitTime);

        // fix value
        IwPage.inputField().setValue("100");
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("num0to100");
    });
});
