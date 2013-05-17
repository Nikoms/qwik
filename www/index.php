<?php
require('..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
require_once '../qwik/Qwik/lib/Qwik/AutoLoader.php';



$qwik = new \Qwik\Application(__DIR__, 'dev', new \Silex\Application());
$qwik->run();
