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
        value: function (id) {
            return browser.element('input#Answer_' + id + '_VALUE');
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

    selectOption: {
        value: function (id) {
            if (!browser.element('input#multiselect-'+id).isSelected()) {
                browser.element('input#multiselect-'+id).click();
            }
        }
    },

    unselectOption: {
        value: function (id) {
            if (browser.element('input#multiselect-'+id).isSelected()) {
                browser.element('input#multiselect-'+id).click();
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
                                console.log("key="+key);
                                if (fv.options[key]) {
                                    console.log('select');
                                    this.selectOption(key);
                                } else {
                                    console.log('unselect');
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
