var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Multiple Select', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO_MS");

        // TODO: validate welcome page
        IwPage.nextButton.waitForExist(browser.options.egoweb.waitTime);
        IwPage.nextButton.click();

        // enter ego id
        let id = IwPage.inputField(1);
        id.waitForExist(browser.options.egoweb.waitTime);
        id.setValue(IwPage.ewid);
        IwPage.nextButton.click();

        // wait for first question
        let firsttitle = IwPage.specificQuestionTitle("first");
        firsttitle.waitForExist(browser.options.egoweb.waitTime);

        // set valid field values for moving forward through survey
        IwPage.fieldValues = {
            'ms0to5of5' : {
                type: 'ms',
                options: {
                    '1515_0' : true,
                    '1515_1' : false,
                    '1515_2' : false,
                    '1515_3' : false,
                    '1515_4' : false
                }
            }
        };

        // go forward to msstart - first question for ms tests
        IwPage.goForwardToQuestion("msstart");
    });

    beforeEach(function () {
        // every test starts at msstart
        IwPage.goBackToQuestion("msstart");
    });

    it("should select option when label is clicked", function () {
        IwPage.goForwardToQuestion('ms0to5of5');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

        // select Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(true);

        // unselect Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(false);
    });

    it("should allow 0 to 5 selections for a question with 5 options", function() {
        IwPage.goForwardToQuestion('ms0to5of5');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

        // no options selected
        expect(browser.element("input#multiselect-1515_0").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_2").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_3").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_4").isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // no options selected
        expect(browser.element("input#multiselect-1515_0").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_2").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_3").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_4").isSelected()).toBe(false);
        // select 1st option
        IwPage.selectOption('1515_0');
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // 1 option selected
        expect(browser.element("input#multiselect-1515_0").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_2").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_3").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_4").isSelected()).toBe(false);
        // select 2 more options
        IwPage.selectOption('1515_3');
        IwPage.selectOption('1515_4');
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // 3 options selected
        expect(browser.element("input#multiselect-1515_0").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_2").isSelected()).toBe(false);
        expect(browser.element("input#multiselect-1515_3").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_4").isSelected()).toBe(true);
        // select 2 more options
        IwPage.selectOption('1515_1');
        IwPage.selectOption('1515_2');
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("num");
        IwPage.back();

        // all options selected
        expect(browser.element("input#multiselect-1515_0").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_2").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_3").isSelected()).toBe(true);
        expect(browser.element("input#multiselect-1515_4").isSelected()).toBe(true);
    });
});
