var IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../../.env");
const { Browser } = require('selenium-webdriver');

describe('Multiple Select', function () {
    before(function () {
        // login
        IwPage.login(egoOpts.loginInterviewer.username, egoOpts.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_STUDY", "ms_start");

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

    });

    beforeEach(function () {
        // every test starts at ms_start (commented out due to headless bug)
      //  IwPage.goToQuestion("ms_start");
    });

    it("should select option when label is clicked", function () {
        IwPage.goToQuestion('ms0to5of5');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

        // select Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        let selector = IwPage.getOptionSelector(2);
        expect($(selector).isSelected()).toBe(true);

        // unselect Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        expect($(selector).isSelected()).toBe(false);
    });

    it("should allow 0 to 5 selections for a question with 5 options", function() {
        IwPage.goToQuestion('ms0to5of5');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);

        // select 1st option
        IwPage.selectOption(1);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // 1 option selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        // select 2 more options
        IwPage.selectOption(4);
        IwPage.selectOption(5);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // 3 options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
        // select 2 more options
        IwPage.selectOption(2);
        IwPage.selectOption(3);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5");
        IwPage.back();

        // all options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
    });

    it("should show/hide text box when option with Other Please Specify is clicked", function() {
        IwPage.goToQuestion('ms0to5of5withother');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5withother.options);

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSpecifySelector(5)).isDisplayed()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5withother");
        IwPage.back();

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);

        // select 5th option, with specify
        IwPage.selectOption(5);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSpecifySelector(5)).isDisplayed()).toBe(true);
        $(IwPage.getOptionSpecifySelector(5)).setValue("blah blah");
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5withother");
        IwPage.back();

        // 1 option selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSpecifySelector(5)).getValue()).toBe("blah blah");

        // unselect 5th option
        IwPage.unselectOption(5);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSpecifySelector(5)).isDisplayed()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms0to5of5withother");
        IwPage.back();

        // check that 5th option is not set
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(5)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSpecifySelector(5)).isDisplayed()).toBe(false);

    });

    it("should only allow 1 option if min/max selection is 1", function() {
        IwPage.goToQuestion('ms1of2');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2.options);

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        // select 1st option
        IwPage.selectOption(1);
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2");
        IwPage.back();

        // make sure 1st option is still selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        // select 2nd option
        IwPage.selectOption(2);
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2");
        IwPage.back();

        // make sure 2nd option is still selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
    });

    it("should show error if no options are selected and min/max selection is 1", function() {
        IwPage.goToQuestion('ms1of2');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2.options);

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        // click next
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("ms1of2");
        $("div.alert=Select 1 response please.").waitForExist(egoOpts.waitTime);

        // fix error
        IwPage.selectOption(1);
        IwPage.next();

        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2");
    });

    it("should only allow 1 option if min/max selection is 1, even if dk/rf options are available", function() {
        IwPage.goToQuestion('ms1of2dkrf');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2dkrf.options);

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);

        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // select 2nd option
        IwPage.selectOption(2);
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
        IwPage.back();

        // make sure 2nd option is still selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // select dk
        IwPage.selectOption(3);
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
        IwPage.back();

        // make sure dk is still selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // select 1st option, then select rf
        IwPage.selectOption(1);
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(true);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);
        IwPage.selectOption(4);
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
        IwPage.next();

        // next question
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
        IwPage.back();

        // make sure rf is still selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(true);
    });

    it("should show error if no options are selected and min/max selection is 1, even if dk/rf options are available", function() {
        IwPage.goToQuestion('ms1of2dkrf');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2dkrf.options);

        // no options selected
        expect($(IwPage.getOptionSelector(1)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(2)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(3)).isSelected()).toBe(false);
        expect($(IwPage.getOptionSelector(4)).isSelected()).toBe(false);

        // click next
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("ms1of2dkrf");
        $("div.alert=Select 1 response please.").waitForExist(egoOpts.waitTime);

        // fix error
        IwPage.selectOption(3);
        IwPage.next();

        //browser.pause(1000);
        // next page
        expect(IwPage.questionTitle.getText()).not.toBe("ms1of2dkrf");
    });




});
