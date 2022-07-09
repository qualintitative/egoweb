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
    get loggedIn () { return $('#mainMenu') }
    get inputUsername () { return $('#loginform-username') }
    get inputPassword () { return $('#loginform-password') }
    get btnLogin () { return $('button[type="submit"]') }

    async login (username, password) {
       // this.open()
        //this.inputUsername.setValue(username);
        //this.inputPassword.setValue(password);
        await this.btnLogin.click(); 
    }

    async open (path) {
        if(path == null)
            await browser.url('site/login');
        else
            await browser.url(path)
    }

    // update summernote fields
    async updateNoteField (field, val) {
        await browser.execute("$('" + field + "').summernote('code', '" + val.replace(/'/g, "\\'") + "');");
    }
}
