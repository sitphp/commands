<?php

namespace SitPHP\Commands;


use SitPHP\Resources\Stream;

class Input
{

    /**
     * @var Stream
     */
    protected $stream;

    protected $verbosity = Command::VERBOSITY_NORMAL;

    function __construct(string $name)
    {
        $this->stream = new Stream($name, 'r+');
    }

    function setVerbosity(int $verbosity){
        $this->verbosity = $verbosity;
    }

    function getVerbosity(){
        return $this->verbosity;
    }

    function getPath(){
        return $this->stream->getPath();
    }
    function getMode(){
        return $this->stream->getMode();
    }

    function getHandle(){
        return $this->stream->getHandle();
    }

    function isEndOfFile(){
        return $this->stream->isEndOfFile();
    }

    function readChar(){
        if(!$this->isatty()){
            throw new \RuntimeException('Cannot read character : "tty" input required, "'.$this->getType().'" found.');
        }
        return $this->stream->read(6);
    }

    function read(int $bytes){
        return $this->stream->read($bytes);
    }

    function readByte(){
        return $this->stream->readByte();
    }

    function readLine(int $bytes = null){
        return $this->stream->readLine($bytes);
    }

    function isChar(){
        return $this->stream->isChar();
    }

    function isFile(){
        return $this->stream->isFile();
    }

    function isPipe(){
        return $this->stream->isPipe();
    }

    function isatty(){
        return $this->stream->isatty();
    }

    function getType(){
        return $this->stream->getType();
    }

    function close(){
        return $this->stream->close();
    }
}