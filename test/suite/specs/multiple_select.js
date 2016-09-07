var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Multiple Select', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO_MS");

        // welcome page
        //expect(IwPage.questionTitle.getText()).toBe("TEST_WDIO_MS");
        IwPage.nextButton.waitForExist(browser.options.egoweb.waitTime);
        IwPage.nextButton.click();

        // enter ego id
        let id = IwPage.inputField();
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
                    1 : true,
                    2 : false,
                    3 : false,
                    4 : false,
                    5 : false
                }
            },
            'ms0to5of5withother' : {
                type: 'ms',
                options: {
                    1 : true,
                    2 : false,
                    3 : false,
                    4 : false,
                    5 : false
                }
            },
            'ms1of2' : {
                type: 'ms',
                options: {
                    1 : true,
                    2 : false,
                }
            },
            'ms1of2dkrf' : {
                type: 'ms',
                options: {
                    1 : true,
                    2 : false,
                    3 : false, // dk
                    4 : false // rf
                }
            },
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
        let selector = IwPage.getOptionSelector(2);
        expect(browser.element(selector).isSelected()).toBe(true);

        // unselect Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        expect(browser.element(selector).isSelected()).toBe(false);
    });

    it("should allow 0 to 5 selections for a question with 5 options", function() {
        IwPage.goForwardToQuestion('ms0to5of5');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);

        // select 1st option
        IwPage.selectOption(1);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // 1 option selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        // select 2 more options
        IwPage.selectOption(4);
        IwPage.selectOption(5);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // 3 options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
        // select 2 more options
        IwPage.selectOption(2);
        IwPage.selectOption(3);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // all options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
    });

    it("should show/hide text box when option with Other Please Specify is clicked", function() {
        IwPage.goForwardToQuestion('ms0to5of5withother');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5withother.options);

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSpecifySelector(5)).isVisible()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5withother");
        IwPage.back();

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);

        // select 5th option, with specify
        IwPage.selectOption(5);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSpecifySelector(5)).isVisible()).toBe(true);
        browser.element(IwPage.getOptionSpecifySelector(5)).setValue("blah blah");
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5withother");
        IwPage.back();

        // 1 option selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSpecifySelector(5)).getValue()).toBe("blah blah");

        // unselect 5th option
        IwPage.unselectOption(5);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSpecifySelector(5)).isVisible()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5withother");
        IwPage.back();

        // check that 5th option is not set
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSpecifySelector(5)).isVisible()).toBe(false);

    });

    it("should only allow 1 option if min/max selection is 1", function() {
        IwPage.goForwardToQuestion('ms1of2');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2.options);

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        // select 1st option
        IwPage.selectOption(1);
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2");
        IwPage.back();

        // make sure 1st option is still selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        // select 2nd option
        IwPage.selectOption(2);
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2");
        IwPage.back();

        // make sure 2nd option is still selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
    });

    it("should show error if no options are selected and min/max selection is 1", function() {
        IwPage.goForwardToQuestion('ms1of2');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2.options);

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        // click next
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("ms1of2");
        browser.element("div.alert=Select 1 response please.").waitForVisible(browser.options.egoweb.waitTime);

        // fix error
        IwPage.selectOption(1);
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2");
    });

    it("should only allow 1 option if min/max selection is 1, even if dk/rf options are available", function() {
        IwPage.goForwardToQuestion('ms1of2dkrf');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2dkrf.options);

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // select 2nd option
        IwPage.selectOption(2);
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
        IwPage.back();

        // make sure 2nd option is still selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // select dk
        IwPage.selectOption(3);
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
        IwPage.back();

        // make sure dk is still selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // select 1st option, then select rf
        IwPage.selectOption(1);
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        IwPage.selectOption(4);
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
        IwPage.back();

        // make sure rf is still selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
    });

    it("should show error if no options are selected and min/max selection is 1, even if dk/rf options are available", function() {
        IwPage.goForwardToQuestion('ms1of2dkrf');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2dkrf.options);

        // no options selected
        expect(browser.element(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect(browser.element(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // click next
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("ms1of2dkrf");
        browser.element("div.alert=Select 1 response please.").waitForVisible(browser.options.egoweb.waitTime);

        // fix error
        IwPage.selectOption(3);
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
    });




});
