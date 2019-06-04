<?php

namespace SitPHP\Commands\Tools\Table;


class Cell
{

    protected $content = '';
    protected $colspan = 1;
    protected $rowspan = 1;

    static function parse(string $cell){
        $parsed = [
            'content' => $cell,
        ];
        if (isset($cell[0]) && $cell[0] === '{' && preg_match('#^{((?:\s*\w+\s*=\s*\w+\s*;?)+?)}#', $cell, $match)) {

            $parsed['content'] = mb_substr($cell, mb_strlen($match[0]));
            $cell_params = explode(';', $match[1]);
            foreach($cell_params as $cell_param){
                $cell_param_parts = explode('=', $cell_param, 2);
                if(isset($cell_param_parts[1])){
                    $parsed[trim($cell_param_parts[0])] = trim($cell_param_parts[1]);
                }
            }
        } else if(isset($cell[0]) && $cell[0] === '\\' && $cell[1] === '{'){
            $parsed['content'] = ltrim($cell, '\\');
        } else {
            $parsed['content'] = $cell;
        }

        $table_cell = new self();
        if(isset($parsed['content'])){
            $table_cell->setContent($parsed['content']);
        }
        if(isset($parsed['colspan'])){
            $table_cell->setColspan($parsed['colspan']);
        }
        if(isset($parsed['rowspan'])){
            $table_cell->setRowspan($parsed['rowspan']);
        }
        return $table_cell;
    }

    function getContent(){
        return $this->content;
    }

    function setContent(string $content){
        $this->content = $content;
        return $this;
    }

    function setColspan(int $colspan){
        $this->colspan = $colspan;
        return $this;
    }

    function getColspan(){
        return $this->colspan;
    }

    function setRowspan(int $rowspan){
        $this->rowspan = $rowspan;
        return $this;
    }

    function getRowspan(){
        return $this->rowspan;
    }

}