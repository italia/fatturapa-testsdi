<?php
$url=@$_SERVER['REQUEST_URI'];
$urlData=explode("/",$url);

define("HOSTNAME", getenv('SDI_HOST_MAIN').@$urlData[1]."/soap/");
define("ROOT", @$_SERVER['DOCUMENT_ROOT'] . "/".@$urlData[1]."/soap/");

define("BASENAME", getenv('SDI_HOST_MAIN').@$urlData[1]."/");
define("BASEROOT", @$_SERVER['DOCUMENT_ROOT'] . "/".@$urlData[1]."/");

define("SAFEROOT", @$_SERVER['DOCUMENT_ROOT'] . "/");
