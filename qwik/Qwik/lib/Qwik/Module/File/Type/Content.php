<?php
namespace Qwik\Module\File\Type;

use Silex\Application;

class Content implements File
{

    /**
     * @var string
     */
    private $html;

    public function __construct($html)
    {
        $this->setHtml($html);
    }

    public function render(Application $application)
    {
        return $this->getHtml();
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }


}