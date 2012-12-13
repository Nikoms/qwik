<?php

namespace Qwik\Kernel\App\Page;

class PageNotFoundException extends \Exception{
    public function __construct(){
        parent::__construct('Page not found', 404);
    }
}