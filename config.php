<?php 
@session_start();
error_reporting(0);
define('DBHOSTNAME','localhost');
define('DBUNAME','root');
define('DBPASSWORD','');
define('DBDATABASENAME','usportaldb');
require_once('DBqueries.php');
$pollObj = new dbconn(DBHOSTNAME,DBUNAME,DBPASSWORD,DBDATABASENAME);
?>