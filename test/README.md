#EgoWeb Test Suite

##About
The EgoWeb Test Suite is a suite of automated tests designed to test the interviewing functionality of EgoWeb.
It is implemented in Javascript, using Selenium, WebdriverIO, and Jasmine.

You do **not** need to install or run the test suite to use EgoWeb.

##Installation

The following instructions are for a Mac. For Windows/Linux, please adjust the commands to match your environment.

* Install NodeJS by following instructions at https://nodejs.org/en/
* Download Selenium Standalone Server from http://www.seleniumhq.org/download/
* Open a Terminal window or command prompt.
* In this test/ directory, run the following command to install packages.
```
npm install
```
* Install Chomedriver and set it up on your path
  * Download Chromedriver from https://sites.google.com/a/chromium.org/chromedriver/getting-started
  * Add the location of Chromedriver to your path
* Configure WebdriverIO
  * In this test/ directory, copy wdio.conf.TEMPLATE.js to wdio.conf.js
  * In wdio.conf.js, change the configuration options starting with "CONFIG_" to match your EgoWeb installation. In particular, configuration
the EgoWeb URL, EgoWeb administrator and interviewer account username/passwords, and phantomjs path.
* Import tests
  * Import each of the studies in the suite/studies/ directory into your EgoWeb installation. Ensure that the name of the
 study in EgoWeb is the same as the file, without the ".study" extension.
  * Set the permissions of the interviewer account (configured in your wdio.conf.js) to allow them to take each of the surveys
in the test suite.


## Executing Tests
* Open a Terminal window, and in the directory where Selenium Standalone Server is installed, run the following command.
If you have a different version of Selenium, substitute the version number.
```
java -jar selenium-server-standalone-2.53.1.jar
```
* Open a 2nd Terminal window, and run the test suite. The tests can be run using a Chrome browser when in debug mode,
and in PhantomJS (headless, scriptable browser) when not in debug mode.

For normal mode using PhantomJS, run the following command.
```
./node_modules/.bin/wdio wdio.conf.js
```
For debug mode using Chrome, run the following command.
```
DEBUG=true ./node_modules/.bin/wdio wdio.conf.js
```
* The output will be shown on the command line, using the Dot reporter. This will show a green dot for every successful test spec,
and a red F for every failed test.
* The output will also be saved in JUnit format in the junitresults directory. These can be read by many software packages,
such as Build/CI systems, or use a tool to generate a HTML report.
<<<<<<< HEAD

## Executing API Tests
```
php APITest/testAPI.php <EGOWEB_URL> <API_Password> <Survey_ID>
```
=======
>>>>>>> dev
