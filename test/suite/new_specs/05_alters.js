var IwPage = require('../pageobjects/interview.page');
var LoginPage = require('../pageobjects/login.page');

describe('Alters', function () {
    beforeAll(function () {
        // login
        LoginPage.loginAs(browser.options.egoweb.loginInterviewer.username, browser.options.egoweb.loginInterviewer.password);

        // start test1 interview
        IwPage.openInterview("TEST_WDIO", "ALTER_PROMPT");

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
    });

    beforeEach(function () {
        // every test starts at NAME_GENERATOR
        IwPage.goToQuestion("ALTER_PROMPT");
    });

    it("should show correct Variable Alter Prompt while adding alters ", function () {
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
        IwPage.updateNavLinks();
    });

    it("should add and remove alters", function() {
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
	    IwPage.pause();
        expect(IwPage.getAlterCount()).toBe(3);
        expect(browser.isExisting("td="+alters[0])).toBe(true);
        expect(browser.isExisting("td="+alters[1])).toBe(true);
        expect(browser.isExisting("td="+alters[2])).toBe(false);
        expect(browser.isExisting("td="+alters[3])).toBe(true);
        IwPage.removeNthAlter(3);
        for (i=2;i<alters.length;i++) {
            IwPage.addAlter(alters[i]);
        }

    });

    it("should show table for alter questions with 1 row per alter", function() {
        IwPage.goToQuestion('alter1');
        browser.element("span=alter1").waitForVisible(browser.options.egoweb.waitTime);

        alters = IwPage.fieldValues['ALTER_PROMPT']['values'];

        // clear all data in the table, using "Set All" checkboxes at bottom
        for (i=2; i<=6; i++) {
            let opt1 = IwPage.getTableCellInputElement(17,i);
            if (!(opt1.isSelected())) {
                // if Set All is off, turn it on to select the entire column
                browser.execute(function(){$("#answerForm div.multiBox").children()[16].scrollIntoView()});

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

        browser.scroll(0, 0);
	    browser.execute(function(){unfixHeader()});

	    for (i=2; i<6; i++) {
			browser.scroll(0, (i-2)*56);
		    IwPage.pause();
		    IwPage.getTableCellInputElement((i-1)*2,i).click();
            expect(IwPage.getTableRowHighlight((i-1)*2)).toBe(false);
        }

	    // click next, even though no cell is selected
        IwPage.next();

        // should stay on same page with error message
        expect(IwPage.questionTitle.getText()).toBe("alter1");
        browser.element("div.alert=Select 1 response for each row please.").waitForVisible(browser.options.egoweb.waitTime);

	    // click a "Set All" button
        IwPage.getTableCellInputElement(17,2).click();

	    // check that no rows are highlighted
        for (i=0; i<15; i++) {
            expect(IwPage.getTableRowHighlight(i+2)).toBe(false);
        }

	    // click next and test skip logic
        IwPage.next();
        expect(IwPage.questionTitle.getText()).toBe("alter2");
        expect(browser.element(IwPage.getTableCellSelector(2,1)).getText()).toBe("charlie");

	    // change option for charlie, test skip logic
        IwPage.back();
        expect(IwPage.questionTitle.getText()).toBe("alter1");
        browser.scroll(0, 0);
	    IwPage.getTableCellInputElement(4,2).click();
	    browser.scroll(0, 9999);
        IwPage.next();
        browser.pause(40000);
        expect(IwPage.questionTitle.getText()).toBe("alterpair1 - alpha");

	    // go back to alter1
        IwPage.back();
        expect(IwPage.questionTitle.getText()).toBe("alter1");
	    // check that no rows are highlighted
        for (i=0; i<15; i++) {
	        expect(IwPage.getTableRowHighlight(i+2)).toBe(false);
        }
    });

    it("should be able to cycle through alter pair pages and create network graph", function() {
        IwPage.goToQuestion('alterpair1 - alpha');
        browser.element("span=alterpair1 - alpha").waitForVisible(browser.options.egoweb.waitTime);
        var alter_pair_pages = 0;
        for(k in IwPage.navLinks){
            if(k.match("alterpair1")){
                alter_pair_pages++;
            }
        }

        expect(alter_pair_pages).toBe(IwPage.fieldValues.ALTER_PROMPT.values.length - 1);

        var edges = 0;
        // iterates through alter pair questions and fills them out randomly
        for(i = 0; i < alter_pair_pages; i++){
            //browser.scroll(0,0);
            browser.element("div=Please select 1 response for each row").waitForVisible(browser.options.egoweb.waitTime);
            for (j=2; j<16-i; j++) {
                browser.scroll(0, (j-2)*56);
                IwPage.pause();
                let x = Math.floor(Math.random()*(4-2+1)+2);
                if(x == 2)
                    edges++;
                IwPage.getTableCellInputElement(j,x).click();
            }
            IwPage.next();
        }

        //see if graph has right number of nodes
        browser.pause(5000);
        let result = browser.execute(function() {
            return s.graph.nodes().length;
        })
        expect(result.value).toBe(15);

        //see if graph has right number of edges
        let result2 = browser.execute(function() {
            return s.graph.edges().length;
        })
        expect(result2.value).toBe(edges);
    });

});
