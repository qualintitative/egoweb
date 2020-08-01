var IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");
const { Browser } = require('selenium-webdriver');

describe('Skip Logic 2', function () {
	before(function () {
		// login
		IwPage.login(egoOpts.loginInterviewer.username, egoOpts.loginInterviewer.password);

		// start test1 interview
		IwPage.openInterview("TEST_STUDY", "skip_start");

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

	beforeEach(function () {
		// every test starts at first question in survey (commented out for headless)
		//	IwPage.goToQuestion("skip_start");
	});

	it("should display question if one or more than one simple expression conditions are true", function () {
		IwPage.goToQuestion('Simple Skip Source 1');
		let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// 2 options are true
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(4);
		IwPage.next();

		IwPage.goForwardToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(60);

		IwPage.next();
		IwPage.goForwardToQuestion('landing');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("compound 1");

		//2 options are ture
		IwPage.goBackToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(50);
		IwPage.next();
		IwPage.goForwardToQuestion('landing');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("compound 1");
	});
	
	it("should not display question if one or more than one simple expression conditions are true", function () {

		//negative test

		IwPage.goBackToQuestion('Simple Skip Source 1');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(6);
		IwPage.next();
		IwPage.pause();

		IwPage.goForwardToQuestion('reference number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(40);
		IwPage.next();
		IwPage.goForwardToQuestion('landing');
		IwPage.next();
		IwPage.pause();
		expect(IwPage.questionTitle.getText()).toBe("landing 2");
	});

	it("should display question if several simple expression conditions are all true", function () {
		IwPage.goToQuestion('Simple Skip Source 1');
		let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// negative test
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(4);
		IwPage.next();

		IwPage.goForwardToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(60);

		IwPage.next();
		IwPage.goForwardToQuestion('landing 2');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("landing 3");

		//one condition met, still negative
		IwPage.goBackToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(50);
		IwPage.next();
		IwPage.goForwardToQuestion('landing 2');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("landing 3");

		//all conditions are met

		IwPage.goBackToQuestion('Simple Skip Source 1');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(6);
		IwPage.next();

		IwPage.goForwardToQuestion('landing 2');
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("compound 2");
	});

	it("should NOT display question if any of several simple expression conditions are all true", function () {
		IwPage.goToQuestion('Simple Skip Source 1');
		let field = IwPage.fieldValues['Simple Skip Source 1']['field'];

		// negative test
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(5);
		IwPage.next();

		IwPage.goForwardToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(50);

		IwPage.next();
		browser.pause(1000);
		expect(IwPage.questionTitle.getText()).toBe("compare number");
		IwPage.inputField().setValue(50);
		IwPage.next();

		IwPage.goForwardToQuestion('landing 3');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("landing 4");



		//one condition met, still negative
		IwPage.goBackToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(40);
		IwPage.next();
		IwPage.goForwardToQuestion('landing 3');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.next();

		expect(IwPage.questionTitle.getText()).toBe("landing 4");
		//neither conditions are met

		IwPage.goBackToQuestion('Simple Skip Source 1');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(6);
		IwPage.next();
		IwPage.goForwardToQuestion('base number');
		IwPage.inputField().waitForExist(egoOpts.waitTime);
		IwPage.inputField().setValue(70);
		IwPage.next();

		IwPage.goForwardToQuestion('landing 3');
		IwPage.next();
		IwPage.pause();

		expect(IwPage.questionTitle.getText()).toBe("compound 3");
	});

});
