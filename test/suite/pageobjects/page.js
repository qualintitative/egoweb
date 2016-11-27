function Page () {
    this.title = 'My Page';
}

Page.prototype.open = function (path = '') {
    if(path.match("http://"))
        browser.url(path)
    else
        browser.url('/' + path)
};

module.exports = new Page();