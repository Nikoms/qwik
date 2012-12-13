<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikoms
 * Date: 14/11/12
 * Time: 20:27
 * To change this template use File | Settings | File Templates.
 */


namespace Qwik\Kernel\App\Routing;

class Response {

    private $content;
    private $targetUrl;
    public function __construct(){

    }

    public function setContent($content){
        $this->content = $content;
    }

    public function getContent(){
        return $this->content;
    }
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->targetUrl = $url;

        $this->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        //$this->headers->set('Location', $url);
        header('Location: ' . $url);
        return $this;
    }

}