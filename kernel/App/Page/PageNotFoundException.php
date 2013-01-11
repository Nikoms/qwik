<?php

namespace Qwik\Kernel\App\Page;

/**
 * Exception envoyée lorsqu'on a pas trouvé de page (erreur 404)
 */
class PageNotFoundException extends \Exception{
    public function __construct(){
        parent::__construct('Page not found', 404);
    }
}