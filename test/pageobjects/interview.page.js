const Page = require('./page');

/**
 * sub page containing specific selectors and methods for a specific page
 */
class IwPage extends Page {
  /**
   * define elements
   */
  get form() {
    return $('#answerForm');
  }
  get startInterviewLink() {
    return $('=Start new interview');
  }
  get nextButton() {
    return $('button.orangebutton=Next');
  }
  get backButton() {
    return $('button.graybutton=Back');
  }
  get finishButton() {
    return $('button.orangebutton=Finish');
  }
  get dkLabel() {
    return $('label=Don\'t Know');
  }
  get rfLabel() {
    return $('label=Refuse');
  }
  get questionTitle() {
    return $('#questionTitle');
  }
  get alterTextBox() {
    return $("form#alterForm .answerInput");
  }
  get alterAddButton() {
    return $('input.alterSubmit');
  }

  /**
   * define or overwrite page methods
   */
   get inputField() {
    return $('input[name*="Answer"]');
  }

  get monthField() {
    return $('select');
  }

  get dayField() {
    return $('input.day');
  }


  get yearField() {
    return $('input.year');
  }


  get hourField() {
    return $('input.hour');
  }


  get minuteField() {
    return $('input.minute');
  }


  get amField() {
    return $('input.am');
  }


  get pmField() {
    return $('input.pm');
  }

  clickError = " Click \"Next\" again to skip to the next question.";

  // EGO ID of current case in survey
  ewid = {
    value: 0,
    enumerable: false,
    writable: true,
    configurable: false
  }

  // fake data to use in survey questions
  fieldValues = {
    value: {},
    enumerable: false,
    writable: true,
    configurable: false
  }

  // fake data to use in survey questions
  navLinks = {
    value: {},
    enumerable: false,
    writable: true,
    configurable: false
  }


  async specificQuestionTitle(question) {
    await $('span#questionTitle=' + question);
  }


  async goBackToQuestion(question) {
    // keep hitting "back" button until we get to the question
    // TODO - put some "maximum" in this loop, and fail if it doesn't find the question
    var curfield = await this.questionTitle.getText()
    while ((curfield = await this.questionTitle.getText()) != question) {
      await this.back();
    }
    await this.pause();
  }


  async goToQuestion(question) {
    await super.open(this.navLinks[question]);
  }


  getOptionSelector(num) {
    // add 1 to num because of hidden row with labels for ego/alter/alter_pair
    return "form#answerForm>div>div.panel>div:nth-child(" + (parseInt(num) + 1) + ")>input.answerInput";
  }


  getOptionSpecifySelector(num) {
    // add 1 to num because of hidden row with labels for ego/alter/alter_pair
    // use 3rd child because of checkbox, label, then textbox
    return "form#answerForm>div>div.panel>div:nth-child(" + (parseInt(num) + 1) + ")>input:nth-child(3)";
  }


  async selectOption(num) {
    let selector = await $(this.getOptionSelector(num));
    if (await selector.isSelected() == false) {
      await $(selector).click();
    }
  }


  async unselectOption(num) {
    let selector = await $(this.getOptionSelector(num));
    if (await selector.isSelected() == true) {
      await $(selector).click();
    }
  }


  async unselectAllOptions(options) {
    for (var key in options) {
      if (options.hasOwnProperty(key) == false)
        continue;
      await this.unselectOption(key);
    }
  }


   optionLabel(label) {
    return $('label=' + label);
  }


  async goForwardToQuestion(question) {
    // keep hitting "next" button until we get to the question
    // TODO - put some "maximum" in this loop, and fail if it doesn't find the question
    //this.questionTitle.waitForExist(egoOpts.waitTime)
    var curfield = await this.questionTitle.getText()
    while ((curfield = await this.questionTitle.getText()) != question) {
      if (curfield in this.fieldValues) {
        let fv = this.fieldValues[curfield];
        switch (fv.type) {
          case 'input':
            // simple text filed
            await this.inputField.setValue(fv.value);
            break;
          case 'ms':
            for (var key in fv.options) {
              if (!fv.options.hasOwnProperty(key)) continue;
              if (fv.options[key]) {
                await this.selectOption(key);
              } else {
                await this.unselectOption(key);
              }
            }
            break;
          case 'alters':
            this.removeAllAlters();
            for (i = 0; i < fv.values.length; i++) {
              await this.addAlter(fv.values[i]);
            }
            break;
        }
      }
      await this.next();
    }
  }


  async next() {
    //browser.pause(egoOpts.pauseTime);
    //this.nextButton.waitForExist(egoOpts.waitTime);
    await this.nextButton.click();
    await browser.pause(egoOpts.pauseTime);
  }


  async back() {
    //this.backButton.waitForExist(egoOpts.waitTime);
    await this.backButton.click();
    await this.pause();
  }


