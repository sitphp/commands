<?php


namespace SitPHP\Commands\Tests;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use SitPHP\Commands\Command;
use SitPHP\Commands\Output;
use SitPHP\Formatters\FormatterManager;
use SitPHP\Resources\Stream;

class OutputTest extends TestCase
{

    /*
     * Test verbosity methods
     */
    public function testVerbosity()
    {
        $output = new Output('php://memory');
        $output->setVerbosity(2);
        $this->assertEquals(2, $output->getVerbosity());
    }
    /*
     * Test formatting methods
     */
    public function testGetSetFormatter(){
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        $output = new Output('php://memory');
        $output->setFormatter($formatter);

        $this->assertSame($formatter, $output->getFormatter());
    }

    public function testFormatting()
    {
        /** @var Output $output */
        $output= Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isatty')->return(true);

        $output = new Output('php://memory');

        $this->assertNull($output->isFormattingActive());
        $output->enableFormatting();
        $this->assertTrue($output->isFormattingActive());
        $output->disableFormatting();
        $this->assertFalse($output->isFormattingActive());
        $output->resetFormatting();
        $this->assertNull($output->isFormattingActive());
    }

    /*
     * Test stream methods
     */
    public function testPut()
    {
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('put')->count(1)->args(['message'])->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->put('message'));
    }

    public function testGetPath()
    {
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('getPath')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->getPath());
    }

    public function testGetHandle()
    {
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('getHandle')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->getHandle());
    }
    function testIsPipe(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('isPipe')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->isPipe());
    }
    function testIsatty(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('isatty')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->isatty());
    }
    function testIsFile(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('isFile')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->isFile());
    }
    function testIschar(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('isChar')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->isChar());
    }
    function testFlush()
    {
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('flush')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->flush());
    }
    function testClose(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('close')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->close());
    }
    function testIsEndOfFile(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('isEndOfFile')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return',$output->isEndOfFile());
    }
    function testPassThru(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('passThru')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->passThru());
    }
    function testSeek(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('seek')->count(1)->args([2])->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->seek(2));
    }

    function testTell(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('tell')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->tell());
    }
    function testRewind(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('rewind')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->rewind());
    }
    function testGetContents(){
        /** @var DoubleStub & Stream $stream */
        $stream = Double::mock(Stream::class)->getInstance('php://memory', 'w+');
        $stream::_method('getContents')->count(1)->return('return');

        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)
            ->allowProtectedMethods(true)
            ->getInstance('php://memory');
        $output::_method('getStream')->return($stream);

        $this->assertEquals('return', $output->getContents());

    }

    /*
     * Test buffer methods
     */
    public function testSetBuffer()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2'];
        $output->setBufferRef($array);
        $array[] = 'message 3';
        $this->assertEquals($array, $output->getBuffer());
    }

    public function testGetBufferRef()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);


        $array = ['message 1', 'message 2'];
        $output->setBufferRef($array);
        $buffer_ref = &$output->getBufferRef();
        $buffer_ref[] = 'message 3';

        $this->assertEquals(['message 1', 'message 2', 'message 3'], $output->getBuffer());
    }

    /*
     * Test write methods
     */
    public function testWrite()
    {
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        $output = new Output('php://memory');
        $output->setFormatter($formatter);
        $output->write('message 1' . "\n");
        $output->write('message 2');
        // Message should not be displayed
        $output->write('message 3', Command::VERBOSITY_VERBOSE);
        $output->write('message 3', Command::VERBOSITY_DEBUG);
        $output->write('message 4', null, false);
        // Formatting still applied with forced formatting
        $output->enableFormatting();
        $output->write("\n" . 'message 5', null, false);

        $this->assertEquals(['message 1'."\n", 'message 2', 'message 4', "\n".'message 5'], $output->getBuffer());
        $this->assertEquals(['line' => 3,'column' => 9], $output->getCursorPosition());
    }

    function testWriteEscaped(){
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        $output = new Output('php://memory');
        $output->setFormatter($formatter);

        $message = '<cs color="red">message 1</cs>';
        $output->write($message, null, null, true);

        $this->assertEquals([$message], $output->getBuffer());
    }

    function testWriteWithTtyShouldFormat(){
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isatty')->return(true);
        $output->setFormatter($formatter);

        $output->write('<cs color="red">message</cs>');
        $this->assertEquals(['[31mmessage[0m'], $output->getBuffer());
    }

    function testWriteUnknownShouldNotFormat(){
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isFile')->return(false);
        $output->setFormatter($formatter);

        $output->write('<cs color="red">message</cs>');
        $this->assertEquals(['message'], $output->getBuffer());
    }

    public function testWriteLn()
    {
        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('write')->args(['message'.PHP_EOL])->count(1);
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $output->writeLn('message');
    }

    public function testLineBreak(){
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        $output = new Output('php://memory');
        $output->setFormatter($formatter);
        $output->lineBreak(3);

        $this->assertEquals(["\n\n\n"], $output->getBuffer());
    }

    public function testClear(){
        $format_manager = new FormatterManager();
        $formatter = $format_manager->formatter('cli');
        $output = new Output('php://memory');
        $output->setFormatter($formatter);

        $output->write('message');
        $output->clear();

        $this->assertEquals([], $output->getBuffer());
    }

    public function testWriteShouldModifyBufferRef(){
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);


        $buffer_ref = &$output->getBufferRef();
        $output->write('message');

        $this->assertEquals(['message'], $buffer_ref);
    }

    public function testWriteAtBufferPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $displayed = $output->writeAtBufferPosition(1, 'message 4');

        $this->assertTrue($displayed);
        $this->assertEquals(['message 1', 'message 2message 4', 'message 3'], $output->getBuffer());
    }

    public function testWriteAtBufferPositionVerbosity()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $displayed = $output->writeAtBufferPosition(1, 'message 4', Command::VERBOSITY_VERBOSE);

        $this->assertFalse($displayed);
        $this->assertEquals(['message 1', 'message 2', 'message 3'], $output->getBuffer());
    }

    public function testPrependAtBufferPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $displayed = $output->prependAtBufferPosition(1, 'message 4');

        $this->assertTrue($displayed);
        $this->assertEquals(['message 1', 'message 4message 2', 'message 3'], $output->getBuffer());
    }

    public function testPrependAtBufferPositionVerbosity()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $displayed = $output->prependAtBufferPosition(1, 'message 4', Command::VERBOSITY_VERBOSE);

        $this->assertFalse($displayed);
        $this->assertEquals(['message 1', 'message 2', 'message 3'], $output->getBuffer());
    }

    public function testOverwriteAtBufferPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $displayed = $output->overwriteAtBufferPosition(1, 'message 4');

        $this->assertTrue($displayed);
        $this->assertEquals(['message 1', 'message 4', 'message 3'], $output->getBuffer());
    }

    public function testOverwriteAtBufferPositionVerbosity()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $displayed = $output->overwriteAtBufferPosition(1, 'message 4', Command::VERBOSITY_VERBOSE);

        $this->assertFalse($displayed);
        $this->assertEquals(['message 1', 'message 2', 'message 3'], $output->getBuffer());
    }

    public function testFileDisplayContentAtBufferPosition(){
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $buffer = ['message'];
        $output->setBufferRef($buffer);
        $output->displayContentAtBufferPosition('write', ' content', 0);

        $this->assertEquals(['message content'], $output->getBuffer());
    }

    public function testTtyDisplayContentAtBufferPosition(){
        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isatty')->return(true);

        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $buffer = ['message'];
        $output->setBufferRef($buffer);
        $output->displayContentAtBufferPosition('write', ' content', 0);

        $this->assertEquals(['message content'], $output->getBuffer());
    }

    public function testUnknownDisplayContentAtBufferPosition(){
        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isFile')->return(false);

        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $buffer = ['message'];
        $output->setBufferRef($buffer);
        $output->displayContentAtBufferPosition('write', ' content', 0);

        $this->assertEquals(['message content'], $output->getBuffer());
    }

    public function testDisplayContentWithUndefinedTypeShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $output = new Output('php://memory');
        $buffer = ['message'];
        $output->setBufferRef($buffer);
        $output->displayContentAtBufferPosition('undefined', 'content', 0);
    }

    public function testDisplayContentAtInvalidBufferPositionShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $output = new Output('php://memory');

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);
        $output->displayContentAtBufferPosition('write','message 4', 3);
    }

    public function testGetBufferSplitAtPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', 'message 2', 'message 3'];
        $output->setBufferRef($array);

        $this->assertEquals(['before' => ['message 1'], 'content'=> 'message 2', 'after'=>['message 3']], $output->getBufferSplitAtPosition(1));
    }

    /*
     * Test cursor methods
     */
    public function testGetTipCursorPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $array = ['message 1', "\n".'message 2', 'message 3'];
        $output->setBufferRef($array);

        $this->assertEquals(['line'=>2, 'column'=> 18], $output->getTipCursorPosition(1));
    }

    public function testGetCursorPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $output->writeLn("message 1");
        $output->writeLn("message 2");
        $output->writeLn("message 3");

        $this->assertEquals(['line'=>4, 'column'=> 0], $output->getCursorPosition());
        $output->moveCursorToPosition(2, 4);
        $this->assertEquals(['line'=>2, 'column'=> 4], $output->getCursorPosition());
    }

    public function testGetSetCursorPositionRef(){
        $output = new Output('php://memory');
        $position = ['line' => 3, 'column' =>4];
        $output->setCursorPositionRef($position);
        $position['line'] = 4;
        $this->assertEquals(['line' => 4, 'column' => 4],$output->getCursorPositionRef());
    }

    public function testMoveCursorToStartPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $output->writeLn("message 1");
        $output->writeLn("message 2");
        $output->writeLn("message 3");
        $output->moveCursorToPosition(2, 4);
        $output->moveCursorToStartPosition();

        $this->assertEquals(['line'=>1, 'column'=> 0], $output->getCursorPosition());
    }
    public function testMoveCursorToTipPosition()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $output->writeLn("message 1");
        $output->writeLn("message 2");
        $output->writeLn("message 3");
        $output->moveCursorToPosition(2, 4);
        $output->moveCursorToTipPosition();
        $this->assertEquals(['line'=>4, 'column'=> 0], $output->getCursorPosition());
    }

    public function testGetCursorPositionRef()
    {
        $output = new Output('php://memory');
        $formatter = (new FormatterManager())->formatter('cli');
        $output->setFormatter($formatter);

        $output->writeLn("message 1");
        $cursor_position = &$output->getCursorPositionRef();
        $cursor_position = ['line' => 3, 'column'=>0];

        $this->assertEquals(['line'=>3, 'column'=> 0], $output->getCursorPosition());
    }


    /*
     * Test ansi methods
     */

    public function testTurnUnderlineModeOn()
    {
        $output = new Output('php://memory');
        $output->turnUnderlineModeOn();
        rewind($output->getHandle());

        $this->assertEquals("\033[4m",stream_get_contents($output->getHandle()));
    }

    public function testClearEntireLine()
    {
        $output = new Output('php://memory');
        $output->clearEntireLine();
        rewind($output->getHandle());

        $this->assertEquals("\033[2K",stream_get_contents($output->getHandle()));
    }

    public function testTurnLowIntensityModeOn()
    {
        $output = new Output('php://memory');
        $output->turnLowIntensityModeOn();
        rewind($output->getHandle());

        $this->assertEquals("\033[2m",stream_get_contents($output->getHandle()));
    }


    public function testTurnInvisibleTextModeOn()
    {
        $output = new Output('php://memory');
        $output->turnInvisibleTextModeOn();
        rewind($output->getHandle());

        $this->assertEquals("\033[8m",stream_get_contents($output->getHandle()));
    }

    public function testTurnBlinkingModeOn()
    {
        $output = new Output('php://memory');
        $output->turnBlinkingModeOn();
        rewind($output->getHandle());

        $this->assertEquals("\033[5m",stream_get_contents($output->getHandle()));
    }


    public function testWindowRestore()
    {
        $output = new Output('php://memory');
        $output->windowRestore();
        rewind($output->getHandle());

        $this->assertEquals("\033[1t",stream_get_contents($output->getHandle()));
    }

    public function testClearEntireScreen()
    {
        $output = new Output('php://memory');
        $output->clearEntireScreen();
        rewind($output->getHandle());

        $this->assertEquals("\033[2J",stream_get_contents($output->getHandle()));
    }

    public function testMoveCursorToUpperLeftCorner()
    {
        $output = new Output('php://memory');
        $output->moveCursorToUpperLeftCorner();
        rewind($output->getHandle());

        $this->assertEquals("\033[H",stream_get_contents($output->getHandle()));
    }

    public function testClearLineFromCursorLeft()
    {
        $output = new Output('php://memory');
        $output->clearLineFromCursorLeft();
        rewind($output->getHandle());

        $this->assertEquals("\033[1K",stream_get_contents($output->getHandle()));
    }

    public function testAnsiMoveCursor()
    {
        $output = new Output('php://memory');
        $output->moveCursor(2,3);
        rewind($output->getHandle());

        $this->assertEquals("\033[3;2;3t",stream_get_contents($output->getHandle()));
    }

    public function testTurnBoldModeOn()
    {
        $output = new Output('php://memory');
        $output->turnBoldModeOn();
        rewind($output->getHandle());

        $this->assertEquals("\033[1m",stream_get_contents($output->getHandle()));
    }

    public function testTurnOffCharacterAttributes()
    {
        $output = new Output('php://memory');
        $output->turnOffCharacterAttributes();
        rewind($output->getHandle());

        $this->assertEquals("\033[0m",stream_get_contents($output->getHandle()));
    }
    public function testTurnReverseVideoModeOn()
    {
        $output = new Output('php://memory');
        $output->turnReverseVideoModeOn();
        rewind($output->getHandle());

        $this->assertEquals("\033[7m",stream_get_contents($output->getHandle()));
    }

    public function testMoveCursorUp()
    {
        $output = new Output('php://memory');
        $output->moveCursorUp(0);
        $output->moveCursorUp(3);
        rewind($output->getHandle());

        $this->assertEquals("\033[3A",stream_get_contents($output->getHandle()));
    }

    public function testMoveCursorDown()
    {
        /** @var Output & DoubleStub $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('isFile')->return(false);
        $output->moveCursorDown(0);
        $output->moveCursorDown(3);
        rewind($output->getHandle());

        $this->assertEquals("\033[3B",stream_get_contents($output->getHandle()));
    }

    public function testMoveCursorLeft()
    {
        $output = new Output('php://memory');
        $output->moveCursorLeft(0);
        $output->moveCursorLeft(3);
        rewind($output->getHandle());

        $this->assertEquals("\033[3D",stream_get_contents($output->getHandle()));
    }

    public function testMoveCursorRight()
    {
        $output = new Output('php://memory');
        $output->moveCursorRight(0);
        $output->moveCursorRight(3);
        rewind($output->getHandle());

        $this->assertEquals("\033[3C",stream_get_contents($output->getHandle()));
    }


    public function testDisableCursor()
    {
        $output = new Output('php://memory');
        $output->disableCursor(3);
        rewind($output->getHandle());

        $this->assertEquals("\033[?25l",stream_get_contents($output->getHandle()));
    }

    public function testClearFromCursorUp()
    {
        $output = new Output('php://memory');
        $output->clearFromCursorUp(3);
        rewind($output->getHandle());

        $this->assertEquals("\033[1J",stream_get_contents($output->getHandle()));
    }
    public function testSaveCursorPosition()
    {
        $output = new Output('php://memory');
        $output->saveCursorPosition();
        rewind($output->getHandle());

        $this->assertEquals("\0337",stream_get_contents($output->getHandle()));
    }
    public function testRestoreCursorPosition()
    {
        $output = new Output('php://memory');
        $output->restoreCursorPosition();
        rewind($output->getHandle());

        $this->assertEquals("\0338",stream_get_contents($output->getHandle()));
    }

    public function testClearFromCursorDown()
    {
        $output = new Output('php://memory');
        $output->clearFromCursorDown();
        rewind($output->getHandle());

        $this->assertEquals("\033[0J",stream_get_contents($output->getHandle()));
    }

    public function testClearLineFromCursorRight()
    {
        $output = new Output('php://memory');
        $output->clearLineFromCursorRight();
        rewind($output->getHandle());

        $this->assertEquals("\033[K",stream_get_contents($output->getHandle()));
    }

    public function testClearAll()
    {
        $output = new Output('php://memory');
        $output->clearAll();
        rewind($output->getHandle());

        $this->assertEquals("\033[2J",stream_get_contents($output->getHandle()));
    }

    public function testEnableCursor()
    {
        $output = new Output('php://memory');
        $output->enableCursor();
        rewind($output->getHandle());

        $this->assertEquals("\033[?25h",stream_get_contents($output->getHandle()));
    }

    public function testWindowSetSize()
    {
        $output = new Output('php://memory');
        $output->setWindowSize(3, 6);
        rewind($output->getHandle());

        $this->assertEquals("\033[8;6;3t",stream_get_contents($output->getHandle()));
    }

    public function testWindowGetSize()
    {
        /** @var DoubleStub & Output $output */
        $output = Double::mock(Output::class)->getInstance('php://memory');
        $output::_method('getWindowHeight')->return(3);
        $output::_method('getWindowWidth')->return(4);

        $this->assertEquals(['height'=> 3, 'width'=>4], $output->getWindowSize());

    }

    public function testWindowMinimize()
    {
        $output = new Output('php://memory');
        $output->windowMinimize();
        rewind($output->getHandle());

        $this->assertEquals("\033[2t",stream_get_contents($output->getHandle()));
    }

    public function testWindowRaise()
    {
        $output = new Output('php://memory');
        $output->windowRaise();
        rewind($output->getHandle());

        $this->assertEquals("\033[5t",stream_get_contents($output->getHandle()));
    }

    public function testWindowLower()
    {
        $output = new Output('php://memory');
        $output->windowLower();
        rewind($output->getHandle());

        $this->assertEquals("\033[6t",stream_get_contents($output->getHandle()));
    }

    public function testGetWindowWidth(){
        $output = new Output('php://memory');
        $this->assertIsInt($output->getWindowWidth());
    }

    public function testGetWindowHeight(){
        $output = new Output('php://memory');
        $this->assertIsInt(0, $output->getWindowHeight());
    }

}
