<?php
require('..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
require_once '../qwik/Qwik/lib/Qwik/AutoLoader.php';

$silex = new \Silex\Application();
$silex['debug'] = true;
$silex->register(new \Qwik\ApplicationProvider(), array(
    'qwik.www' => __DIR__,
    'qwik.config' => 'dev',
));
$silex->run();
