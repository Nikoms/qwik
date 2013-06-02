<?php
namespace Qwik\Module\File\Type;


use Silex\Application;

interface File
{
    /**
     * @param Application $application
     * @return string
     */
    public function render(Application $application);
}