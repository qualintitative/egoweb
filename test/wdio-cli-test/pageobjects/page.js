/**
* main page object containing all methods, selectors and functionality
* that is shared across all page objects
*/

module.exports = class Page {
    /**
    * Opens a sub page of the page
    * @param path path of the sub page (e.g. /path/to/page.html)
    */
    get pageTitle () { return $('#pageTitle') }
    get loggedIn () { return $('.fui-list') }
    get inputUsername () { return $('#LoginForm_username') }
    get inputPassword () { return $('#LoginForm_password') }
    get btnLogin () { return $('input[type="submit"]') }

    login (username, password) {
        this.open()
        this.inputUsername.setValue(username);
        this.inputPassword.setValue(password);
        this.btnLogin.click(); 
    }

    open (path) {
        if(path == null)
            return browser.url('site/login');
        else
            return browser.url(path)
    }

    // update summernote fields
    updateNoteField (field, val) {
        browser.execute("$('" + field + "').code('" + val.replace(/'/g, "\\'") + "')");
    }
}
