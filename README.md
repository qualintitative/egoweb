Egoweb
=========
EgoWeb 2.0 is an ongoing project lead by RAND Corporation. EgoWeb facilitates social network data collection and processing.  In the past it has emphasized personal network data collection but can now do whole network and cognitive network data collection. EgoWeb has some basic analysis procedures but also produces basic data output for analysis in other programs outside of EgoWeb. 

EgoWeb is designed to be run on a webserver where data can be collected by interviewers with live internet connections or by respondents themselves. Respondents can be sent links to unique surveys associated with their email addresses. Interviewers can also collect data in the field without a live internet connection with iPads and (soon) android tablets. The data are stored locally temporarily and then synced with a web server / removed from the tablet.  EgoWeb can also be installed on Windows and Mac machines and be used without any webserver connection at all.

Refer to the Wiki in this repository for more information about the history of EgoWeb and installation instructions.  Please report any issues with installation or use of EgoWEb here in this repository. 

We are working on a page that provides a user guide for the software. In the meantime documentation can be made available on request.

Installation
--------------
Place all of the folders and files in the "egowebWebApp" directory in the root directory(i.e. /var/www/html)

Then create a database to store all of the Egoweb data.  Let's pretend we named it 'egoweb.'  Actually, that's a damn fine name.  After that import the latest sql dump file usually in root directory of this repository.

Open up /egowebWebApp/protected/config/main.php and edit the db credentials listed within the array 'db'

`
'db'=>array(
	'connectionString' => 'mysql:host=localhost;dbname=egoweb',
	'emulatePrepare' => true,
	'username' => 'root',
	'password' => '[some password]',
	'charset' => 'utf8',
),
`
After that you should be good given that you know where the domain is pointed and the server is properly configured.
