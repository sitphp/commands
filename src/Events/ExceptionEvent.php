<?php


namespace SitPHP\Commands\Events;


use Exception;
use SitPHP\Commands\Request;
use SitPHP\Events\Event;

class ExceptionEvent extends Event
{
    /**
     * @return Exception
     */
    function getException(){
        return $this->getParam('exception');
    }

    /**
     * @return Request
     */
    function getRequest(){
        return $this->getParam('request');
    }
}