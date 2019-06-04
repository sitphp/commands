<?php

namespace SitPHP\Commands\Tools\Section;

use LogicException;
use SitPHP\Commands\Command;
use SitPHP\Commands\Tool;

class SectionTool extends Tool
{

    // Internal properties
    private $buffer_position;
    private $is_placed = false;

    /**
     * @var SectionManager
     */
    private $manager;


    function __construct(Command $command, SectionManager $manager)
    {
        parent::__construct($command);
        $this->manager = $manager;
    }

    function placeHere(){
        $buffer_position = $this->prepareBuffer();
        if($this->is_placed){
            $buffer_split = $this->getBufferSplit();
            $this->clear();
            $this->buffer_position = $this->resolveBufferPosition();
            $this->getOutput()->displayContentAtBufferPosition('write', $buffer_split['content'], $buffer_position);
        }
        $this->buffer_position = $buffer_position;
        $this->is_placed = true;
        return $this;
    }

    function isPlaced(){
        return $this->is_placed;
    }

    function prepend(string $message, int $verbosity = null, $width = null, $format = null){
        $this->expectPlaced();
        $this->getOutput()->prependAtBufferPosition($this->buffer_position, $message, $verbosity, $width, $format);
        return $this;
    }

    function prependLn(string $message, int $verbosity = null, $width = null, $format = null){
        return $this->prepend($message.PHP_EOL, $verbosity, $width, $format);
    }

    function write($message, int $verbosity = null, $width = null, $format = null){
        $this->expectPlaced();
        $this->getOutput()->writeAtBufferPosition($this->buffer_position, $message, $verbosity, $width, $format);
        return $this;
    }

    function writeLn(string $message, int $verbosity = null, $width = null, $format = null){
        return $this->write($message.PHP_EOL, $verbosity, $width, $format);
    }


    function overwrite(string $message, int $verbosity = null, $width = null, $format = null){
        $this->expectPlaced();
        $this->getOutput()->overwriteAtBufferPosition($this->buffer_position, $message, $verbosity, $width, $format);
        return $this;
    }

    function overwriteLn(string $message, int $verbosity = null, $width = null, $format = null){
        return $this->overwrite($message.PHP_EOL, $verbosity, $width, $format);
    }

    function lineBreak(int $count = 1, int $verbosity = null){
        $this->expectPlaced();
        $this->getOutput()->writeAtBufferPosition($this->buffer_position, str_repeat(PHP_EOL, $count), $verbosity);
    }

    function clear(int $verbosity = null)
    {
        $this->expectPlaced();
        return $this->overwrite('', $verbosity);
    }

    /**
     * Return array of buffer content before and after section position
     *
     * @return array
     */
    function getBufferSplit(){
        $this->expectPlaced();
        return $this->getOutput()->getBufferSplitAtPosition($this->buffer_position);
    }


    function moveCursorToStartPosition(){
        $this->expectPlaced();
        $before_after_buffers = $this->getOutput()->getBufferSplitAtPosition($this->buffer_position);
        $start_position = $this->getOutput()->getContentCursorPosition(implode('', $before_after_buffers['before']));
        $this->getOutput()->moveCursorToPosition($start_position['line'], $start_position['column']);
    }

    function moveCursorToTipPosition(){
        $this->expectPlaced();
        $before_after_buffers = $this->getOutput()->getBufferSplitAtPosition($this->buffer_position);
        $tip_position = $this->getOutput()->getContentCursorPosition(implode('', $before_after_buffers['before']) . $before_after_buffers['content']);
        $this->getOutput()->moveCursorToPosition($tip_position['line'], $tip_position['column']);
    }


    protected function prepareBuffer(){
        $buffer_position = $this->resolveBufferPosition();
        $output = $this->getOutput();
        $buffer = $output->getBuffer();
        $buffer[] = '';
        $output->setBufferRef($buffer);
        return $buffer_position;
    }

    protected function expectPlaced(){
        if(!$this->is_placed){
            throw new LogicException('Section should be placed with the "placeHere" method before it can be used');
        }
    }

    protected function resolveBufferPosition(){
        return count($this->getOutput()->getBuffer());
    }
}