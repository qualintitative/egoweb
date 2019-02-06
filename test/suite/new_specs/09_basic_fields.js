var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Basic Fields', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO", "basic_start");

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
        IwPage.goToQuestion("basic_start");
    });

    it("should handle positive numbers in number field", function () {
        IwPage.goToQuestion('num');
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
        IwPage.goToQuestion('num');
        let field = IwPage.fieldValues['num']['field'];

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        IwPage.inputField().setValue(-7);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // num
        IwPage.inputField().waitForExist(browser.options.egoweb.waitTime);
        expect(IwPage.inputField().getValue()).toBe("-7");

    });

    it("should show error for letters in number field", function() {
        IwPage.goToQuestion('num');
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
        IwPage.goToQuestion('numdkrf');
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
        IwPage.goToQuestion("num0to100");
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
        IwPage.goToQuestion("num0to100");
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

    it("should show error if textual value is blank", function() {
        IwPage.goToQuestion("textual");
        IwPage.inputField().setValue("");
        IwPage.next();
        browser.element("div.alert=Value cannot be blank").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.questionTitle.getText()).toBe("textual");
        IwPage.inputField().setValue("test");
        IwPage.next();
        browser.element("span=date").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.questionTitle.getText()).not.toBe("textual");
    });

    it("should show error when date fields are missing", function() {
        IwPage.goToQuestion("date");
        IwPage.next();
        browser.element("div.alert=Please enter a month").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.monthField().selectByValue('December');
        IwPage.next();
        browser.element("div.alert=Please enter a valid year").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.yearField().setValue("1999");
        IwPage.next();
        browser.element("div.alert=Please enter a day of the month").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.dayField().setValue("31");
        IwPage.next();
        expect(IwPage.questionTitle.getText()).not.toBe("date");
    });

    it("should show error when hour and minute fields are missing", function() {
        IwPage.goToQuestion("hour_min");
        IwPage.next();
        browser.element("div.alert=Please enter the time of day").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.hourField().setValue('23');
        IwPage.minuteField().setValue('60');
        IwPage.next();
        browser.element("div.alert=Please enter the time of day").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.pmField().click();
        IwPage.next();
        browser.element("div.alert=Please enter 0 to 59 for MM").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.minuteField().setValue("59");
        IwPage.next();
        browser.element("div.alert=Please enter 1 to 12 for HH").waitForVisible(browser.options.egoweb.waitTime);
        IwPage.hourField().setValue('11');
        IwPage.next();
        expect(IwPage.questionTitle.getText()).not.toBe("hour_min");
    });

    it("only weeks and days are displayed", function() {
        IwPage.goToQuestion("weeks_days");
        browser.element("label=Weeks").waitForVisible(browser.options.egoweb.waitTime);
        browser.element("label=Days").waitForVisible(browser.options.egoweb.waitTime);
        expect(browser.isExisting('label=Years')).toBe(false);
        expect(browser.isExisting('label=Months')).toBe(false);
        expect(browser.isExisting('label=Hours')).toBe(false);
        expect(browser.isExisting('label=Minutes')).toBe(false);
    });

});
