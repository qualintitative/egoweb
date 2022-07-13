const IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");

describe('Skip Logic 2', function () {
	before(async function () {
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
				value: 5
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

	beforeEach(function () {
		// every test starts at first question in survey (commented out for headless)
		//	IwPage.goToQuestion("skip_start");
	});

	it("should display question if one or more than one simple expression conditions are true", async function () {
		await IwPage.goToQuestion('Simple Skip Source 1');
		//let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// 2 options are true
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(4);
		await IwPage.next();

		await IwPage.goForwardToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(60);

		await IwPage.next();
		await IwPage.goForwardToQuestion('landing');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();

		var qTitle = await IwPage.questionTitle;
		await expect(await qTitle.getText()).toBe("compound 1");

		//2 options are ture
		await IwPage.goBackToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(50);
		await IwPage.next();
		await IwPage.goForwardToQuestion('landing');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();

		await expect(await qTitle.getText()).toBe("compound 1");
	});
	
	it("should not display question if one or more than one simple expression conditions are true", async function () {
		await IwPage.goBackToQuestion('Simple Skip Source 1');

		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(6);
		await IwPage.next();
		await browser.pause(2000);

		//$('a=Show if more than 5').waitForExist(egoOpts.waitTime)
		await IwPage.goForwardToQuestion('reference number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(40);
		await IwPage.next();
		await browser.pause();
		//$('a=base is Less or Equal').waitForExist(egoOpts.waitTime)
		await IwPage.goForwardToQuestion('landing');
		await browser.pause();
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();
		await browser.pause(5000);
		var qTitle = await IwPage.questionTitle;
		await expect(await qTitle.getText()).toBe("landing 2");
	});

	it("should display question if several simple expression conditions are all true", async function () {
		await IwPage.goToQuestion('Simple Skip Source 1');
		//let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// negative test
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(4);
		await IwPage.next();

		await IwPage.goForwardToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(60);

		await IwPage.next();
		await IwPage.goForwardToQuestion('landing 2');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();

		var qTitle = await IwPage.questionTitle;
		await expect(await qTitle.getText()).toBe("landing 3");

		//one condition met, still negative
		await IwPage.goBackToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(50);
		await IwPage.next();
		await IwPage.goForwardToQuestion('landing 2');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();

		await expect(await qTitle.getText()).toBe("landing 3");

		//all conditions are met
		await IwPage.goBackToQuestion('Simple Skip Source 1');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(6);
		await IwPage.next();

		await IwPage.goForwardToQuestion('landing 2');
		await IwPage.next();

		await expect(await qTitle.getText()).toBe("compound 2");
	});


	it("should NOT display question if any of several simple expression conditions are all true", async function () {
		await IwPage.goToQuestion('Simple Skip Source 1');
		//let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// negative test
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(5);
		await IwPage.next();

		await IwPage.goForwardToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(50);

		await IwPage.next();
		await browser.pause(1000);

		var qTitle = await IwPage.questionTitle;
		await expect(await qTitle.getText()).toBe("compare number");
		await IwPage.inputField.setValue(50);
		await IwPage.next();

		await IwPage.goForwardToQuestion('landing 3');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();

		await expect(await qTitle.getText()).toBe("landing 4");

		//one condition met, still negative
		await IwPage.goBackToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(40);
		await IwPage.next();
		//$('a=compare number').waitForExist(egoOpts.waitTime);

		await IwPage.goForwardToQuestion('landing 3');
		await IwPage.next();

		await expect(await qTitle.getText()).toBe("landing 4");
		//neither conditions are met

		IwPage.fieldValues['reference number'].value = 6;
		await IwPage.goBackToQuestion('Simple Skip Source 1');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(6);
		await IwPage.next();
		//$('a=Show if more than 5').waitForExist(egoOpts.waitTime)
		await IwPage.goForwardToQuestion('base number');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.inputField.setValue(70);
		await IwPage.next();
		//$('a=compare number').waitForExist(egoOpts.waitTime);

		await IwPage.goForwardToQuestion('landing 3');
		//IwPage.inputField.waitForExist(egoOpts.waitTime);
		await IwPage.next();

		await expect(await qTitle.getText()).toBe("compound 3");
	});
});
