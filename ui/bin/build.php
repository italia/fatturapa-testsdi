#!/usr/bin/php
<?php
// builds a static HTML page from twig templates
//
// Copyright (c) 2018, Paolo Greppi <paolo.greppi@simevo.com>
// License: BSD 3-Clause

require_once(__DIR__ . "/../vendor/autoload.php");

if (count($argv) <= 2) {
    echo "Usage: build.php template_name title\n";
    exit(-1);
}

$template = $argv[1];
$title = $argv[2];

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

echo $twig->render("$template.twig", array(
    'title' => $title,
));