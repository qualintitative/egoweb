// login.page.js
var Page = require('./page')

var IwPage = Object.create(Page, {
    /**
     * define elements
     */
    form: {
        get: function () {
            return browser.element('#answerForm');
        }
    },
    startInterviewLink: {
        get: function () {
            return browser.element('=Start new interview');
        }
    },
    nextButton: {
        get: function () {
            return browser.element('button.orangebutton=Next');
        }
    },
    alterAddButton: {
        get: function () {
            return browser.element('input.alterSubmit');
        }
    },
    backButton: {
        get: function () {
            return browser.element('button.graybutton=Back');
        }
    },
    dkLabel: {
        get: function () {
            return browser.element('label=Don\'t Know')
        }
    },
    rfLabel: {
        get: function () {
            return browser.element('label=Refuse')
        }
    },
    questionTitle: {
        get: function () {
            return browser.element('span#questionTitle')
        }
    },

    // EGO ID of current case in survey
    ewid: {
        value: 0,
        enumerable: false,
        writable: true,
        configurable: false
    },

    // fake data to use in survey questions
    fieldValues: {
        value: {},
        enumerable: false,
        writable: true,
        configurable: false
    },

    /**
     * define or overwrite page methods
     */
    inputField: {
        value: function (id = null) {
            if (id != null) {
                return browser.element('input#Answer_' + id + '_VALUE');
            } else {
                return browser.element('input[name*="Answer"]');
            }
        }
    },

    specificQuestionTitle: {
        value: function (question) {
            return browser.element('span#questionTitle=' + question);
        }
    },

    goBackToQuestion: {
        value: function (question) {
            // keep hitting "back" button until we get to the question
            // TODO - put some "maximum" in this loop, and fail if it doesn't find the question
            while ((foo = IwPage.questionTitle.getText()) != question) {
                this.back();
            }
            this.pause();
        }
    },

    getOptionSelector: {
        value: function(num) {
            // add 1 to num because of hidden row with labels for ego/alter/alter_pair
            return "form#answerForm>div>div.panel>div:nth-child(" + (parseInt(num) + 1) + ")>input.answerInput";
        }
    },

    getOptionSpecifySelector: {
        value: function(num) {
            // add 1 to num because of hidden row with labels for ego/alter/alter_pair
            // use 3rd child because of checkbox, label, then textbox
            return "form#answerForm>div>div.panel>div:nth-child(" + (parseInt(num) + 1) + ")>input:nth-child(3)";
        }
    },

    selectOption: {
        value: function (num) {
            let selector = this.getOptionSelector(num);
            if (!browser.element(selector).isSelected()) {
                browser.element(selector).click();
            }
        }
    },

    unselectOption: {
        value: function (num) {
            let selector = this.getOptionSelector(num);
            if (browser.element(selector).isSelected()) {
                browser.element(selector).click();
            }
        }
    },

    unselectAllOptions: {
        value: function(options) {
            for (var key in options) {
                if (!options.hasOwnProperty(key)) continue;
                this.unselectOption(key);
            }
        }
    },

    optionLabel: {
        value: function (label) {
            return browser.element('label=' + label);
        }
    },

    goForwardToQuestion: {
        value: function (question) {
            // keep hitting "next" button until we get to the question
            // TODO - put some "maximum" in this loop, and fail if it doesn't find the question
            while ((curfield = IwPage.questionTitle.getText()) != question) {
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
                    }
                }
                this.next();
            }
        }
    },

    next: {
        value: function () {
            this.nextButton.waitForVisible(browser.options.egoweb.waitTime);
            this.nextButton.click();
            this.pause();
        }
    },

    back: {
        value: function () {
            this.backButton.waitForVisible(browser.options.egoweb.waitTime);
            this.backButton.click();
            this.pause();
        }
    },

    pause: {
        value: function () {
            browser.pause(browser.options.egoweb.pauseTime);
        }
    },

    openInterview: {
        value: function (interview) {
            this.open('interview');

            browser.element("=" + interview).click();

            // search existing IDs to find max
            browser.element("table.items").waitForExist(browser.options.egoweb.waitTime);
            ids = browser.elements("table.items tr td");

            max = 0;
            ids.value.forEach(function (el) {
                val = parseInt(browser.elementIdText(el.ELEMENT).value);
                if (val > max) {
                    max = val;
                }
            });

            // pick a random ID higher than max, in case multiple tests/threads are running simultaneously
            newid = max + 1 + Math.floor(Math.random() * 100);

            // save ID
            this.ewid = newid;

            // start interview
            this.startInterviewLink.click();

            return this;
        }
    },

    submit: {
        value: function () {
            this.form.submitForm();
            return this;
        }
    }
});

module.exports = IwPage;