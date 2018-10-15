<?php
$url=@$_SERVER['REQUEST_URI'];
$urlData=explode("/",$url);

define("HOSTNAME", "https://teamdigitale3.simevo.com/".@$urlData[1]."/soap/");
define("ROOT", @$_SERVER['DOCUMENT_ROOT'] . "/".@$urlData[1]."/soap/");

define("BASENAME", "https://teamdigitale3.simevo.com/".@$urlData[1]."/");
define("BASEROOT", @$_SERVER['DOCUMENT_ROOT'] . "/".@$urlData[1]."/");

define("SAFEROOT", @$_SERVER['DOCUMENT_ROOT'] . "/");
