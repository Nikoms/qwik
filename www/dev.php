<?php
//On cherche Qwick et on rajoute kernel.
//require_once substr(__DIR__,0,stripos(__DIR__, 'Qwik' . DIRECTORY_SEPARATOR)+5).'/kernel/app/AutoLoader.php';
require('..'. DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php');
require_once '../qwik/Qwik/lib/Qwik/AutoLoader.php';
Qwik\Cms\AppManager::initWithPath(__DIR__)->render();
