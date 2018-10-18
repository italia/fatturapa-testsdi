<?php
require_once("config.php");
require_once("SoapServerDebug.php");

# echo $_SERVER['REQUEST_URI'] . '<br/>' . PHP_EOL;
$request_uri = explode('/', $_SERVER['REQUEST_URI']);

if (sizeof($request_uri) < 4) {
    echo "404 too short path to soap endpoint";
    die;
}

$actor = $request_uri[1];
$soap = $request_uri[2];
$endpoint = $request_uri[3];

if ($soap != 'soap') {
    echo "404 wrong path to soap endpoint";
    die;
}

if (($actor != 'sdi') && !((substr($actor, 0, 2) == 'td') && strlen($actor) == 9)) {
    echo "404 wrong actor $actor";
    die;
}

# echo __FILE__ . ': [' . $actor . ', ' . $soap . ', '. $endpoint . ']<br/>' . PHP_EOL;

switch ($actor) {
    case 'sdi':
        switch ($endpoint) {
            case 'SdIRiceviFile':
                require './SdIRiceviFile/index.php';
                break;
            case 'SdIRiceviNotifica':
                require './SdIRiceviNotifica/index.php';
                break;
            default:
                echo "404 soap endpoint $endpoint not found for actor $actor";
                break;
        }
        break;
    default:
        switch ($endpoint) {
            case 'RicezioneFatture':
                require './RicezioneFatture/index.php';
                break;
            case 'TrasmissioneFatture':
                require './TrasmissioneFatture/index.php';
                break;
            default:
                echo "404 soap endpoint not $endpoint found for actor $actor";
                break;
        }
}
