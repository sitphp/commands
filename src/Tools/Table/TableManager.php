<?php


namespace SitPHP\Commands\Tools\Table;

use Exception;
use SitPHP\Commands\Command;
use SitPHP\Commands\ToolManager;

class TableManager extends ToolManager
{
    private $styles = [];
    private $lines = [];

    /**
     * TableToolManager constructor.
     *
     * @throws Exception
     */
    function __construct()
    {
        $this->buildStyle('default');
        $this->buildStyle('transparent')
            ->setPadding(0)
            ->clearBorderTop()
            ->clearBorderBottom()
            ->clearBorderLeft()
            ->clearBorderRight()
            ->setLineChars(' ', ' ')
            ->setCellSeparationChar(' ');

        $this->buildStyle('minimal')
            ->clearBorderLeft()
            ->clearBorderRight()
            ->setTopBorderChars('=', ' ')
            ->setBottomBorderChars('=', ' ')
            ->setLineChars('=', ' ');

        $this->buildStyle('box')
            ->setTopBorderChars('─', '┬')
            ->setBottomBorderChars('─', '┴')
            ->setLeftBorderChars('│', '├')
            ->setRightBorderChars('│', '┤')
            ->setLineChars('─', '┼')
            ->setCornerChars('┌', '┐', '┘', '└')
            ->setCellSeparationChar('│');

        $this->buildLine('line');
    }

    /**
     * @param Command $command
     * @param array $params
     * @return TableTool
     */
    function make(Command $command, ...$params)
    {
       $tool = new TableTool($command, $this);
       $tool->setStyle('default');

       return $tool;
    }


    /*
     * Style methods
     */

    /**
     * @param $name
     * @return TableStyle
     */
    function buildStyle(string $name)
    {
        return $this->styles[$name] = new TableStyle();
    }

    /**
     * @param $name
     * @return TableStyle|null
     */
    function getStyle(string $name)
    {
        return $this->styles[$name] ?? null;
    }

    /**
     * @param string $name
     */
    function removeStyle(string $name){
        unset($this->styles[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    function hasStyle(string $name)
    {
        return isset($this->styles[$name]);
    }

    /**
     * @param string $name
     * @return Line
     */
    function buildLine(string $name){
        $this->lines[$name] = new Line();
        return $this->lines[$name];
    }

    /**
     * @param $name
     * @return Line|null
     */
    function getLine(string $name)
    {
        return $this->lines[$name] ?? null;
    }

    /**
     * @param string $name
     */
    function removeLine(string $name){
        unset($this->lines[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    function hasLine(string $name)
    {
        return isset($this->lines[$name]);
    }

}