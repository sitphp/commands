<?php

namespace SitPHP\Commands\Tools\Bloc;

use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\Tool;
use SitPHP\Commands\ToolManager;
use SitPHP\Commands\Tools\Section\SectionTool;

class BlocTool extends Tool
{
    /**
     * @var $section Tool
     */
    private $section;

    /**
     * @var BlocStyle
     */
    private $style;
    private $content;
    private $placed = false;
    /**
     * @var ToolManager
     */
    private $manager;


    function __construct(Command $command, BlocManager $manager)
    {
        parent::__construct($command);
        $this->manager = $manager;
        $this->setStyle('default');
    }

    function setContent(string $content, int $verbosity = null, bool $format = true){
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        if(!$format){
            $content = $this->formatContent($content);
        }
        $this->content = $content;
        return $this;
    }

    function addContent(string $content, int $verbosity = null,  bool $format = true){
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        if(!$format){
            $content = $this->formatContent($content);
        }
        $this->content = $this->content.$content;
        return $this;
    }

    function getContent(){
        return $this->content;
    }

    function prependContent(string $content, int $verbosity = null, bool $format = true){
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        if(!$format){
            $content = $this->formatContent($content);
        }
        $this->content = $content.$this->content;
        return $this;
    }

    function clearContent(int $verbosity = null){
        if($this->verbosityPasses($verbosity)){
            return $this;
        }
        $this->content = null;
        return $this;
    }

    function placeHere(){
        /** @var SectionTool section */
        $this->section = $this->tool('section')->placeHere();
        $this->placed = true;
        return $this;
    }
    function isPlaced(){
        return $this->placed;
    }

    /*
     * Style methods
     */
    function setStyle(string $name)
    {
        $style = $this->manager->getStyle($name);
        if ($style === null) {
            throw new InvalidArgumentException('Undefined style ' . $name);
        }
        $this->style = clone $style;
        return $this;
    }

    function getStyle(){
        return $this->style;
    }
    
    function setWidth(int $width){
        $this->getStyle()->setWidth($width);
        return $this;
    }

    function getWidth(){
        return $this->getStyle()->getWidth();
    }

    function setColor(string $color){
        $this->getStyle()->setColor($color);
        return $this;
    }

    function getColor(){
        return $this->getStyle()->getColor();
    }

    function setBackgroundColor(string $color){
        $this->getStyle()->setBackgroundColor($color);
        return $this;
    }
    function getBackgroundColor(){
        return $this->getStyle()->getBackgroundColor();
    }

    function setPadding(int ...$padding){
        $this->getStyle()->setPadding(...$padding);
        return $this;
    }


    function setPaddingTop(int $padding){
        $this->getStyle()->setPaddingTop($padding);
        return $this;
    }
    function getPaddingTop(){
        return $this->getStyle()->getPaddingTop();
    }

    function setPaddingBottom(int $padding){
        $this->getStyle()->setPaddingBottom($padding);
        return $this;
    }
    function getPaddingBottom(){
        return $this->getStyle()->getPaddingBottom();
    }

    function setPaddingLeft(int $padding){
        $this->getStyle()->setPaddingLeft($padding);
        return $this;
    }
    function getPaddingLeft(){
        return $this->getStyle()->getPaddingLeft();
    }

    function setPaddingRight(int $padding){
        $this->getStyle()->setPaddingRight($padding);
        return $this;
    }
    function getPaddingRight(){
        return $this->getStyle()->getPaddingRight();
    }

    function setBorder(...$border){
        $this->getStyle()->setBorder(...$border);
        return $this;
    }
    function setBorderTop(int $width, $color){
        $this->getStyle()->setBorderTop($width, $color);
        return $this;
    }
    function getBorderTop(){
        return $this->getStyle()->getBorderTop();
    }
    function setBorderBottom(int $width, $color){
        $this->getStyle()->setBorderBottom($width, $color);
        return $this;
    }
    function getBorderBottom(){
        return $this->getStyle()->getBorderBottom();
    }
    function setBorderLeft(int $width, $color){
        $this->getStyle()->setBorderLeft($width, $color);
        return $this;
    }
    function getBorderLeft(){
        return $this->getStyle()->getBorderLeft();
    }
    function setBorderRight(int $width, $color){
        $this->getStyle()->setBorderRight($width, $color);
        return $this;
    }
    function getBorderRight(){
        return $this->getStyle()->getBorderRight();
    }

    function display(int $verbosity = null){
        $bloc = $this->getDisplay();
        $this->doDisplay($bloc, $verbosity);
        return $this;
    }

