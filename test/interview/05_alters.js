const IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");

describe('Alters', function() {
  before(async function() {
    // login
    await IwPage.open();
    await IwPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await IwPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await IwPage.login();

    // start test1 interview
    await IwPage.openInterview("TEST_STUDY", "ALTER_PROMPT");

    // set valid field values for moving forward through survey
    IwPage.fieldValues = {
      'ALTER_PROMPT': {
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

  beforeEach(async function() {
    // every test starts at NAME_GENERATOR
    //await IwPage.goToQuestion("ALTER_PROMPT");
  });

  it("should show correct Variable Alter Prompt while adding alters ", async function() {
    // clear any alters that are already entered
    await IwPage.removeAllAlters();
    await expect(await IwPage.getAlterCount()).toBe(0);

    var alters = IwPage.fieldValues['ALTER_PROMPT']['values'];
    await $("div=Please enter a name, then click the Add button").waitForExist(egoOpts.waitTime);

    await IwPage.addAlter(alters[0]);
    await $("div=Please enter another name, then click the Add button").waitForExist(egoOpts.waitTime);
    await expect(await IwPage.getAlterCount()).toBe(1);

    await IwPage.addAlter(alters[1]);
    await expect(await IwPage.getAlterCount()).toBe(2);
    await IwPage.addAlter(alters[2]);
    await IwPage.addAlter(alters[3]);
    await IwPage.addAlter(alters[4]);
    await IwPage.addAlter(alters[5]);
    await IwPage.addAlter(alters[6]);
    await IwPage.addAlter(alters[7]);
    await IwPage.addAlter(alters[8]);
    await IwPage.addAlter(alters[9]);
    await IwPage.addAlter(alters[10]);
    await IwPage.addAlter(alters[11]);
    await IwPage.addAlter(alters[12]);
    await IwPage.addAlter(alters[13]);
    await $("div=Please enter another name, then click the Add button").waitForExist(egoOpts.waitTime);
    await expect(await IwPage.getAlterCount()).toBe(14);

    // add final alter, make sure display updates correctly
    await IwPage.addAlter(alters[14]);
    await $("div=Please click the Next button").waitForExist(egoOpts.waitTime);
    await expect(await IwPage.getAlterCount()).toBe(15);
    var addButon = await IwPage.alterAddButton;
    await expect(await addButon.isDisplayed()).toBe(false);
    await IwPage.updateNavLinks();
  });

  it("should add and remove alters", async function() {
    // clear any alters that are already entered
    await IwPage.removeAllAlters();

    var alters = IwPage.fieldValues['ALTER_PROMPT']['values'];

    // add some alters
    await IwPage.addAlter(alters[0]);
    await IwPage.addAlter(alters[1]);
    await IwPage.addAlter(alters[2]);
    await IwPage.addAlter(alters[3]);
    await expect(await IwPage.getAlterCount()).toBe(4);

    // remove 3rd alter
    await IwPage.removeNthAlter(3);
    await expect(await IwPage.getAlterCount()).toBe(3);
    await expect(await $("td=" + alters[0]).isExisting()).toBe(true);
    await expect(await $("td=" + alters[1]).isExisting()).toBe(true);
    await expect(await $("td=" + alters[2]).isExisting()).toBe(false);
    await expect(await $("td=" + alters[3]).isExisting()).toBe(true);
    await IwPage.removeNthAlter(3);
    for (i = 2; i < alters.length; i++) {
      await IwPage.addAlter(alters[i]);
    }

  });

  it("should show table for alter questions with 1 row per alter", async function() {
    await IwPage.goToQuestion('alter1');
    //expect(IwPage.questionTitle.getText()).toBe("alter1");

    var alters = IwPage.fieldValues['ALTER_PROMPT']['values'];

    // clear all data in the table, using "Set All" checkboxes at bottom
    for (i = 2; i <= 6; i++) {
      let opt1 = await IwPage.getTableCellInputElement(16, i);
      if (await opt1.isSelected() == false) {
        // if Set All is off, turn it on to select the entire column
        await browser.execute(function() {
          $("#answerForm #qTable tbody").children()[15].scrollIntoView()
        });

        await opt1.click();
        //await IwPage.pause();
      }
      // turn Set All to off, to unselect the entire column
      opt1 = await IwPage.getTableCellInputElement(16, i);
      await opt1.click();
      await browser.pause();
    }

    // check that table has 15 rows, one per alter. Skip header/SetAll rows
    for (i = 0; i < 15; i++) {
      // check that 1st col has alter name
      await expect(await $(IwPage.getTableCellSelector(i + 1, 1)).getText()).toBe(alters[i]);
    }

    await expect(await IwPage.getTableHeaderText(2)).toHaveTextContaining("Option 1");
    await expect(await IwPage.getTableHeaderText(3)).toHaveTextContaining("Option 2");
    await expect(await IwPage.getTableHeaderText(4)).toHaveTextContaining("Option 3");
    await expect(await IwPage.getTableHeaderText(5)).toHaveTextContaining("Don't Know");
    await expect(await IwPage.getTableHeaderText(6)).toHaveTextContaining("Refuse");

    // click next, even though no cell is selected
    await IwPage.next();
    // should stay on same page with error message
    await expect(await IwPage.questionTitle.getText()).toBe("alter1");

    var alert = await $("div.alert");
    await expect(await alert.getText()).toBe("Select 1 response for each row please." + IwPage.clickError);

    // check that all rows are highlighted
    for (i = 0; i < 15; i++) {
      await expect(await IwPage.getTableRowHighlight(i + 1)).toBe(true);
    }

    // select answers in some rows

    //   browser.scroll(0, 0);

    for (i = 2; i < 6; i++) {
      //browser.scroll(0, (i-2)*56);
      //IwPage.pause();
      //console.log(IwPage.getTableCellInputElement((i-1)*2,i))
      await IwPage.getTableCellInputElement((i - 1) * 2, i).scrollIntoView(false)
      await IwPage.getTableCellInputElement((i - 1) * 2, i).click();
      await expect(await IwPage.getTableRowHighlight((i - 1) * 2)).toBe(false);
    }

    // click next, even though no cell is selected
    //IwPage.next();

    // should stay on same page with error message
    await expect(await IwPage.questionTitle.getText()).toBe("alter1");
    await expect(await alert.getText()).toBe("Select 1 response for each row please." + IwPage.clickError);

    // click a "Set All" button
    await IwPage.getTableCellInputElement(16, 2).click();

    // check that no rows are highlighted
    for (i = 0; i < 15; i++) {
      await expect(await IwPage.getTableRowHighlight(i + 2)).toBe(false);
    }

    // click next and test skip logic
    await IwPage.next();
    await browser.pause(10000);
    await expect(await IwPage.questionTitle.getText()).toBe("alter2");
    await expect(await $(IwPage.getTableCellSelector(1, 1)).getText()).toBe("delta");

    // change option for charlie, test skip logic
    await IwPage.back();
    await expect(await IwPage.questionTitle.getText()).toBe("alter1");
    //browser.scroll(0, 0);
    await IwPage.getTableCellInputElement(4, 2).scrollIntoView(false)
    await IwPage.getTableCellInputElement(4, 2).click();
    //browser.scroll(0, 9999);
    await IwPage.next();
    await browser.pause(2000);
    await expect(await IwPage.questionTitle.getText()).toBe("alterpair1 - alpha");

    // go back to alter1
    await IwPage.back();
    await expect(await IwPage.questionTitle.getText()).toBe("alter1");

  });

  it("should be able to cycle through alter pair pages and create network graph", async function() {
    await IwPage.goToQuestion('alterpair1 - alpha');
    await expect(await IwPage.questionTitle.getText()).toBe("alterpair1 - alpha");

    var alter_pair_pages = 0;
    var alters = IwPage.fieldValues['ALTER_PROMPT']['values'];

    for (k in IwPage.navLinks) {
      if (k.match("alterpair1")) {
        alter_pair_pages++;
      }
    }

    await expect(alter_pair_pages).toBe(IwPage.fieldValues.ALTER_PROMPT.values.length - 1);

    var edges = 0;
    // iterates through alter pair questions and fills them out randomly
    for (i = 0; i < alter_pair_pages; i++) {
      //browser.scroll(0,0);
      var qTitle = await IwPage.questionTitle;
      await $("a#questionTitle=alterpair1 - " + alters[i]).waitForExist(egoOpts.waitTime);
      for (j = 1; j < 15 - i; j++) {
        
        //browser.scroll(0, (j-2)*41);
        //await browser.pause(500);
        let x = Math.floor(Math.random() * (4 - 2 + 1) + 2);
        var header = await IwPage.getTableCellInputElement(j, x);
        await header.scrollIntoView(false);
        if (x == 2)
          edges++;
        await IwPage.getTableCellInputElement(j, x).click();
      }
      await IwPage.next();
    }

    //see if graph has right number of nodes
    await browser.pause(5000);
    let result = await browser.execute(function() {
      return s[0].graph.nodes().length;
    })
    await expect(result).toBe(15);

    //see if graph has right number of edges
    let result2 = await browser.execute(function() {
      return s[0].graph.edges().length;
    })
    await expect(result2).toBe(edges);
  });

});