<?php
//On cherche Qwick et on rajoute kernel.
//require_once substr(__DIR__,0,stripos(__DIR__, 'Qwik' . DIRECTORY_SEPARATOR)+5).'/kernel/app/AutoLoader.php';
require_once '../kernel/App/AutoLoader.php';
//Initialisation du logger (dev only?)
\Qwik\Kernel\Log\Logger::getInstance();
Qwik\Kernel\App\AppManager::init(__DIR__, $_SERVER['HTTP_HOST'])->render($_SERVER['REQUEST_URI']);
