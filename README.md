Egoweb
=========
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
