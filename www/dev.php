<?php
require('..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
require_once '../qwik/Qwik/lib/Qwik/AutoLoader.php';
Qwik\Cms\AppManager::initWithPath(__DIR__, 'dev')->render();
