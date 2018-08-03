<?php
require_once(__DIR__ . "/../vendor/autoload.php");

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

echo $twig->render('test.twig', array(
    'title' => 'TestUI home',
));