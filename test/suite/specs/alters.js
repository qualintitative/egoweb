var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Alters', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO_ALTER");

        // welcome page
        expect(IwPage.questionTitle.getText()).toBe("INTRODUCTION");
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
            'ALTER_PROMPT' : {
                type: 'alters',
                options: {
                    1 : 'alpha',
                    2 : 'bravo',
                    3 : 'charlie',
                    4 : 'delta',
                    5 : 'echo',
                    6 : 'foxtrot',
                    7 : 'golf',
                    8 : 'hotel',
                    9 : 'india',
                    10: 'juliet',
                    11: 'kilo',
                    12: 'lima',
                    13: 'mike',
                    14: 'november',
                    15: 'oscar'
                }
            }
        };

        // go forward to ALTER_PROMPT - first question for ms tests
        IwPage.goForwardToQuestion("ALTER_PROMPT");
    });

    beforeEach(function () {
        // every test starts at ALTER_PROMPT
        IwPage.goBackToQuestion("ALTER_PROMPT");
    });

    it("should show correct Variable Alter Prompt while adding alters ", function () {
        IwPage.goForwardToQuestion('ALTER_PROMPT');

        // clear any alters that are already entered
        IwPage.removeAllAlters();

        expect(IwPage.getAlterCount()).toBe(0);

        alters = IwPage.fieldValues['ALTER_PROMPT']['options'];
        browser.element("div=Please enter a name, then click the Add button").waitForVisible(browser.options.egoweb.waitTime);

        IwPage.addAlter(alters[1]);
        browser.element("div=Please enter another name, then click the Add button").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.getAlterCount()).toBe(1);

        IwPage.addAlter(alters[2]);
        expect(IwPage.getAlterCount()).toBe(2);
        IwPage.addAlter(alters[3]);
        IwPage.addAlter(alters[4]);
        IwPage.addAlter(alters[5]);
        IwPage.addAlter(alters[6]);
        IwPage.addAlter(alters[7]);
        IwPage.addAlter(alters[8]);
        IwPage.addAlter(alters[9]);
        IwPage.addAlter(alters[10]);
        IwPage.addAlter(alters[11]);
        IwPage.addAlter(alters[12]);
        IwPage.addAlter(alters[13]);
        IwPage.addAlter(alters[14]);
        browser.element("div=Please enter another name, then click the Add button").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.getAlterCount()).toBe(14);

        // add final alter, make sure display updates correctly
        IwPage.addAlter(alters[15]);
        browser.element("div=Please click the Next button").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.getAlterCount()).toBe(15);
        expect(IwPage.alterAddButton.isVisible()).toBe(false);
    });

    it("should add and remove alters", function() {
        IwPage.goForwardToQuestion('ALTER_PROMPT');

        // clear any alters that are already entered
        IwPage.removeAllAlters();

        alters = IwPage.fieldValues['ALTER_PROMPT']['options'];

        // add some alters
        IwPage.addAlter(alters[1]);
        IwPage.addAlter(alters[2]);
        IwPage.addAlter(alters[3]);
        IwPage.addAlter(alters[4]);
        expect(IwPage.getAlterCount()).toBe(4);

        // remove 3rd alter
        IwPage.removeNthAlter(3);
        expect(IwPage.getAlterCount()).toBe(3);
        expect(browser.isExisting("td="+alters[1])).toBe(true);
        expect(browser.isExisting("td="+alters[2])).toBe(true);
        expect(browser.isExisting("td="+alters[3])).toBe(false);
        expect(browser.isExisting("td="+alters[4])).toBe(true);

    });

});
