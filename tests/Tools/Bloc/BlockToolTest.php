<?php

namespace SitPHP\Commands\Tests\Tools\Bloc;

use Doubles\Double;
use Doubles\Lib\DoubleStub;
use Doubles\TestCase;
use InvalidArgumentException;
use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;
use SitPHP\Commands\Tools\Bloc\BlocStyle;
use SitPHP\Commands\Tools\Bloc\BlocTool;
use SitPHP\Commands\Tools\Bloc\Tool;
use SitPHP\Commands\Tools\Bloc\BlocManager;

class blocToolTest extends TestCase
{

    public function makebloc(){
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $request = new Request('my_command', null, 'php://temp', 'php://memory', 'php://memory');
        $command::_method('getRequest')->return($request);
        $command->setManager(new CommandManager());

        $bloc = new BlocTool($command, new BlocManager());

        return $bloc;
    }

    public function makeblocWithStyle(){
        /** @var DoubleStub & Command $command */
        $command = Double::mock(Command::class)->getInstance();
        $command::_method('getRequest')->return(new Request('my_command'));
        $command->setManager(new CommandManager());

        /** @var DoubleStub & BlocStyle $style */
        $style = Double::mock(BlocStyle::class)->getInstance();
        /** @var DoubleStub & Tool $style */
        $bloc = Double::mock(BlocTool::class)->getInstance($command, new BlocManager());
        $bloc::_method('getStyle')->return($style);

        return [$bloc, $style];
    }

    public function testSetStyleShouldFailIfStyleIsUndefined(){
        $this->expectException(InvalidArgumentException::class);
        $bloc = $this->makebloc();
        $bloc->setStyle('undefined');
    }

