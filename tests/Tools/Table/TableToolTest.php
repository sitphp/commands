<?php

namespace SitPHP\Commands\Tests\Tools;


use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Table\Line;
use SitPHP\Commands\Tools\Table\TableManager;
use SitPHP\Commands\Tools\Table\TableStyle;
use SitPHP\Commands\Tools\Table\TableTool;

class TableToolTest extends TestCase
{

    public function makeTable(){
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $table = new TableTool($command, new TableManager());
        return $table;
    }

    public function makeTableWithStyle(){
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        /** @var TableStyle & DoubleStub $style */
        $style = Double::mock(TableStyle::class)->getInstance();

        /** @var TableTool & DoubleStub $choice */
        $table = Double::mock(TableTool::class)->getInstance($command, new TableManager());
        $table::_method('getStyle')->return($style);

        return [$table, $style];
    }

    public function testDisplayInvalidRowShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $table = $this->makeTable();
        $table->addRow(new \stdClass());

        $table->display();
    }
    public function testDisplayRowWithInvalidCellShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $table = $this->makeTable();
        $table->addRow([new \stdClass()]);

        $table->display();
    }


    public function testGetSetColumnWidth()
    {
        $table = $this->makeTable();
        $table->setColumnWidth(2,4);
        $this->assertEquals(4, $table->getColumnWidth(2));
    }

    public function testGetSetColumnMaxWidth()
    {
        $table = $this->makeTable();
        $table->setColumnMaxWidth(2,4);
        $this->assertEquals(4, $table->getColumnMaxWidth(2));
    }

    public function testGetSetRowHeight()
    {
        $table = $this->makeTable();
        $table->setRowHeight(2,4);
        $this->assertEquals(4, $table->getRowHeight(2));
    }

    public function testGetSetRowMaxHeight()
    {
        $table = $this->makeTable();
        $table->setRowMaxHeight(2,4);
        $this->assertEquals(4, $table->getRowMaxHeight(2));
    }

    public function testGetSetPadding()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();

        $style::_method('setPadding')->count(1)->args([2]);
        $style::_method('getPadding')->count(1);

        $table->setPadding(2);
        $this->assertEquals(2, $table->getPadding());
    }

    function testSetTopBorderChars()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setTopBorderChars')->count(1)->args(['>', '>']);
        $table->setTopBorderChars('>', '>');
    }

    function testSetBottomBorderChars()
    {
        /**
        * @var TableTool & DoubleStub $table
        * @var TableStyle & DoubleStub $style
        */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setBottomBorderChars')->count(1)->args(['>', '>']);
        $table->setBottomBorderChars('>', '>');
    }

    function testSetLeftBorderChars()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setLeftBorderChars')->count(1)->args(['>', '>']);
        $table->setLeftBorderChars('>', '>');
    }

    function testSetRightBorderChars()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setRightBorderChars')->count(1)->args(['>', '>']);
        $table->setRightBorderChars('>', '>');
    }

    function testSetCellSeparationChar()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setCellSeparationChar')->count(1)->args(['>']);
        $table->setCellSeparationChar('>');
    }

    function testSetCornerChars()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setCornerChars')->count(1)->args(['>','>','>','>']);
        $table->setCornerChars('>', '>', '>', '>');
    }

    function testSetLineChars()
    {
        /**
         * @var TableTool & DoubleStub $table
         * @var TableStyle & DoubleStub $style
         */
        list($table, $style) = $this->makeTableWithStyle();
        $style::_method('setLineChars')->count(1)->args(['>','>']);
        $table->setLineChars('>', '>');
    }

    public function testGetSetFooter()
    {
        $table = $this->makeTable();
        $table->setFooter('footer');
        $this->assertEquals('footer', $table->getFooter());
    }

    public function testGetSetHeader()
    {
        $table = $this->makeTable();
        $table->setHeader('header');
        $this->assertEquals('header', $table->getHeader());
    }


    public function testIsPlaced()
    {
        $table = $this->makeTable();
        $table->placeHere();

        $this->assertTrue($table->isPlaced());
    }

    public function testSetUndefinedStyleShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $table = $this->makeTable();
        $table->setStyle('undefined');
    }

    public function testAddSetPrependClearRow()
    {
        $table = $this->makeTable();
        $table->addRow([true, 'item2']);

        $this->assertEquals('+------+-------+
| true | item2 |
+------+-------+
',$table->getDisplay());

        $table->setRows([['item1', 'item2'], [3, 'item4']]);

        $this->assertEquals('+-------+-------+
| item1 | item2 |
| 3     | item4 |
+-------+-------+
',$table->getDisplay());

        $table->prependRow(['item5', 'item6']);

        $this->assertEquals('+-------+-------+
| item5 | item6 |
| item1 | item2 |
| 3     | item4 |
+-------+-------+
',$table->getDisplay());

        $table->clear();
        $this->assertEquals('',$table->getDisplay());
    }

    /*
     * Test line display
     */

    public function testLineDisplay(){
        $line = (new Line())
            ->setLineChar('+')
            ->setLeftBorderChar('*')
            ->setRightBorderChar('*')
            ->setTitle('title is too long')
            ->setSeparationChar('&');

        $table = $this->makeTable();
        $table->setRows([
            ['item1', 'item2'],
            $line,
            ['item3', 'item4'],
            'line',
            ['item5', 'item6']
        ]);

        $this->assertEquals('+-------+-------+
| item1 | item2 |
*+ title is... +*
| item3 | item4 |
+-------+-------+
| item5 | item6 |
+-------+-------+
',$table->getDisplay());
    }

    public function testDisplayUndefinedLineShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $table = $this->makeTable();
        $table->addRow('undefined');

        $table->display();
    }

    public function testHeaderFooterDisplay(){
        $table = $this->makeTable();
        $table
            ->setRows([
                ['item1', 'item2']
            ])
            ->setHeader('header')
            ->setFooter('footer');

        $this->assertEquals('+--- header ----+
| item1 | item2 |
+--- footer ----+
',$table->getDisplay());
    }

    public function testDisplay(){
        $table = $this->makeTable();
        $table->setRows(['item1', 'item2']);
        $table->addRow(['item3', 'item4']);
        $table->display();

        $output = $table->getOutput();

        rewind($output->getHandle());

        $this->assertEquals('+-------+-------+
| item1 | item2 |
| item3 | item4 |
+-------+-------+
', stream_get_contents($output->getHandle()));
    }

    public function testPlacedDisplay()
    {
        $table = $this->makeTable();
        $table
            ->addRow(['item1', 'item2'])
            ->placeHere()
            ->display();

        $table->addRow(['item3', 'item4'])
            ->display();

        $this->assertEquals('+-------+-------+
| item1 | item2 |
| item3 | item4 |
+-------+-------+
', implode('',$table->getOutput()->getBuffer()));
    }

    public function testColumnWidthDisplay(){
        $table = $this->makeTable();
        $table
            ->addRow(['very long message ...', 'item'])
            ->setColumnWidth(1, 10)
            ->setColumnWidth(2, 10);

        $this->assertEquals('+------------+------------+
| very long  | item       |
| message .. |            |
| .          |            |
+------------+------------+
', $table->getDisplay());
    }

    public function testColumnMaxWidthDisplay(){
        $table = $this->makeTable();
        $table
            ->addRow(['very long message ...', 'item'])
            ->setColumnMaxWidth(1, 10)
            ->setColumnMaxWidth(2, 10);

        $this->assertEquals('+------------+------+
| very long  | item |
| message .. |      |
| .          |      |
+------------+------+
', $table->getDisplay());
    }

    public function testRowHeightDisplay(){
        $table = $this->makeTable();
        $table
            ->addRow(['item1', 'item2'])
            ->setRowHeight(1, 3);

        $this->assertEquals('+-------+-------+
| item1 | item2 |
|       |       |
|       |       |
+-------+-------+
', $table->getDisplay());
    }

    public function testRowMaxHeightDisplay(){
        $table = $this->makeTable();
        $table
            ->addRow(['very long message ...', 'item2'])
            ->setRowMaxHeight(1, 3)
            ->setColumnWidth(1, 5);

        $this->assertEquals('+-------+-------+
| very  | item2 |
| long  |       |
| messa |       |
+-------+-------+
', $table->getDisplay());
    }

    public function testColspanDisplay(){
        $table = $this->makeTable();
        $table->addRow(['{colspan = 2}item1', 'item2']);
        $this->assertEquals('+--------+--------+-------+
| item1           | item2 |
+--------+--------+-------+
', $table->getDisplay());
    }

    public function testRowspanDisplay(){
        $table = $this->makeTable();
        $table->addRow(['{rowspan = 2}very long message ...', 'item2']);
        $table->addRow([null, 'item4']);
        $table->setColumnWidth(1, 10);
        $this->assertEquals('+------------+-------+
| very long  | item2 |
| message .. | item4 |
| .          |       |
+------------+-------+
', $table->getDisplay());

        $table = $this->makeTable();
        $table->addRow(['{rowspan = 2}very long message ...', 'item2']);
        $table->addRow(['item3', 'item4']);
        $table->setColumnWidth(1, 10);
        $this->assertEquals('+------------+-------+
| very long  | item2 |
| message .. |       |
| .          |       |
| item3      | item4 |
+------------+-------+
', $table->getDisplay());


        $table = $this->makeTable();
        $table->addRow(['{rowspan = 2}very long message ...', 'item2']);
        $this->assertEquals('+-----------------------+-------+
| very long message ... | item2 |
|                       |       |
+-----------------------+-------+
', $table->getDisplay());
    }

    public function testColspanRowspanDisplay(){
        $table1 = $this->makeTable();
        $table1->addRow(['{rowspan = 2; colspan = 2}very long message ...', 'item']);
        $table1->addRow(['another long message ...', 'item']);
        $table1->setColumnWidth(1, 10);

        $table2 = $this->makeTable();
        $table2->addRow(['{rowspan = 2; colspan = 2}very long message ...', 'item']);
        $table2->addRow(null);
        $table2->addRow(['another long message ...', 'item']);
        $table2->setColumnWidth(1, 10);


        $expected = '+------------+------+------+
| very long message | item |
|  ...              |      |
| another lo | item |      |
| ng message |      |      |
|  ...       |      |      |
+------------+------+------+
';
        $this->assertEquals($expected, $table1->getDisplay());
        $this->assertEquals($expected, $table2->getDisplay());
    }

    function testTableIsDisplayedOnNewLine(){
        $table = $this->makeTable();
        $table->getOutput()->write('hello');
        $table->setRows(['item1', 'item2'])
            ->display();

        rewind($table->getOutput()->getHandle());

        $this->assertEquals("hello
+-------+-------+
| item1 | item2 |
+-------+-------+
", stream_get_contents($table->getOutput()->getHandle()));
    }

    function testPlacedTableIsDisplayedOnNewLine(){
        $table = $this->makeTable();
        $table->getOutput()->write('hello');
        $table->setRows(['item1', 'item2'])
            ->placeHere()
            ->display();

        rewind($table->getOutput()->getHandle());

        $this->assertEquals("hello
+-------+-------+
| item1 | item2 |
+-------+-------+
", stream_get_contents($table->getOutput()->getHandle()));
    }

    function testBorderLessStyle(){
        $table = $this->makeTable();
        $table->setRows([
            ['item1', 'item2'],
            'line',
            ['item3', 'item4']
        ])
            ->setStyle('transparent')
            ->display();

        rewind($table->getOutput()->getHandle());

        $this->assertEquals("item1 item2
           
item3 item4
", stream_get_contents($table->getOutput()->getHandle()));
    }

    function testBoxStyle(){
        $table = $this->makeTable();
        $table->setRows([
            ['item1', 'item2'],
            'line',
            ['item3', 'item4']
        ])
            ->setStyle('box')
            ->display();

        rewind($table->getOutput()->getHandle());

        $this->assertEquals('┌───────┬───────┐
│ item1 │ item2 │
├───────┼───────┤
│ item3 │ item4 │
└───────┴───────┘
', stream_get_contents($table->getOutput()->getHandle()));
    }

    function testCompactStyle(){
        $table = $this->makeTable();
        $table->setRows([
            ['item1', 'item2'],
            'line',
            ['item3', 'item4']
        ])
            ->setStyle('minimal')
            ->display();

        rewind($table->getOutput()->getHandle());

        $this->assertEquals('======= =======
 item1 | item2 
======= =======
 item3 | item4 
======= =======
', stream_get_contents($table->getOutput()->getHandle()));
    }
}
