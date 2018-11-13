<?php
defined("DBDRIVER")or define('DBDRIVER', 'pgsql');
defined("DBHOST")or define('DBHOST', getenv('SDI_DB_HOST'));
defined("DBNAME")or define('DBNAME', getenv('SDI_DB_NAME'));
defined("DBUSER")or define('DBUSER', getenv('SDI_DB_USER'));
defined("DBPASS")or define('DBPASS', getenv('SDI_DB_PASS'));
define("ROOTMAIN", $_SERVER['DOCUMENT_ROOT'] . "/");
define("HOSTMAIN", getenv('SDI_HOST_MAIN'));