  async finish() {
    //this.finishButton.waitForExist(egoOpts.waitTime);
    await this.finishButton.click();
  }


  async pause() {
    await browser.pause(egoOpts.pauseTime);
  }


  async addAlter(name) {
    await this.alterTextBox.setValue(name);
    await this.alterAddButton.click();
    await this.pause();
  }


  removeNthAlterButton(num) {
    return $("div#alterListBox>table.items>tbody>tr:nth-child(" + parseInt(num) + ")>td>a.alter-delete");
  }


  async removeNthAlter(num) {
    let btn = await this.removeNthAlterButton(num);
    await btn.click();
    await this.pause();
  }


  async removeAllAlters() {
    var btn = await this.removeNthAlterButton(1);
    while (await btn.isExisting()) {
      await btn.click();
      await this.pause();
      btn = await this.removeNthAlterButton(1);
    }
  }


  async getAlterCount() {
    let alters = await $("div#alterListBox").$$("tr");
    return alters.length;
  }


  getTableCellSelector(row, col) {
    return "#answerForm>#qTable>tbody>tr.multi:nth-child(" + parseInt(row) + ")>td:nth-child(" + parseInt(col) + ")";
  }


  getTableRowSelector(row) {
    return "#answerForm>#qTable>tbody>tr.multi:nth-child(" + parseInt(row) + ")";
  }


  getTableHeaderText(col) {
    //let selector = "form#answerForm>#qTable>thead>tr.multi:nth-child(" + parseInt(1) + ")>td:nth-child(" + parseInt(col) + ")";
    return $('//*[@id="answerForm"]/div[2]/table/thead/tr[2]/td[' + col + ']');
  }


  getTableCellInputElement(row, col) {
    let selector = this.getTableCellSelector(row, col);
    selector += ">input";
    return $(selector);
  }

  async getTableRowHighlight(row) {
    let selector = this.getTableCellSelector(row, 1);
    let el = await $(selector);
    let foo = await el.getAttribute("class");
    return (foo.indexOf("alert-danger") !== -1);
  }

  async updateNavLinks() {
    // set up nav links to be referenced by question title
    //$("#second li").waitForExist(egoOpts.waitTime);
    var menuLinks = await $("#second").$$("a");

      
    for (let m = 0; m < menuLinks.length; m++) {
      //let link = await menuLinks[m].getAttribute("href");
      let html = await menuLinks[m].getHTML();
      //console.log(html.replace(/<(?:.|\n)*?>/gm, ''));
      let title = html.replace(/<(?:.|\n)*?>/gm, '');
    //  console.log(title.replace(/<(?:.|\n)*?>/gm, ''), link, m, menuLinks.length);
      this.navLinks[title] = html.match(/href="(.*)">(.*)</)[1];
      //console.log(title);
    }
    //console.log("nav links updated");
  }


  async openInterview(interview, startPage) {
    await $("h3=" + interview).click();
    // search existing IDs to find max
    var ids = [];
    var max = 0;
    var vals = []
    var val = 0;
    var div = await $("h3=" + interview).getAttribute("data-target");
    await browser.pause(1000);
    var interviewDiv = await $(div);

   if (await interviewDiv.isExisting() == true) {
      ids = await interviewDiv.$$("a");
       for(var i = 0; i < ids.length; i++){
        var el = await ids[i].getText();
        var val = parseInt(el);
        vals.push(val);
        if (val > max) {
          max = val;
        }
      }
    }

    if (egoOpts.reuseInterview == true && max != 0) {
      console.log("existing interview found: " + max)

      // opens most recent interview
      this.ewid = max;
      await $('a=' + this.ewid).scrollIntoView(false)
      await $('a=' + this.ewid).click();
      await this.pause();
      await this.updateNavLinks();

      if (startPage != null){
        console.log("starting.. " + startPage, this.navLinks[startPage]);
        await super.open(this.navLinks[startPage]);
        //await browser.pause(1000);

      }else{
        await super.open(this.navLinks["INTRODUCTION"]);
        console.log("starting.. INTRODUCTION", this.navLinks["INTRODUCTION"]);
      }
    } else {
      this.ewid = max + 1 + Math.floor(Math.random() * 100);
      console.log("new interview")
      await this.startInterviewLink.click();
      //await browser.pause(1000)
      await this.goForwardToQuestion("EGO ID");

      // enter ego id
      let id = await this.inputField;
      //id.waitForExist(egoOpts.waitTime);
      await id.setValue(this.ewid);
      await this.next();
      await this.pause();
      await this.updateNavLinks();
     // console.log(this.navLinks[startPage]);
      if (startPage != null)
        await super.open(this.navLinks[startPage]);
    }
  }


  async submit() {
    await this.form.submitForm();
    //return this;
  }

}

module.exports = new IwPage;