const IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");

describe('Skip Logic 1', function() {
  before(async function() {
    // login
    await IwPage.open();
    await IwPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await IwPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await IwPage.login();

    // start test1 interview
    await IwPage.openInterview("TEST_STUDY", "skip_start");

    // set valid field values for moving forward through survey
    IwPage.fieldValues = {
      'Simple Skip Source 1': {
        type: 'input',
        value: '5'
      },
      'Simple Skip Source 2': {
        type: 'ms',
        options: {
          1: true,
          2: false,
          3: false,
          4: false,
          5: false
        }
      },
      'base number': {
        type: 'input',
        value: 50
      },
      'compare number': {
        type: 'input',
        value: 50
      },
      'reference number': {
        type: 'input',
        value: 50
      },
    }
  });

  beforeEach(async function() {
    // every test starts at first question in survey
    await IwPage.goToQuestion("skip_start");
  });

  it("should allow question to be shown if previous question condition is met: question should display if previous question is not skipped, don't know, or refused", async function() {
    await IwPage.goToQuestion('Simple Skip Source 1');
    //let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

    // Simple Skip Source 1
    await IwPage.inputField.setValue(4);
    await IwPage.next();

    // Show if Less than or equal to 5
		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("Show if Less than or equal to 5");
    await IwPage.next();

    // Question after skip
		await expect(await qTitle.getText()).toBe("Question after skip");
    await IwPage.back();
    await IwPage.back();

    // Simple Skip Source 1
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
		var iField = await IwPage.inputField;
    await expect(await iField.getValue()).toBe("4");
    await IwPage.inputField.setValue(6);
    await IwPage.next();

    // Show if more than 5
    await expect(await qTitle.getText()).toBe("Show if more than 5");
    await IwPage.next();

    // Question after skip
    await expect(await qTitle.getText()).toBe("Question after skip");


    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.back();

    // Simple Skip Source 1
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(5);
    await IwPage.next();

    // Show if equal to 5
    await expect(await qTitle.getText()).toBe("Show if equal to 5");
    await IwPage.next();

    // Question after skip
    await expect(await qTitle.getText()).toBe("Question after skip");
  });

  it("should allow question to be shown if previous question condition is met: question should display if previous question IS skipped, don't know, or refused", async function() {
    await IwPage.goToQuestion('Simple Skip Source 1');
   // let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

    // Simple Skip Source 1
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.rfLabel.click();
    await IwPage.next();

    // Show if refused
		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("Show if Less than or equal to 5");
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.dkLabel.click();
    await IwPage.next();

    // Show if Don't know
    await expect(await qTitle.getText()).toBe("Show if Less than or equal to 5");
  });

  it("should allow question to be shown if previous question condition is met: one, two or multiple selection response is selected in previous question.", async function() {
    await IwPage.goToQuestion('Simple Skip Source 2');
    //let selector = IwPage.getOptionSelector(2);
    // Simple Skip Source 2
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    // select Option 2 by clicking on label
		var optionLabel = await IwPage.optionLabel('opt2');
		await optionLabel.click();
    await IwPage.next();
    //Show if one option selected
		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("At least one option selected");
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    //show if 2 options selected
		optionLabel = await IwPage.optionLabel('opt4');
		await optionLabel.click();
    //IwPage.optionLabel('opt4').click();
    await IwPage.next();
		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("At least one option selected");
    await IwPage.back();

    //show if multiple options selected
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.unselectAllOptions(IwPage.fieldValues['Simple Skip Source 2'].options);

		opt5 = await IwPage.optionLabel('opt5');
		opt2 = await IwPage.optionLabel('opt2');
		opt1 = await IwPage.optionLabel('opt1');
    await opt5.click();
		await opt2.click();
		await opt1.click();

    await IwPage.next();
    await expect(await qTitle.getText()).toBe("At least one option selected");
  });

  it("should allow question to be shown if previous question condition is met: several multiple selection responses are selected in previous question.", async function() {
    await IwPage.goToQuestion('Simple Skip Source 2');
    //let selector = IwPage.getOptionSelector(2);
    // Simple Skip Source 2
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    // no options selected
    await IwPage.dkLabel.click();
    await IwPage.next();

		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("base number");

    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.unselectAllOptions(IwPage.fieldValues['Simple Skip Source 2'].options);

		opt5 = await IwPage.optionLabel('opt5');
		opt2 = await IwPage.optionLabel('opt2');
		opt3 = await IwPage.optionLabel('opt3');
    await opt5.click();
		await opt2.click();
		await opt3.click();

    await IwPage.next();
    await browser.pause(2000);
    await expect(await qTitle.getText()).toBe("At least one option selected");
    await IwPage.next();
    await browser.pause(1000);
    await expect(await qTitle.getText()).toBe("Several multiple selection responses are selected");
  });

  it("should show question if one numeric question is greater than the other", async function() {
    await IwPage.goForwardToQuestion('base number');
    //let field = IwPage.fieldValues['base number']['field'];

    // set base number
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(75);
    await IwPage.next();

		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("compare number");

    // set compare number
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(25);
    await IwPage.next();

    //base > compare
    await expect(await qTitle.getText()).toBe("base higher than compare");

    //change compare to be higher than base
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await expect(await qTitle.getText()).toBe("compare number");
    await IwPage.inputField.setValue(85);
    await IwPage.next();

    //base < compare
    await expect(await qTitle.getText()).toBe("base is lower than compare");

    //change compare to be equal to base
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await expect(await qTitle.getText()).toBe("compare number");
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    
		await IwPage.inputField.setValue(75);

    await IwPage.next();
    await browser.pause(2000);

    //base = compare
    await expect(await qTitle.getText()).toBe("base is equal to compare");
  });

  it("should allow question to be shown if previous question condition is met: one numeric question is GE/LE a number", async function() {
    await IwPage.goForwardToQuestion('reference number');
    //let field = IwPage.fieldValues['base number']['field'];

    // set reference number
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(50);
    await IwPage.next();
    
		var qTitle = await IwPage.questionTitle;
		await expect(await qTitle.getText()).toBe("base is Less or Equal");
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    await IwPage.next();
    await expect(await qTitle.getText()).toBe("base is More or Equal");
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    await IwPage.next();
    await expect(await qTitle.getText()).toBe("landing");
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(49);


    // check lower
    await IwPage.next();
    await browser.pause(1000);
    await expect(await qTitle.getText()).toBe("base is Less or Equal");
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.next();
    await expect(await qTitle.getText()).toBe("landing");
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.back();
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    // check higher
    await IwPage.inputField.setValue(51);
    await IwPage.next();
		await browser.pause(5000);
    await expect(await qTitle.getText()).toBe("base is More or Equal");
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.next();
    await expect(await qTitle.getText()).toBe("landing");
  });

  it("should show question if one numeric question is greater than the the sum of more than one other question", async function() {
    await IwPage.goToQuestion('Simple Skip Source 1');
    //let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

    // set base number 1
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(5);
    await IwPage.next();

    //set base number 2
    await IwPage.goForwardToQuestion('base number');
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(50);
    await IwPage.next();

    await IwPage.goForwardToQuestion('compare number');
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(50);
    await IwPage.next();
    await browser.pause(5000);

    //50 < 5+50 : negative test
    await IwPage.goForwardToQuestion('landing 4');
    await IwPage.next();

		var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("landing 5");
    await IwPage.goBackToQuestion('compare number');
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue(60);
    await browser.pause(1000);
    await IwPage.next();

    //60 > 5+45 : positive test
    await IwPage.goForwardToQuestion('landing 4');
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.next();
    await browser.pause(2000);
    await expect(await qTitle.getText()).toBe("compare 2");

  });


});