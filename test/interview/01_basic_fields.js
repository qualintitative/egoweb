const IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");

describe('Basic Fields', function() {
  before(async function() {
    // login
    await IwPage.open();
    await IwPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await IwPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await IwPage.login();

    // start test1 interview
    await IwPage.openInterview("TEST_STUDY", "basic_start");
  });

  beforeEach(async function() {
    // every test starts at first question in survey
    //   await IwPage.goToQuestion("basic_start");
  });

  it("should handle positive numbers in number field", async function() {
    //console.log("going to question ");
    await IwPage.goToQuestion('num');
    //let field = IwPage.fieldValues['num']['field'];

    // 

    await IwPage.inputField.setValue(55);
    await IwPage.next();

    // num
    var qTitle = await IwPage.questionTitle;
    console.log(await qTitle.getText());
    await expect(await qTitle.getText()).not.toBe("num");

    // go back and check input value
    await IwPage.back();
    var iField = await IwPage.inputField;
    await expect(await iField.getValue()).toBe("55");
  });

  it("should handle negative numbers in number field", async function() {
    await IwPage.goToQuestion('num');
    //let field = IwPage.fieldValues['num']['field'];

    // num
    await IwPage.inputField.setValue(-7);
    await IwPage.next();

    var qTitle = await IwPage.questionTitle;
    //console.log(await qTitle.getText());
    await expect(await qTitle.getText()).not.toBe("num");

    // go back and check input value
    await IwPage.back();
    var iField = await IwPage.inputField;
    await expect(await iField.getValue()).toBe("-7");
  });

  it("should show error for letters in number field", async function() {
    await IwPage.goToQuestion('num');
    //let field = IwPage.fieldValues['num']['field'];

    // num
    //IwPage.inputField.waitForExist(egoOpts.waitTime);
    await IwPage.inputField.setValue("abc");
    await IwPage.next();

    // error message
    //$("div.alert").waitForExist(egoOpts.waitTime);
    let alert = await $("div.alert");
    await expect(await alert.getText()).toBe("Please enter a number.");

    // fix error
    await IwPage.inputField.setValue("5");
    await IwPage.next();

    // numdkrf
    var qTitle = await IwPage.questionTitle;
    expect(await qTitle.getText()).not.toBe("num");
  });

  it("should handle DK and RF with number field", async function() {
    await IwPage.goToQuestion('numdkrf');
    //let field = IwPage.fieldValues['numdkrf']['field'];

    // numdkrf
    //IwPage.inputField.waitForExist(egoOpts.waitTime);

    await IwPage.inputField.setValue("99");

    // dk should clear value
    await IwPage.dkLabel.click();
    var iField = await IwPage.inputField;
    await expect(await iField.getValue()).toBe("");
    await IwPage.next();

    // next page
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("numdkrf");
    await IwPage.back();

    await IwPage.inputField.setValue("44");

    //rf should clear value
    await IwPage.rfLabel.click();
    iField = await IwPage.inputField;
    await expect(await iField.getValue()).toBe("");
    await IwPage.next();

    // next page
    qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("numdkrf");
  });

  it("should succeed if number value is within min/max range", async function() {
    await IwPage.goToQuestion("num0to100");
    //let field = IwPage.fieldValues['num0to100']['field'];

    // num0to100

    // try min value
    await IwPage.inputField.setValue("0");
    await IwPage.next();

    // next page
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("num0to100");
    await IwPage.back();

    // try max value
    await IwPage.inputField.setValue("100");
    await IwPage.next();

    // next page
    qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("num0to100");
    await IwPage.back();

    // try a value inside range
    await IwPage.inputField.setValue("73");
    await IwPage.next();

    qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("num0to100");
  });

  it("should show error if number value is outside min/max range", async function() {
    await IwPage.goToQuestion("num0to100");
    //let field = IwPage.fieldValues['num0to100']['field'];

    // num0to100
    // IwPage.inputField.waitForExist(egoOpts.waitTime);

    // try value below min
    await IwPage.inputField.setValue("-1");
    await IwPage.next();

    // error message
    var alert = await $("div.alert");
    //.waitForExist(egoOpts.waitTime);
    await expect(await alert.getText()).toBe("The range of valid answers is 0 to 100." + IwPage.clickError);

    // fix value
    await IwPage.inputField.setValue("0");
    await IwPage.next();

    // next page
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("num0to100");
    await IwPage.back();

    // num0to100
    // IwPage.inputField.waitForExist(egoOpts.waitTime);

    // try value below min
    await IwPage.inputField.setValue("101");
    await IwPage.next();

    // error message
    alert = await $("div.alert");
    //$("div.alert").waitForExist(egoOpts.waitTime);
    await expect(await alert.getText()).toBe("The range of valid answers is 0 to 100." + IwPage.clickError);

    // fix value
    await IwPage.inputField.setValue("100");
    await IwPage.next();

    // next page
    qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("num0to100");
  });

  it("should show error if textual value is blank", async function() {
    await IwPage.goToQuestion("textual");
    await IwPage.inputField.setValue("");
    await IwPage.next();

    // error message
    var alert = $("div.alert");
    await expect(await alert.getText()).toBe("Value cannot be blank." + IwPage.clickError);

    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("textual");
    await IwPage.inputField.setValue("test");
    await IwPage.next();
    //browser.pause(5000)
    qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("textual");
  });

  it("should show error when date fields are missing", async function() {
    await IwPage.goToQuestion("date");

    // reset values
    var month = await IwPage.monthField;
    await month.selectByVisibleText('Select month');
    var year = await IwPage.yearField;
    await year.setValue("");
    var day = IwPage.dayField;
    await day.setValue("");

    await IwPage.next();

    // error message
    var alert = await $("div.alert");
    await expect(await alert.getText()).toBe("Please enter a month." + IwPage.clickError);

    await month.selectByVisibleText('December');
    //await browser.pause(500);
    //IwPage.next();
    alert = await $("div.alert");
    //$("div.alert").waitForExist(egoOpts.waitTime);
    await expect(await alert.getText()).toBe("Please enter a valid year." + IwPage.clickError);

    await year.setValue("1999");
    //IwPage.next();
    //alert = await $("div.alert");
    //$("div.alert").waitForExist(egoOpts.waitTime);
    await expect(await alert.getText()).toBe("Please enter a day of the month." + IwPage.clickError);
    await day.setValue("31");
    //await browser.pause(500);
    await IwPage.next();
    qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("date");
  });

  it("should show error when hour and minute fields are missing", async function() {
    await IwPage.goToQuestion("hour_min");

    var hour = await IwPage.hourField;
    var minute = await IwPage.minuteField;
    var pm = await IwPage.pmField;
    var refuse = await IwPage.rfLabel;
    await hour.setValue('');
    await minute.setValue('');
    //await IwPage.next();
    await refuse.click();
    await refuse.click();
    await IwPage.next();

    var alert = await $("div.alert");
    await expect(await alert.getText()).toBe("Please enter the time of day." + IwPage.clickError);

    await minute.setValue('60');
    await hour.setValue('23');
    await expect(await alert.getText()).toBe("Please enter the time of day." + IwPage.clickError);

    await pm.click();
    await expect(await alert.getText()).toBe("Please enter 0 to 59 for MM." + IwPage.clickError);

    await minute.setValue("59");
    await expect(await alert.getText()).toBe("Please enter 1 to 12 for HH." + IwPage.clickError);

    await hour.setValue('11');
    await IwPage.next();
    ''

    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("hour_min");
  });

  it("only weeks and days are displayed", async function() {
    await IwPage.goToQuestion("weeks_days");

    var years = await $('label=Years');
    var months = await $('label=Months');
    var hours = await $('label=Months');
    var minutes = await $('label=Minutes');

    await expect(await years.isExisting()).toBe(false);
    await expect(await months.isExisting()).toBe(false);
    await expect(await hours.isExisting()).toBe(false);
    await expect(await minutes.isExisting()).toBe(false);
  });

});