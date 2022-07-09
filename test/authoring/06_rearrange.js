const AuthoringPage = require('../pageobjects/authoring.page');
var assert = require('assert');
const env = require("../.env");

describe('Rearrange Questions', function () {
  before(async function () {
    await AuthoringPage.open();
    await AuthoringPage.inputUsername.setValue(egoOpts.loginAdmin.username)
    await AuthoringPage.inputPassword.setValue(egoOpts.loginAdmin.password)
    await AuthoringPage.login();
    const studyUrl = await AuthoringPage.studyLink.getAttribute("href");
    await browser.url(studyUrl);
  });
  describe('Rearrange', function () {

    it('Go to question list page', async function () {
      browserUrl = await AuthoringPage.questionsLink.getAttribute("href");
      await browser.url(browserUrl);
    });

    it('Rearange', async function () {
      btnQ0 = await $$("//header/button")[0]
      var label0 = await btnQ0.getText();
      btnQ1 = await $$("//header/button")[1]
      var label1 = await btnQ1.getText();
      btnQ2 = await $$("//header/button")[2]
      var label2 = await btnQ2.getText();
      

        await browser.performActions([{
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
        await browser.execute(
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
      await browser.pause(3000);
      await browser.url(browserUrl)
      //browser.pause(1000);
      place0 = await $$("//header/button")[0].getText();
      place1 = await $$("//header/button")[1].getText();
      place2 = await $$("//header/button")[2].getText();
 
      assert.strictEqual(place2, label0);
      
  })

});
});