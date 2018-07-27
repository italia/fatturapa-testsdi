<?php

define("HOSTNAME","https://teamdigitale1.simevo.com/sdi/");
define("ROOT", $_SERVER['DOCUMENT_ROOT'] . "/sdi/");
define("DB_FILE","test.json");

// STATI FATTURE SDI
define("FATTURA_RICEVUTA",1);
define("FATTURA_SCARTATA",2);
define("NOTIFICA_SCARTATA",3);
define("FATTURA_ACCETTATA",4);
define("FATTURA_MANCATA_CONSEGNA",5);