<?php
/**
* database.inc.php
*
* Database goodness.
*
* @author Brett O'Connor
*/

/** @staticvar string MySQL hostname. */
define('SQL_HOST', 'localhost');

/** @staticvar string MySQL database name. */
define('SQL_DB', 'delanigif');

/** @staticvar string Mysql username. */
define('SQL_USERNAME','delanigif');

/** @ignore MySQL password */
define('SQL_PASSWORD','password');

/** Bring in the library that handles escaping quotes from arrays and such */
require 'quotehandle.inc.php';

/**
* Bring in ADOdb libraries.
*/
require 'adodb/adodb.inc.php';

$Db = ADONewConnection('mysqlt');

//uncomment to debug all database queries
#$Db->debug = true;

//create a persistant connection to the database now
$Db->PConnect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DB);

?>
