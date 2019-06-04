<?php


namespace SitPHP\Commands\Tools\Table;


class Line
{

    private $title;
    private $line_char;
    private $separation_char;
    private $left_border_char;
    private $right_border_char;

    public function setTitle(string $title){
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setLineChar(string $line_char){
        $this->line_char = $line_char;
        return $this;
    }

    public function getLineChar()
    {
        return $this->line_char;
    }

    public function setSeparationChar($separation_char){
        $this->separation_char = $separation_char;
        return $this;
    }

    public function getSeparationChar()
    {
        return $this->separation_char;
    }

    public function setBorderChar($char){
        $this->setLeftBorderChar($char);
        $this->setRightBorderChar($char);
        return $this;
    }

    public function setLeftBorderChar($left_border_char){
        $this->left_border_char = $left_border_char;
        return $this;
    }

    public function getLeftBorderChar()
    {
        return $this->left_border_char;
    }

    public function setRightBorderChar($right_char){
        $this->right_border_char = $right_char;
        return $this;
    }

    public function getRightBorderChar()
    {
        return $this->right_border_char;
    }
}