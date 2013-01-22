<?php
//On cherche Qwick et on rajoute kernel.
//require_once substr(__DIR__,0,stripos(__DIR__, 'Qwik' . DIRECTORY_SEPARATOR)+5).'/kernel/app/AutoLoader.php';
require_once '../kernel/App/AutoLoader.php';
Qwik\Kernel\App\AppManager::initWithPath(__DIR__)->render();
