<?php
namespace Qwik\Module\File\Type;

use Silex\Application;

class Twig implements File
{


    /**
     * @var string
     */
    private $twigPath;

    public function __construct($twigPath)
    {
        $this->setTwigPath($twigPath);
    }

    public function render(Application $application)
    {
        return $application['twig']->render($this->getTwigPath(), array(
            'this' => $this,
        ));
    }

    /**
     * @param string $twigPath
     */
    public function setTwigPath($twigPath)
    {
        $this->twigPath = $twigPath;
    }

    /**
     * @return string
     */
    public function getTwigPath()
    {
        return $this->twigPath;
    }


}