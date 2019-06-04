<?php

namespace SitPHP\Commands\Events;

use SitPHP\Commands\Request;
use SitPHP\Events\Event;

class RequestEvent extends Event
{
    /**
     * @return Request
     */
    function getRequest(){
        return $this->getParam('request');
    }
}