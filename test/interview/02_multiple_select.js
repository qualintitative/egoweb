const IwPage = require('../pageobjects/interview.page');
var assert = require('assert');
const env = require("../.env");

describe('Multiple Select', function() {
  before(async function() {
    // login
    await IwPage.open();
    await IwPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await IwPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await IwPage.login();

    // start test1 interview
    await IwPage.openInterview("TEST_STUDY", "ms_start");

    // set valid field values for moving forward through survey
    IwPage.fieldValues = {
      'ms0to5of5': {
        type: 'ms',
        options: {
          1: true,
          2: false,
          3: false,
          4: false,
          5: false
        }
      },
      'ms0to5of5withother': {
        type: 'ms',
        options: {
          1: true,
          2: false,
          3: false,
          4: false,
          5: false
        }
      },
      'ms1of2': {
        type: 'ms',
        options: {
          1: true,
          2: false,
        }
      },
      'ms1of2dkrf': {
        type: 'ms',
        options: {
          1: true,
          2: false,
          3: false, // dk
          4: false // rf
        }
      },
    };

  });

  beforeEach(function() {
    // every test starts at ms_start (commented out due to headless bug)
    //  IwPage.goToQuestion("ms_start");
  });

  it("should select option when label is clicked", async function() {
    await IwPage.goToQuestion('ms0to5of5');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

    // select Option 2 by clicking on label
    var optionLabel = await IwPage.optionLabel('Option 2');
    await optionLabel.click();
    //await browser.pause(5000);
    let selector = await $(IwPage.getOptionSelector(2));
    await expect(await selector.isSelected()).toBe(true);

    // unselect Option 2 by clicking on label
    await optionLabel.click();
    await expect(await selector.isSelected()).toBe(false);
  });

  it("should allow 0 to 5 selections for a question with 5 options", async function() {
    await IwPage.goToQuestion('ms0to5of5');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5.options);

    // no options selected
    var option1 = await $(IwPage.getOptionSelector(1));
    var option2 = await $(IwPage.getOptionSelector(2));
    var option3 = await $(IwPage.getOptionSelector(3));
    var option4 = await $(IwPage.getOptionSelector(4));
    var option5 = await $(IwPage.getOptionSelector(5));

    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);
    await IwPage.next();

    // next question
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("ms0to5of5");
    await IwPage.back();

    // no options selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);

    // select 1st option
    await IwPage.selectOption(1);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms0to5of5");
    await IwPage.back();

    // 1 option selected
    await expect(await option1.isSelected()).toBe(true);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);

    // select 2 more options
    await IwPage.selectOption(4);
    await IwPage.selectOption(5);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms0to5of5");
    await IwPage.back();

    // 3 options selected
    await expect(await option1.isSelected()).toBe(true);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(true);
    await expect(await option5.isSelected()).toBe(true);

    // select 2 more options
    await IwPage.selectOption(2);
    await IwPage.selectOption(3);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms0to5of5");
    await IwPage.back();

    // all options selected
    await expect(await option1.isSelected()).toBe(true);
    await expect(await option2.isSelected()).toBe(true);
    await expect(await option3.isSelected()).toBe(true);
    await expect(await option4.isSelected()).toBe(true);
    await expect(await option5.isSelected()).toBe(true);
  });

  it("should show/hide text box when option with Other Please Specify is clicked", async function() {
    await IwPage.goToQuestion('ms0to5of5withother');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms0to5of5withother.options);


    // no options selected
    var option1 = await $(IwPage.getOptionSelector(1));
    var option2 = await $(IwPage.getOptionSelector(2));
    var option3 = await $(IwPage.getOptionSelector(3));
    var option4 = await $(IwPage.getOptionSelector(4));
    var option5 = await $(IwPage.getOptionSelector(5));

    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);

    var otherSpecify = await $(IwPage.getOptionSpecifySelector(5));
    await expect(await otherSpecify.isDisplayed()).toBe(false);
    await IwPage.next();

    // next question
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("ms0to5of5withother");
    await IwPage.back();

    // no options selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);

    // select 5th option, with specify
    await IwPage.selectOption(5);
    await expect(await option5.isSelected()).toBe(true);
    await expect(await otherSpecify.isDisplayed()).toBe(true);
    var osSelector = await $(IwPage.getOptionSpecifySelector(5));
    await osSelector.setValue("blah blah");
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms0to5of5withother");
    await IwPage.back();

    // 1 option selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(true);
    await expect(await osSelector.getValue()).toBe("blah blah");

    // unselect 5th option
    await IwPage.unselectOption(5);
    await expect(await option5.isSelected()).toBe(false);
    await expect(await osSelector.isDisplayed()).toBe(false);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms0to5of5withother");
    await IwPage.back();

    // check that 5th option is not set
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);
    await expect(await option5.isSelected()).toBe(false);
    await expect(await osSelector.isDisplayed()).toBe(false);

  });

  it("should only allow 1 option if min/max selection is 1", async function() {
    await IwPage.goToQuestion('ms1of2');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2.options);

    // no options selected
    var option1 = await $(IwPage.getOptionSelector(1));
    var option2 = await $(IwPage.getOptionSelector(2));
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);

    // select 1st option
    await IwPage.selectOption(1);
    await expect(await option1.isSelected()).toBe(true);
    await expect(await option2.isSelected()).toBe(false);
    await IwPage.next();

    // next question
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("ms1of2");
    await IwPage.back();

    // make sure 1st option is still selected
    await expect(await option1.isSelected()).toBe(true);
    await expect(await option2.isSelected()).toBe(false);

    // select 2nd option
    await IwPage.selectOption(2);
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(true);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms1of2");
    await IwPage.back();

    // make sure 2nd option is still selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(true);
  });

  it("should show error if no options are selected and min/max selection is 1", async function() {
    await IwPage.goToQuestion('ms1of2');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2.options);


    // no options selected
    var option1 = await $(IwPage.getOptionSelector(1));
    var option2 = await $(IwPage.getOptionSelector(2));
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);

    // click next
    await IwPage.next();

    // should stay on same page with error message
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("ms1of2");
    var alert = await $("div.alert");
    await expect(await alert.getText()).toBe("Select 1 response please.");

    // fix error
    await IwPage.selectOption(1);
    await IwPage.next();

    // next page
    await expect(await qTitle.getText()).not.toBe("ms1of2");
  });

  it("should only allow 1 option if min/max selection is 1, even if dk/rf options are available", async function() {
    await IwPage.goToQuestion('ms1of2dkrf');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2dkrf.options);

    // no options selected
    var option1 = await $(IwPage.getOptionSelector(1));
    var option2 = await $(IwPage.getOptionSelector(2));
    var option3 = await $(IwPage.getOptionSelector(3));
    var option4 = await $(IwPage.getOptionSelector(4));

    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);

    // select 2nd option
    await IwPage.selectOption(2);
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(true);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await IwPage.next();

    // next question
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).not.toBe("ms1of2dkrf");
    await IwPage.back();

    // make sure 2nd option is still selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(true);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);

    // select dk
    await IwPage.selectOption(3);
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(true);
    await expect(await option4.isSelected()).toBe(false);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms1of2dkrf");
    await IwPage.back();

    // make sure dk is still selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(true);
    await expect(await option4.isSelected()).toBe(false);

    // select 1st option, then select rf
    await IwPage.selectOption(1);
    await expect(await option1.isSelected()).toBe(true);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);
    await IwPage.selectOption(4);
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(true);
    await IwPage.next();

    // next question
    await expect(await qTitle.getText()).not.toBe("ms1of2dkrf");
    await IwPage.back();

    // make sure rf is still selected
    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(true);
  });

  it("should show error if no options are selected and min/max selection is 1, even if dk/rf options are available", async function() {
    await IwPage.goToQuestion('ms1of2dkrf');
    await IwPage.unselectAllOptions(IwPage.fieldValues.ms1of2dkrf.options);

    // no options selected
    var option1 = await $(IwPage.getOptionSelector(1));
    var option2 = await $(IwPage.getOptionSelector(2));
    var option3 = await $(IwPage.getOptionSelector(3));
    var option4 = await $(IwPage.getOptionSelector(4));

    await expect(await option1.isSelected()).toBe(false);
    await expect(await option2.isSelected()).toBe(false);
    await expect(await option3.isSelected()).toBe(false);
    await expect(await option4.isSelected()).toBe(false);

    // click next
    await IwPage.next();

    // should stay on same page with error message
    var qTitle = await IwPage.questionTitle;
    await expect(await qTitle.getText()).toBe("ms1of2dkrf");

    var alert = await $("div.alert");
    await expect(await alert.getText()).toBe("Select 1 response please." + IwPage.clickError);

    // fix error
    await IwPage.selectOption(3);
    await IwPage.next();
    await browser.pause(2000);
    await expect(await qTitle.getText()).not.toBe("ms1of2dkrf");
  });

});