var IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../../.env");

describe('Skip Logic 1', function () {
	before(function () {
		// login
		IwPage.login(egoOpts.loginInterviewer.username, egoOpts.loginInterviewer.password);

		// start test1 interview
		IwPage.openInterview("TEST_STUDY", "skip_start");

		// set valid field values for moving forward through survey
		IwPage.fieldValues = {
			'Simple Skip Source 1' : {
				type: 'input',
				value: '5'
			},
			'Simple Skip Source 2' : {
                type: 'ms',
                options: {
                    1 : true,
                    2 : false,
                    3 : false,
                    4 : false,
                    5 : false
                }
			},
			'base number' : {
                type: 'input',
                value:50
			},
			'compare number' : {
                type: 'input',
                value:50
			},
			'reference number' : {
                type: 'input',
                value:50
			},

		}

	});

	beforeEach(function () {
		// every test starts at first question in survey
		IwPage.goToQuestion("skip_start");
	});

    it("should allow question to be shown if previous question condition is met: question should display if previous question is not skipped, don't know, or refused", function () {
		IwPage.goToQuestion('Simple Skip Source 1');
		let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// Simple Skip Source 1
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(4);
		IwPage.next();

		// Show if Less than or equal to 5
		expect(IwPage.questionTitle.getText()).toBe("Show if Less than or equal to 5");
		IwPage.next();

		// Question after skip
		expect(IwPage.questionTitle.getText()).toBe("Question after skip");
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.back();

		// Simple Skip Source 1
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		expect(IwPage.inputField().getValue()).toBe("4");
		IwPage.inputField().setValue(6);
		IwPage.next();

		// Show if more than 5
		expect(IwPage.questionTitle.getText()).toBe("Show if more than 5");
		IwPage.next();

		// Question after skip
		expect(IwPage.questionTitle.getText()).toBe("Question after skip");


		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.back();

		// Simple Skip Source 1
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(5);
		IwPage.next();

		// Show if equal to 5
		expect(IwPage.questionTitle.getText()).toBe("Show if equal to 5");
		IwPage.next();

		// Question after skip
		expect(IwPage.questionTitle.getText()).toBe("Question after skip");


	});

	it("should allow question to be shown if previous question condition is met: question should display if previous question IS skipped, don't know, or refused", function () {
		IwPage.goToQuestion('Simple Skip Source 1');
		let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// Simple Skip Source 1
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.rfLabel.click();
		IwPage.next();

		// Show if refused
		expect(IwPage.questionTitle.getText()).toBe("Show if Less than or equal to 5");
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.dkLabel.click();
		IwPage.next();

		// Show if Don't know
		expect(IwPage.questionTitle.getText()).toBe("Show if Less than or equal to 5");

	});

	it("should allow question to be shown if previous question condition is met: one, two or multiple selection response is selected in previous question.", function () {
		IwPage.goToQuestion('Simple Skip Source 2');
		let selector = IwPage.getOptionSelector(2);
		// Simple Skip Source 2
		IwPage.inputField().waitForExist(egoOpts.waitTime);

		// select Option 2 by clicking on label
        IwPage.optionLabel('opt2').click();
		IwPage.next();
		//Show if one option selected
		expect(IwPage.questionTitle.getText()).toBe("At least one option selected");
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);

		//show if 2 options selected
		IwPage.optionLabel('opt4').click();
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("At least one option selected");
		IwPage.back();

		//show if multiple options selected
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.unselectAllOptions(IwPage.fieldValues['Simple Skip Source 2'].options);
		IwPage.optionLabel('opt5').click();
		IwPage.optionLabel('opt2').click();
		IwPage.optionLabel('opt1').click();
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("At least one option selected");
	});

	it("should allow question to be shown if previous question condition is met: several multiple selection responses are selected in previous question.", function () {
		IwPage.goToQuestion('Simple Skip Source 2');
		let selector = IwPage.getOptionSelector(2);
		// Simple Skip Source 2
		IwPage.inputField().waitForExist(egoOpts.waitTime);

		// no options selected
		IwPage.dkLabel.click();
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("base number");

		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.unselectAllOptions(IwPage.fieldValues['Simple Skip Source 2'].options);
		IwPage.optionLabel('opt5').click();
		IwPage.optionLabel('opt2').click();
		IwPage.optionLabel('opt3').click();
		IwPage.next();
        browser.pause(5000);
		expect(IwPage.questionTitle.getText()).toBe("At least one option selected");
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("Several multiple selection responses are selected");
	});

	it("should show question if one numeric question is greater than the other", function () {
		IwPage.goForwardToQuestion('base number');
		let field = IwPage.fieldValues['base number']['field'];

		// set base number
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(75);
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("compare number");

		// set compare number
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(25);
		IwPage.next();

		//base > compare
		expect(IwPage.questionTitle.getText()).toBe("base higher than compare");

		//change compare to be higher than base
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		expect(IwPage.questionTitle.getText()).toBe("compare number");
		IwPage.inputField().setValue(85);

		IwPage.next();

		//base < compare
		expect(IwPage.questionTitle.getText()).toBe("base is lower than compare");

		//change compare to be equal to base
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		expect(IwPage.questionTitle.getText()).toBe("compare number");
		IwPage.inputField().setValue(75);

		IwPage.next();

		//base = compare
		expect(IwPage.questionTitle.getText()).toBe("base is equal to compare");

	});

	it("should allow question to be shown if previous question condition is met: one numeric question is GE/LE a number", function () {
		IwPage.goForwardToQuestion('reference number');
		let field = IwPage.fieldValues['base number']['field'];

		// set reference number
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(50);
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("base is Less or Equal");
		IwPage.inputField().waitForExist(egoOpts.waitTime);

		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("base is More or Equal");
		IwPage.inputField().waitForExist(egoOpts.waitTime);

		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("landing");
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(49);


		// check lower
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("base is Less or Equal");
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("landing");
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.back();
		IwPage.inputField().waitForExist(egoOpts.waitTime);

		// check higher
		IwPage.inputField().setValue(51);
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("base is More or Equal");
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("landing");

	});

	it("should show question if one numeric question is greater than the the sum of more than one other question", function () {
		IwPage.goToQuestion('Simple Skip Source 1');
		let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// set base number 1
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(5);
		IwPage.next();

		//set base number 2
		IwPage.goForwardToQuestion('base number');
		IwPage.inputField().setValue(50);
		IwPage.next();

		IwPage.goForwardToQuestion('compare number');
		IwPage.inputField().setValue(50);
		IwPage.next();

		//50 < 5+50 : negative test
		IwPage.goForwardToQuestion('landing 4');
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("landing 5");


		IwPage.goBackToQuestion('compare number');
		IwPage.inputField().setValue(60);
		IwPage.next();

		//60 > 5+45 : positive test
		IwPage.goForwardToQuestion('landing 4');
		IwPage.next();
		expect(IwPage.questionTitle.getText()).toBe("compare 2");

	});


});
