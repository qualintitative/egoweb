var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Multiple Select', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO_BASIC");

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
            'num' : {
                type: 'input',
                field: 2,
                value: '5'
            },
            'numdkrf' : {
                type: 'input',
                field: 6,
                value: '5'
            },
            'num0to100' : {
                type: 'input',
                field: 7,
                value: '5'
            },
            'ms' : {
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
        IwPage.goForwardToQuestion('ms');
        IwPage.unselectAllOptions(IwPage.fieldValues.ms.options);

        // select Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(true);

        // unselect Option 2 by clicking on label
        IwPage.optionLabel('Option 2').click();
        expect(browser.element("input#multiselect-1515_1").isSelected()).toBe(false);
    });

});
