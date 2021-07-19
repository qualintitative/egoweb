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
        return $('label=Don\'t Know')
    }

    get rfLabel() {
        return $('label=Refuse')
    }

    get questionTitle() {
        return $('#questionTitle')
    }

    get alterTextBox() {
        return $("form#alterForm .answerInput");
    }

    get alterAddButton() {
        return $('input.alterSubmit');
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

    /**
     * define or overwrite page methods
     */
    inputField(id = null) {
        if (id != null) {
            return $('input#Answer_' + id + '_VALUE');
        } else {
            return $('input[name*="Answer"]');
        }
    }

    monthField() {
        return $('select');
    }

    dayField() {
        return $('input.day');
    }


    yearField() {
        return $('input.year');
    }


    hourField() {
        return $('input.hour');
    }


    minuteField() {
        return $('input.minute');
    }


    amField() {
        return $('input.am');
    }


    pmField() {
        return $('input.pm');
    }


    specificQuestionTitle(question) {
        return $('span#questionTitle=' + question);
    }


    goBackToQuestion(question) {
        // keep hitting "back" button until we get to the question
        // TODO - put some "maximum" in this loop, and fail if it doesn't find the question
        var foo = this.questionTitle.getText();
        while ((foo = this.questionTitle.getText()) != question) {
            this.back();
        }
        this.pause();
    }


    goToQuestion(question) {
        this.open(this.navLinks[question]);
        this.pause();
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


    selectOption(num) {
        let selector = this.getOptionSelector(num);
        if (!$(selector).isSelected()) {
            $(selector).click();
        }
    }


    unselectOption(num) {
        let selector = this.getOptionSelector(num);
        if ($(selector).isSelected()) {
            $(selector).click();
        }
    }


    unselectAllOptions(options) {
        for (var key in options) {
            if (!options.hasOwnProperty(key)) continue;
            this.unselectOption(key);
        }
    }


    optionLabel(label) {
        return $('label=' + label);
    }


    goForwardToQuestion(question) {
        // keep hitting "next" button until we get to the question
        // TODO - put some "maximum" in this loop, and fail if it doesn't find the question
        this.questionTitle.waitForExist(egoOpts.waitTime)
        var curfield = this.questionTitle.getText()
        while ((curfield = this.questionTitle.getText()) != question) {
            if (curfield in this.fieldValues) {
                let fv = this.fieldValues[curfield];
                switch (fv.type) {
                    case 'input':
                        // simple text filed
                        this.inputField(fv.field).setValue(fv.value);
                        break;
                    case 'ms':
                        for (var key in fv.options) {
                            if (!fv.options.hasOwnProperty(key)) continue;
                            if (fv.options[key]) {
                                this.selectOption(key);
                            } else {
                                this.unselectOption(key);
                            }
                        }
                        break;
                    case 'alters':
                        this.removeAllAlters();
                        for (i = 0; i < fv.values.length; i++) {
                            this.addAlter(fv.values[i]);
                        }
                        break;
                }
            }
            this.next();
        }
    }


    next() {
        this.pause(500);
        this.nextButton.waitForExist(egoOpts.waitTime);
        this.nextButton.click();
        this.pause(1000);
    }


    back() {
        this.backButton.waitForExist(egoOpts.waitTime);
        this.backButton.click();
        this.pause();
    }


    finish() {
        this.finishButton.waitForExist(egoOpts.waitTime);
        this.finishButton.click();
    }


    pause() {
        browser.pause(egoOpts.pauseTime);
    }


    addAlter(name) {
        this.alterTextBox.setValue(name);
        this.alterAddButton.click();
        this.pause();
    }


    removeNthAlterButton(num) {
        return $("div#alterListBox>table.items>tbody>tr:nth-child(" + parseInt(num) + ")>td>a.alter-delete");
    }


    removeNthAlter(num) {
        this.removeNthAlterButton(num).click();
        this.pause();
    }


    removeAllAlters() {
        let btn = this.removeNthAlterButton(1);

        while (btn.isExisting()) {
            btn.click();
            this.pause();
            btn = this.removeNthAlterButton(1);
        }
    }


    getAlterCount() {
        let alters = $("div#alterListBox").$$("tr");
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
        return $('//*[@id="answerForm"]/div[2]/table/thead/tr[2]/td[' + col + ']').getHTML(false);
    }


    getTableCellInputElement(row, col) {
        let selector = this.getTableCellSelector(row, col);
        selector += ">input";
        return $(selector);
    }


    getTableRowHighlight(row) {
        let selector = this.getTableCellSelector(row, 1);
        let el = $(selector);
        let foo = el.getAttribute("class");
        return (foo.indexOf("bg-danger") !== -1);
    }



    updateNavLinks() {
        // set up nav links to be referenced by question title
        $("#second li").waitForExist(egoOpts.waitTime);
        let menuLinks = $("#second").$$("a");
        for(let m = 0; m < menuLinks.length; m++){
            let link = menuLinks[m].getAttribute("href");
            let title = menuLinks[m].getHTML();
            this.navLinks[title.replace(/<(?:.|\n)*?>/gm, '')] = link;
        }
    }


    openInterview(interview, startPage) {
        $("h3=" + interview).click();
        var ids = [];
        // search existing IDs to find max
        var max = 0;
        var vals = []
        var val = 0;
        let div = $("h3=" + interview).getAttribute("data-target");
        if($(div).$(".list-group").isExisting()){
            $(div).$(".list-group").waitForExist(egoOpts.waitTime);
            ids = $(div).$(".list-group").$$("a");
            ids.forEach(function (el) {
                val = parseInt(el.getText());
                vals.push(val);
                if (val > max) {
                    max = val;
                }
            });
        }
        //console.log(egoOpts.reuseInterview, max)
        if (egoOpts.reuseInterview == true && max != 0) {
            // opens most recent interview
            this.ewid = max;
            $('a=' + this.ewid).scrollIntoView(false)
            $('a=' + this.ewid).click();
            this.updateNavLinks();

            if (startPage != null)
                this.open(this.navLinks[startPage]);
            else
                this.open(this.navLinks["INTRODUCTION"]);
        } else {
            this.ewid = max + 1 + Math.floor(Math.random() * 100);
            console.log("new interview")
            this.startInterviewLink.click();
            browser.pause(1000)
            this.goForwardToQuestion("EGO ID");

            // enter ego id
            let id = this.inputField();
            id.waitForExist(egoOpts.waitTime);
            id.setValue(this.ewid);
            this.next();
            this.updateNavLinks();

            if (startPage != null)
                this.open(this.navLinks[startPage]);
        }

        return this;
    }


    submit() {
        this.form.submitForm();
        return this;
    }

}

module.exports = new IwPage;
