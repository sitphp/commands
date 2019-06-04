<?php


namespace SitPHP\Commands\Tools\ProgressBar;


class ProgressBarStyle
{

    protected $format = '%steps% [%bar%] (%percents%) %message%';
    protected $width = 30;
    protected $progress_char = '=';
    protected $space_char = '.';
    protected $indicator_char = '';
    /**
     * @var string
     */
    private $name;

    function __construct(string $name = null)
    {
        $this->name = $name;
    }

    function getName(){
        return $this->name;
    }

    function setWidth(int $width){
        $this->width = $width;
    }

    function getWidth(){
        return $this->width;
    }

    function setFormat(string $format){
        $this->format = $format;
    }

    function getFormat(){
        return $this->format;
    }

    function setSpaceChar(string $char){
        $this->space_char = $char;
    }

    function getSpaceChar(){
        return $this->space_char;
    }

    function setProgressChar(string $char){
        $this->progress_char = $char;
    }

    function getProgressChar(){
        return $this->progress_char;
    }

    function setIndicatorChar(string $char){
        $this->indicator_char = $char;
    }

    function getIndicatorChar(){
        return $this->indicator_char;
    }
}