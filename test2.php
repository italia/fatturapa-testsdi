<?php

require 'config.php';

require 'SdIRiceviFile/autoload.php';
require_once("SdIRiceviFile/SdIRiceviFileHandler.php");

$test = new SdIRiceviFileHandler();
$NomeFile = 'cuccia.xml';
$File = base64_encode('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
$fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);

$test->RiceviFile($fileSdIBase);