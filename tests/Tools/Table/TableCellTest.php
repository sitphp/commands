<?php

namespace SitPHP\Commands\Tests\Tools;

use Doublit\TestCase;
use SitPHP\Commands\Tools\Table\Cell;

class TableCellTest extends TestCase
{

    public function testParse(){
        $table_cell = Cell::parse('{colspan = 2; rowspan = 3}content');
        $this->assertEquals(2, $table_cell->getColspan());
        $this->assertEquals(3, $table_cell->getRowspan());
        $this->assertEquals('content', $table_cell->getContent());

        $table_cell = Cell::parse('\{colspan = 2; rowspan = 3}content');
        $this->assertEquals('{colspan = 2; rowspan = 3}content',$table_cell->getContent());

        $table_cell = Cell::parse('content');
        $this->assertEquals('content',$table_cell->getContent());

    }

    public function testGetSetContent()
    {
        $table_cell = new Cell();
        $table_cell->setContent('content');
        $this->assertEquals('content', $table_cell->getContent());
    }

    public function testGetSetRowspan()
    {
        $table_cell = new Cell();
        $table_cell->setRowspan(3);
        $this->assertEquals(3, $table_cell->getRowspan());
    }


    public function testGetSetColspan()
    {
        $table_cell = new Cell();
        $table_cell->setColspan(3);
        $this->assertEquals(3, $table_cell->getColspan());
    }

}