    function getDisplay(){

        $bloc = '';
        if($this->content === null){
            return $bloc;
        }
        $width = $this->getStyle()->getWidth() ?? $this->resolveWidth();
        $line_total_width = $this->getStyle()->getPaddingLeft() + $this->getStyle()->getPaddingRight() + $width;
        $empty_line = $this->applyBlocLineStyle(str_repeat(' ', $line_total_width)).PHP_EOL;


        $border_left = $this->getStyle()->getBorderLeft();
        $border_right = $this->getStyle()->getBorderRight();

        // Border top
        if(null !== $border_top = $this->getStyle()->getBorderTop()){

            for($i = 1; $i<= $border_top['width'];$i++){
                if(null !== $border_left){
                    $bloc .= '<cs background-color="'.$border_left['color'].'">'.str_repeat(' ', $border_left['width']).'</cs>';
                }
                $bloc .= '<cs background-color="'.$border_top['color'].'">'.str_repeat(' ', $line_total_width).'</cs>';
                if(null !== $border_right){
                    $bloc .= '<cs background-color="'.$border_right['color'].'">'.str_repeat(' ', $border_right['width']).'</cs>';
                }
                $bloc .= PHP_EOL;
            }

        }

        // Padding top
        for($i = 1; $i<= $this->getStyle()->getPaddingTop(); $i++){
            $bloc .= $empty_line;
        }
        // Content
        $bloc.= $this->renderContent($width);

        // Padding bottom
        for($i = 1; $i<= $this->getStyle()->getPaddingBottom(); $i++){
            $bloc .= $empty_line;
        }

        if(null !== $border_bottom = $this->getStyle()->getBorderBottom()){
            for($i = 1; $i<= $border_bottom['width'];$i++){
                if(null !== $border_left){
                    $bloc .= '<cs background-color="'.$border_left['color'].'">'.str_repeat(' ', $border_left['width']).'</cs>';
                }
                $bloc .= '<cs background-color="'.$border_bottom['color'].'">'.str_repeat(' ', $line_total_width).'</cs>';
                if(null !== $border_right){
                    $bloc .= '<cs background-color="'.$border_right['color'].'">'.str_repeat(' ', $border_right['width']).'</cs>';
                }
                $bloc .= PHP_EOL;
            }
        }
        return $bloc;
    }


    protected function verbosityPasses(int $verbosity = null){
        return null !== $verbosity && $verbosity > $this->getRequest()->getVerbosity();
    }
    protected function resolveWidth(){
        $formatter = $this->getCommand()->getManager()->getFormatter();
        $resolved_width = 0;
        $content_text = $formatter->plain($this->content);
        $content_text_parts = explode("\n",$content_text);
        foreach ($content_text_parts as $content_text_part){
            $content_part_width = mb_strlen($content_text_part);
            if($content_part_width > $resolved_width){
                $resolved_width = $content_part_width;
            }
        }
        return $resolved_width;
    }

    protected function doDisplay(string $bloc, int $verbosity = null)
    {

        // Make sure bloc starts in new line
        if($this->isPlaced()){
            $before_after_buffers = $this->section->getBufferSplit();
            if(!empty($before_after_buffers['before']) && mb_substr(end($before_after_buffers['before']), -1) !== "\n"){
                $bloc = "\n".$bloc;
            }
        } else {
            $buffer = $this->getOutput()->getBuffer();
            if(!empty($buffer) && mb_substr(end($buffer), -1) !== "\n"){
                $bloc = "\n".$bloc;
            }
        }

        if ($this->isPlaced()) {
            $this->section->overwrite($bloc, $verbosity);
        } else {
            $this->getOutput()->write($bloc, $verbosity);
        }
    }

    protected function renderContent($width){

        $parser = $this->getCommand()->getManager()->getFormatter();
        $padding_left_chars = str_repeat(' ', $this->getStyle()->getPaddingLeft());
        $padding_right_chars = str_repeat(' ', $this->getStyle()->getPaddingRight());

        $bloc = '';
        $lines = explode("\n", $parser->split($this->content, $width, false, true));

        foreach($lines as $line){
            $line_text_length = mb_strlen($parser->plain($line));
            $empty_chars = str_repeat(' ', $width - $line_text_length);
            $bloc .= $this->applyBlocLineStyle($padding_left_chars.$line.$empty_chars.$padding_right_chars).PHP_EOL;
        }

        return $bloc;
    }

    protected function applyBlocLineStyle(string $text){
        $border_left = $this->getStyle()->getBorderLeft();
        $border_right = $this->getStyle()->getBorderRight();
        $background_color= $this->getStyle()->getBackgroundColor();
        $text_color= $this->getStyle()->getColor();
        $line = '';
        if(null !== $border_left){
            $line .= '<cs background-color="'.$border_left['color'].'">'.str_repeat(' ', $border_left['width']).'</cs>';
        }
        $line.='<cs background-color="'.$background_color.'" color="'.$text_color.'">'.$text.'</cs>';
        if(null !== $border_right){
            $line .= '<cs background-color="'.$border_right['color'].'">'.str_repeat(' ', $border_right['width']).'</cs>';
        }
        return $line;
    }

    protected function formatContent(string $content){
        return strtr($content, ['<'=> '\<']);
    }
}