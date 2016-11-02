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
                values: [
                    'alpha',
                    'bravo',
                    'charlie',
                    'delta',
                    'echo',
                    'foxtrot',
                    'golf',
                    'hotel',
                    'india',
                    'juliet',
                    'kilo',
                    'lima',
                    'mike',
                    'november',
                    'oscar'
                ]
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

        alters = IwPage.fieldValues['ALTER_PROMPT']['values'];
        browser.element("div=Please enter a name, then click the Add button").waitForVisible(browser.options.egoweb.waitTime);

        IwPage.addAlter(alters[0]);
        browser.element("div=Please enter another name, then click the Add button").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.getAlterCount()).toBe(1);

        IwPage.addAlter(alters[1]);
        expect(IwPage.getAlterCount()).toBe(2);
        IwPage.addAlter(alters[2]);
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
        browser.element("div=Please enter another name, then click the Add button").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.getAlterCount()).toBe(14);

        // add final alter, make sure display updates correctly
        IwPage.addAlter(alters[14]);
        browser.element("div=Please click the Next button").waitForVisible(browser.options.egoweb.waitTime);
        expect(IwPage.getAlterCount()).toBe(15);
        expect(IwPage.alterAddButton.isVisible()).toBe(false);
    });

    it("should add and remove alters", function() {
        IwPage.goForwardToQuestion('ALTER_PROMPT');

        // clear any alters that are already entered
        IwPage.removeAllAlters();

        alters = IwPage.fieldValues['ALTER_PROMPT']['values'];

        // add some alters
        IwPage.addAlter(alters[0]);
        IwPage.addAlter(alters[1]);
        IwPage.addAlter(alters[2]);
        IwPage.addAlter(alters[3]);
        expect(IwPage.getAlterCount()).toBe(4);

        // remove 3rd alter
        IwPage.removeNthAlter(3);
        expect(IwPage.getAlterCount()).toBe(3);
        expect(browser.isExisting("td="+alters[0])).toBe(true);
        expect(browser.isExisting("td="+alters[1])).toBe(true);
        expect(browser.isExisting("td="+alters[2])).toBe(false);
        expect(browser.isExisting("td="+alters[3])).toBe(true);

    });

    it("should show table for alter questions with 1 row per alter", function() {
        IwPage.goForwardToQuestion('alter1');

        alters = IwPage.fieldValues['ALTER_PROMPT']['values'];

        // clear all data in the table, using "Set All" checkboxes at bottom
        for (i=2; i<=6; i++) {
            let opt1 = IwPage.getTableCellInputElement(17,i);
            if (!(opt1.isSelected())) {
                // if Set All is off, turn it on to select the entire column
                opt1.click();
                IwPage.pause();
            }
            // turn Set All to off, to unselect the entire column
            opt1 = IwPage.getTableCellInputElement(17,i);
            opt1.click();
            IwPage.pause();
        }

        // check that table has 15 rows, one per alter. Skip header/SetAll rows
        for (i=0; i<15; i++) {
            // check that 1st col has alter name
            expect(browser.element(IwPage.getTableCellSelector(i+2,1)).getText()).toBe(alters[i]);
        }

        // check the table headers
        expect(IwPage.getTableHeaderText(2)).toBe("Option 1");
        expect(IwPage.getTableHeaderText(3)).toBe("Option 2");
        expect(IwPage.getTableHeaderText(4)).toBe("Option 3");
        expect(IwPage.getTableHeaderText(5)).toBe("Don't Know");
        expect(IwPage.getTableHeaderText(6)).toBe("Refuse");

        // click next, even though no cell is selected
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("alter1");
        browser.element("div.alert=Select 1 response for each row please.").waitForVisible(browser.options.egoweb.waitTime);
        // check that all rows are highlighted
        for (i=0; i<15; i++) {
            expect(IwPage.getTableRowHighlight(i+2)).toBe(true);
        }

        // select answers in some rows
        IwPage.getTableCellInputElement(2,2).click();
        IwPage.getTableCellInputElement(4,3).click();
        IwPage.getTableCellInputElement(6,4).click();
        IwPage.getTableCellInputElement(8,5).click();
        IwPage.getTableCellInputElement(10,6).click();

        expect(IwPage.getTableRowHighlight(2)).toBe(false);
        expect(IwPage.getTableRowHighlight(4)).toBe(false);
        expect(IwPage.getTableRowHighlight(6)).toBe(false);
        expect(IwPage.getTableRowHighlight(8)).toBe(false);
        expect(IwPage.getTableRowHighlight(10)).toBe(false);

        // click next, even though no cell is selected
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("alter1");
        browser.element("div.alert=Select 1 response for each row please.").waitForVisible(browser.options.egoweb.waitTime);

        // click a "Set All" button
        IwPage.getTableCellInputElement(17,3).click();

        // check that no rows are highlighted
        for (i=0; i<15; i++) {
            expect(IwPage.getTableRowHighlight(i+2)).toBe(false);
        }

        // click next and validate next page
        IwPage.next();
        expect(IwPage.questionTitle.getText()).not.toBe("alter1");

        // click back and validate alter1 page
        IwPage.back();
        expect(IwPage.questionTitle.getText()).toBe("alter1");
        // check that no rows are highlighted
        for (i=0; i<15; i++) {
            expect(IwPage.getTableRowHighlight(i+2)).toBe(false);
        }
    });
});