    public function testDisplay()
    {
        $bloc = $this->makebloc();
        $bloc->getOutput()->enableFormatting();

        $bloc->setContent('hello');
        $bloc->setBackgroundColor('red');
        $bloc->setPadding(2);
        $bloc->setBorder(2, 2, 'blue');
        $bloc->setWidth(30);
        $bloc->display();

        rewind($bloc->getOutput()->getHandle());

        $this->assertEquals('<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="white">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="white">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="white">  hello                           </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="white">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="white">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
',stream_get_contents($bloc->getOutput()->getHandle()));
    }

    public function testGetDisplay()
    {
        $bloc = $this->makebloc();
        $bloc->setContent('hello')
            ->setBackgroundColor('red')
            ->setColor('yellow')
            ->setPadding(2)
            ->setWidth(30)
            ->setBorder(2, 2, 'blue')
            ->display();

        $this->assertEquals('<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="yellow">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="yellow">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="yellow">  hello                           </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="yellow">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="red" color="yellow">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
<cs background-color="blue">  </cs><cs background-color="blue">                                  </cs><cs background-color="blue">  </cs>
',$bloc->getDisplay());
    }

    function testblocIsDisplayedOnNewLine(){
        $bloc = $this->makebloc();
        $bloc->getOutput()->write('hello');
        $bloc->setContent('hello')->display();
        rewind($bloc->getOutput()->getHandle());

        $this->assertEquals('hello
<cs background-color="blue" color="white">           </cs>
<cs background-color="blue" color="white">   hello   </cs>
<cs background-color="blue" color="white">           </cs>
', stream_get_contents($bloc->getOutput()->getHandle()));
    }

    function testPlacedblocIsDisplayedOnNewLine(){
        $bloc = $this->makebloc();
        $bloc->getOutput()->write('hello');
        $bloc->placeHere()->setContent('hello')->display();
        rewind($bloc->getOutput()->getHandle());

        $this->assertEquals('hello
<cs background-color="blue" color="white">           </cs>
<cs background-color="blue" color="white">   hello   </cs>
<cs background-color="blue" color="white">           </cs>
', stream_get_contents($bloc->getOutput()->getHandle()));
    }

    public function testSetContent()
    {
        $bloc = $this->makebloc()
            ->setBackgroundColor('blue')
            ->setColor('white')
            ->setContent('hello')
            ->setContent('hi');
        $this->assertEquals('<cs background-color="blue" color="white">        </cs>
<cs background-color="blue" color="white">   hi   </cs>
<cs background-color="blue" color="white">        </cs>
',$bloc->getDisplay());
    }

    public function testGetContent(){
        $bloc = $this->makebloc();
        $bloc->setContent('content');

        $this->assertEquals('content', $bloc->getContent());
    }

    public function testSetContentVerbosity(){
        $bloc = $this->makebloc();

        $bloc->setContent('hello', Command::VERBOSITY_VERBOSE);
        $this->assertEquals('', $bloc->getDisplay());
    }

    public function testSetContentFormat(){
        $bloc = $this->makebloc();

        $bloc->setContent('<cs>hello</cs>', null, false);
        $this->assertEquals('<cs background-color="blue" color="white">                    </cs>
<cs background-color="blue" color="white">   \<cs>hello\</cs>   </cs>
<cs background-color="blue" color="white">                    </cs>
', $bloc->getDisplay());
    }

    public function testPrependContent(){
        $bloc = $this->makebloc()
            ->setBackgroundColor('blue')
            ->setColor('white')
            ->setContent('hello')
            ->prependContent('hi ');
        $this->assertEquals('<cs background-color="blue" color="white">              </cs>
<cs background-color="blue" color="white">   hi hello   </cs>
<cs background-color="blue" color="white">              </cs>
',$bloc->getDisplay());
    }

    public function testPrependContentVerbosity(){
        $bloc = $this->makebloc();
        $bloc->prependContent('hello', Command::VERBOSITY_VERBOSE);

        $this->assertEquals('', $bloc->getDisplay());
    }

    public function testPrependContentFormat(){
        $bloc = $this->makebloc();
        $bloc->prependContent('<cs>hello</cs>', null, false);

        $this->assertEquals('<cs background-color="blue" color="white">                    </cs>
<cs background-color="blue" color="white">   \<cs>hello\</cs>   </cs>
<cs background-color="blue" color="white">                    </cs>
', $bloc->getDisplay());
    }

    public function testAddContent(){
        $bloc = $this->makeBloc()
            ->setBackgroundColor('blue')
            ->setColor('white')
            ->addContent('hello')
            ->addContent(' hi');
        $this->assertEquals('<cs background-color="blue" color="white">              </cs>
<cs background-color="blue" color="white">   hello hi   </cs>
<cs background-color="blue" color="white">              </cs>
',$bloc->getDisplay());
    }

    public function testAddContentVerbosity(){
        $bloc = $this->makeBloc();

        $bloc->addContent('hello', Command::VERBOSITY_VERBOSE);
        $this->assertEquals('', $bloc->getDisplay());
    }

    public function testAddContentFormat(){
        $bloc = $this->makeBloc();

        $bloc->addContent('<cs>hello</cs>', null, false);
        $this->assertEquals('<cs background-color="blue" color="white">                    </cs>
<cs background-color="blue" color="white">   \<cs>hello\</cs>   </cs>
<cs background-color="blue" color="white">                    </cs>
', $bloc->getDisplay());
    }

    public function testClearContent(){
        $bloc = $this->makeBloc()
            ->setBackgroundColor('blue')
            ->setColor('white')
            ->setContent('hello')
            ->clearContent();
        $this->assertEquals('',$bloc->getDisplay());
    }

    public function testClearContentVerbosity(){
        $bloc = $this->makeBloc();

        $bloc->addContent('hello');
        $bloc->clearContent(Command::VERBOSITY_VERBOSE);
        $this->assertEquals('<cs background-color="blue" color="white">           </cs>
<cs background-color="blue" color="white">   hello   </cs>
<cs background-color="blue" color="white">           </cs>
', $bloc->getDisplay());
    }

    /*
     * Test style
     */

    public function testSetPadding()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setPadding')->count(1)->args([2, 3, 4 ,5]);
        $bloc->setPadding(2,3,4,5);
    }


    public function testGetSetPaddingLeft()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setPaddingLeft')->count(1)->args([2]);
        $style::_method('getPaddingLeft')->count(1);
        $bloc->setPaddingLeft(2);
        $this->assertEquals(2, $bloc->getPaddingLeft());
    }

    public function testGetSetPaddingRight()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setPaddingRight')->count(1)->args([2]);
        $style::_method('getPaddingRight')->count(1);
        $bloc->setPaddingRight(2);
        $this->assertEquals(2, $bloc->getPaddingRight());
    }

    public function testGetSetPaddingTop()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setPaddingTop')->count(1)->args([2]);
        $style::_method('getPaddingTop')->count(1);
        $bloc->setPaddingTop(2);
        $this->assertEquals(2, $bloc->getPaddingTop());
    }

    public function testGetSetPaddingBottom()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setPaddingBottom')->count(1)->args([2]);
        $style::_method('getPaddingBottom')->count(1);
        $bloc->setPaddingBottom(2);
        $this->assertEquals(2, $bloc->getPaddingBottom());
    }

    public function testGetSetWidth()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setWidth')->count(1)->args([2]);
        $style::_method('getWidth')->count(1);
        $bloc->setWidth(2);
        $this->assertEquals(2, $bloc->getWidth());
    }


    public function testGetSetBackgroundColor()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();
        $style::_method('setBackgroundColor')->count(1)->args(["red"]);
        $style::_method('getBackgroundColor')->count(1);
        $bloc->setBackgroundColor('red');
        $this->assertEquals('red', $bloc->getBackgroundColor());
    }

    public function testGetSetColor()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setColor')->count(1)->args(["red"]);
        $style::_method('getColor')->count(1);
        $bloc->setColor('red');
        $this->assertEquals('red', $bloc->getColor());
    }


    public function testSetBorder()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setBorder')->count(1)->args([2,3]);
        $bloc->setBorder(2,3);
    }
    public function testGetSetBorderTop()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setBorderTop')->count(1)->args([2,'blue']);
        $style::_method('getBorderTop')->count(1);
        $bloc->setBorderTop(2,'blue');
        $this->assertEquals(['width'=>2, 'color'=> 'blue'], $bloc->getBorderTop());
    }
    public function testGetSetBorderBottom()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setBorderBottom')->count(1)->args([2,'blue']);
        $style::_method('getBorderBottom')->count(1);
        $bloc->setBorderBottom(2,'blue');
        $this->assertEquals(['width'=>2, 'color'=> 'blue'], $bloc->getBorderBottom());
    }
    public function testGetSetBorderLeft()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setBorderLeft')->count(1)->args([2,'blue']);
        $style::_method('getBorderLeft')->count(1);
        $bloc->setBorderLeft(2,'blue');
        $this->assertEquals(['width'=>2, 'color'=> 'blue'], $bloc->getBorderLeft());
    }
    public function testGetSetBorderRight()
    {
        /**
         * @var Tool & DoubleStub $bloc
         * @var BlocStyle & DoubleStub $style
         */
        list($bloc, $style) = $this->makeBlocWithStyle();

        $style::_method('setBorderRight')->count(1)->args([2,'blue']);
        $style::_method('getBorderRight')->count(1);
        $bloc->setBorderRight(2,'blue');
        $this->assertEquals(['width'=>2, 'color'=> 'blue'], $bloc->getBorderRight());
    }

    /*
     * Test placed
     */
    public function testPlaceHere()
    {
        $bloc = $this->makeBloc();
        $bloc->placeHere()
            ->addContent('hello')
            ->addContent('hello')
            ->display();

        rewind($bloc->getOutput()->getHandle());


        $this->assertEquals('<cs background-color="blue" color="white">                </cs>
<cs background-color="blue" color="white">   hellohello   </cs>
<cs background-color="blue" color="white">                </cs>
', stream_get_contents($bloc->getOutput()->getHandle()));
    }


    public function testIsPlaced()
    {
        $bloc = $this->makeBloc();
        $this->assertFalse($bloc->isPlaced());
        $bloc->placeHere();
        $this->assertTrue($bloc->isPlaced());
    }
}
