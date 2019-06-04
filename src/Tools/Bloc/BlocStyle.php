<?php


namespace SitPHP\Commands\Tools\Bloc;


use Exception;
use InvalidArgumentException;

class BlocStyle
{

    protected $width;
    protected $background_color = 'blue';
    protected $color = 'white';
    protected $padding_top = 1;
    protected $padding_bottom = 1;
    protected $padding_left= 3;
    protected $padding_right = 3;
    protected $border_top;
    protected $border_bottom;
    protected $border_left;
    protected $border_right;
    /**
     * @var string
     */
    protected $name;


    function __construct(string $name = null)
    {
        $this->name = $name;
    }

    function getName(){
        return $this->name;
    }

    function setWidth(int $width){
        if($width < 1){
            throw new Exception('Width should be greater than 0');
        }
        $this->width = $width;
        return $this;
    }

    function getWidth(){
        return $this->width;
    }

    function setColor(string $color){
        $this->color = $color;
        return $this;
    }

    function getColor(){
        return $this->color;
    }

    function setBackgroundColor(string $color){
        $this->background_color = $color;
        return $this;
    }

    function getBackgroundColor(){
        return $this->background_color;
    }

    function setPadding(int ...$padding){
        $padding_count  = count($padding);
        if($padding_count === 1){
            $padding = $padding[0];
            $this->setPaddingTop($padding);
            $this->setPaddingBottom($padding);
            $this->setPaddingLeft($padding);
            $this->setPaddingRight($padding);
        } else if($padding_count === 2){
            $top_bottom_padding = $padding[0];
            $left_right_padding = $padding[1];
            $this->setPaddingTop($top_bottom_padding);
            $this->setPaddingBottom($top_bottom_padding);
            $this->setPaddingLeft($left_right_padding);
            $this->setPaddingRight($left_right_padding);
        } else if($padding_count === 4){
            $this->setPaddingTop($padding[0]);
            $this->setPaddingBottom($padding[2]);
            $this->setPaddingLeft($padding[3]);
            $this->setPaddingRight($padding[1]);
        } else {
            throw new InvalidArgumentException('Invalid padding number of arguments : expected 1, 2 or 4');
        }
        return $this;
    }

    function setPaddingTop(int $padding){
        $this->padding_top = $padding;
        return $this;
    }

    function getPaddingTop(){
        return $this->padding_top;
    }

    function setPaddingBottom(int $padding){
        $this->padding_bottom = $padding;
        return $this;
    }
    function getPaddingBottom(){
        return $this->padding_bottom;
    }
    function setPaddingLeft(int $padding){
        $this->padding_left = $padding;
        return $this;
    }
    function getPaddingLeft(){
        return $this->padding_left;
    }

    function setPaddingRight(int $padding){
        $this->padding_right = $padding;
        return $this;
    }
    function getPaddingRight(){
        return $this->padding_right;
    }

    function setBorder(...$params){
        $param_count = count($params);
        if($param_count === 2){
            $this->setBorderTop($params[0]);
            $this->setBorderBottom($params[0]);
            $this->setBorderLeft($params[1]);
            $this->setBorderRight($params[1]);
        } else if($param_count === 3){
            $this->setBorderTop($params[0], $params[2]);
            $this->setBorderBottom($params[0], $params[2]);
            $this->setBorderLeft($params[1], $params[2]);
            $this->setBorderRight($params[1], $params[2]);
        }
        else {
            throw new InvalidArgumentException('Invalid border number of arguments : expected 2 or 3');
        }
        return $this;
    }
    function setBorderTop(int $width, string $color = 'black'){
        $this->border_top = [
            'color' => $color,
            'width' => $width
        ];
        return $this;
    }

    function getBorderTop(){
        return $this->border_top;
    }

    function setBorderBottom(int $width, string $color  = 'black'){
        $this->border_bottom = [
            'color' => $color,
            'width' => $width
        ];
        return $this;
    }

    function getBorderBottom(){
        return $this->border_bottom;
    }

    function setBorderLeft(int $width, string $color = 'black'){
        $this->border_left = [
            'color' => $color,
            'width' => $width
        ];
        return $this;
    }

    function getBorderLeft(){
        return $this->border_left;
    }

    function setBorderRight(int $width, string $color  = 'black'){
        $this->border_right = [
            'color' => $color,
            'width' => $width
        ];
        return $this;
    }

    function getBorderRight(){
        return $this->border_right;
    }
}