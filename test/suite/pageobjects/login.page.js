var Page = require('./page')

var LoginPage = Object.create(Page, {
    /**
     * define elements
     */
    username: { get: function () { return browser.element('#LoginForm_username'); }},
    password: { get: function () { return browser.element('#LoginForm_password'); }},
    form: { get: function () { return browser.element('#login-form'); }},
    errorMessage: { get: function () { return browser.element("div=Incorrect username or password."); }},
    logoutLink: { get: function () { return browser.element("a=Log Out"); }},
    loginLink: { get: function () { return browser.element("a=Click here to log in"); }},

    /**
     * define or overwrite page methods
     */
    logout: { value: function() {
        this.open('site/logout');
        return this;
    }},

    loginAs: { value: function(username, password) {
        // logout
        this.logout();

        // go to login page
        this.open();
        this.loginLink.waitForExist(browser.options.egoweb.waitTime);
        this.loginLink.click();

        let loginh1 = browser.element("h1=Login");
        loginh1.waitForExist(browser.options.egoweb.waitTime);

        // login with username/password
        browser.element("#LoginForm_username").setValue(username);
        browser.element("#LoginForm_password").setValue(password);
        this.submit();
        return this;
    }},

    submit: { value: function() {
        this.form.submitForm();
        return this;
    } }
});

module.exports = LoginPage;
