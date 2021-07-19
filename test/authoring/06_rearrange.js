const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Rearrange Questions', function () {
  before(function () {
    AuthoringPage.open();
    AuthoringPage.login(egoOpts.loginAdmin.username, egoOpts.loginAdmin.password);
  });
  describe('Rearrange', function () {

    it('Go to question list page', function () {
      studyLink = $('//div[@aria-label="' + studyTest.settings.title + '"]//a[text()="Authoring"]')
      studyUrl = studyLink.getAttribute("href");
      browser.url(studyUrl);
      idQLink = $('//main//a[text()="Questions"]')
      expect(idQLink).toBeExisting();
      browserUrl = idQLink.getAttribute("href");
      browser.url(browserUrl)
      browser.pause(1000);
    });

    it('Rearange', function () {
      btnQ0 = $$("//header/button")[0]
      var label0 = btnQ0.getText();
      btnQ1 = $$("//header/button")[1]
      var label1 = btnQ1.getText();
      btnQ2 = $$("//header/button")[2]
      var label2 = btnQ2.getText();
      

        browser.performActions([{
          type: 'pointer',
          id: 'pointer1',
          parameters: { pointerType: 'mouse' },
          actions: [
            { type: 'pointerMove', origin: btnQ0, x: 5, y: 5 },
            { type: 'pointerDown', button: 0 },
            { type: 'pointerMove' ,  origin: 'pointer', x: 0 , y: 5, duration: 5},
          ]
        }])
      

        // emulate drop with js
        browser.execute(
          function (elemDrag, elemDrop) {
            const pos = elemDrop.getBoundingClientRect()
            const center2X = Math.floor((pos.left + pos.right) / 2)
            const center2Y = Math.floor((pos.top + pos.bottom) / 2)

            function fireMouseEvent(type, relatedTarget, clientX, clientY) {
              const evt = new MouseEvent(type, { clientX, clientY, relatedTarget, bubbles: true })
              relatedTarget.dispatchEvent(evt)
            }

            fireMouseEvent('dragover', elemDrop, center2X, center2Y)
            fireMouseEvent('dragend', elemDrag, center2X, center2Y)
            fireMouseEvent('mouseup', elemDrag, center2X, center2Y)

          },
          btnQ0,
          btnQ2
        )
        browser.pause(3000);

      browser.url(browserUrl)
      browser.pause(1000);
      place0 = $$("//header/button")[0].getText();
      place1 = $$("//header/button")[1].getText();
      place2 = $$("//header/button")[2].getText();
 
      assert.strictEqual(place2, label0);
      
  })

});
});