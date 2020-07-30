## Setup

Setting up a new test environment using Mochajs and seleminum-webdriver

Confirm `node` and `npm` are installed.

Using some kind of terminal window;
`npm install selenium-webdriver`
`npm install -g mocha`

Download the Mozilla Geckodriver for firefox, to drive tests on firefox.

Download the ChromeDriver - WebDriver for Chrome. The Chrome browser will also need to be installed.
You will have to update your PATH environment variable to include the ChromeDriver.

To test, on a new terminal window; `chromedriver`
You should see something like this;
```
Starting ChromeDriver 2.41 on port 9515
Only local connections are allowed.
```

In the root folder create the file .env
Copy the lines below and update to meet your needs.

```
url='',
headless='Y'
email='' //superadmin user
password=''  //superadmin password
OS='Win' //required when testing on Win 10.
```

The env variable `WORKSPACE` is required, and is the parent of the repo folder randlex-tests

## Running tests

Each file in the tests folder contains a suite of tests. To run a suite, see the example below.  
```
npm i
wdio wdio.conf.js

```

## Headless Firefox and Chrome

Set the SELENIUM_BROWSER enviorment varilable as shown below, an example of running a suite of tests when .env flag headless = 'Y'

```
SELENIUM_BROWSER=firefox mocha tests/homepage_tests.js
```

## Reports

The command above uses the default mocha report, spec. For something different, read the reporters section in mochajs.org.
Mocha reporters display a test duration, given the default of 75ms as being slow:

1. FAST: Tests that run within half of the “slow” threshold will show the duration in green (if at all).
2. NORMAL: Tests that run exceeding half of the threshold (but still within it) will show the duration in yellow.
3. SLOW: Tests that run exceeding the threshold will show the duration in red.

## Screen Grab

Code exists that can be copied to other test suites that will save a png to the $PATH environment variable.
Place this function somewhere in the test suite file so it can be called from any test.
```
const fs = require("fs");

function getScreenshot(data, name) {
    name = name || 'ss.png';
    var screenshotPath = '$PATH';
    fs.writeFileSync(screenshotPath + name, data, 'base64');
  };
```
Use something this in the test and rename the png file to match the test.
```
await driver.takeScreenshot().then(function(data){
    getScreenshot(data, 'baselineTip.png');
});
```
