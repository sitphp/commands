# Setup

## Basics

### Creating the bash command application

Creating your own command application is rather easy. It will allow you to register your commands and command tools and to customize everything you want.

You should first create a php bash file (in the root of your application for example). Name it "my_app" without any extension for example. This file should instantiate new `SitPHP\Commands\CommandManager` instance and run it with a request. To get started, you can just copy/paste the following code.

```php
#!/usr/bin/env php
<?php

use SitPHP\Commands\ExceptionHandler;
use SitPHP\Commands\CommandManager;
use SitPHP\Commands\Request;

require 'vendor/autoload.php';

set_time_limit(0);
set_error_handler('errorHandler');

$request = Request::createFromGlobal();
$command_manager = new CommandManager();
try{
    $command_manager->setCommand('fdsdfs','hbbjh');
    $command_manager->run($request);
} catch (\Throwable $exception){
    $exception_handler = new ExceptionHandler();
    $exception_handler->handleForConsole($command_manager, $request, $exception);
}
exit();


/**
 * @param $severity
 * @param $message
 * @param $file
 * @param $line
 * @throws ErrorException
 */
function errorHandler($severity, $message, $file, $line) {
    // This error code is not included in error_reporting
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
```
    
    
### Running your application    
    
You can now run your command application simply by typing the path to your application in your terminal.
If you have named your bash file "my_app" for example, you can run the pre-registered "list" command by typing this in your terminal :

```php
path/to/my_app list
```
    
If you got this working, you can now register your first command.

## Registering command

A command can be executed without being registered. However registering a command makes it easier to execute since you don't have to worry about it's class name. You only need to give it a name and execute it with the chosen name.

To register a command, use the `setCommand` method.    
    
```php
// In your command application file ...
$command_manager = new Console();
$command_manager->setCommand('yourcommand', \App\Commands\YourCommand::class);
```

This will allow you to run your command using a simplified syntax.

```php
vendor/bin/command yourcommand
```
        
## Events

There are four events thrown by the console that you can listen to. To listen to an event, you can use specific listener class or simply a callback.

### The request event

The "request" event is thrown with every command request. To listen to this event, use the `addOnRequestListener` method.

With a callback listener :
    
```php
use SitPHP\Commands\Events\RequestEvent;

// In your command application file ...
$command_manager->addOnRequestListener(function(RequestEvent event){
    $request = $event->getRequest();
    // do something with the request ...
});
```

Or with a listener class :

```php
namespace App\Listeners

use SitPHP\Commands\Events\RequestEvent;
use SitPHP\Events\Listener;

class YourListener extends Listener
{
    function handle(RequestEvent $event){
        $event->getRequest();
        // do something with the request ...
    }
}
```

and then register your listener
    
```
// In your command application file ...
$command_manager->addOnRequestListener(\App\Listeners\YourListener::class);
```
    
### The before command event 

The "before command" event is thrown after the command is created but before it is executed. To listen to this event, use the `addBeforeCommandListener` method.

```php
use SitPHP\Commands\Events\CommandEvent;

// In your command application file ...
$command_manager->addBeforeCommandListener(function(Command event){
    $command = $event->getCommand();
    // do something with the command ...
});
```
    
You can also specify which commands you wish to listen to in the 2nd argument :

```php
use SitPHP\Commands\Events\CommandEvent;

// In your command application file ...
$command_manager->addBeforeCommandListener(function(Command event){
    $command = $event->getCommand();
    // do something with the command ...
}, ['my_command', 'my_other_command']);
```
    
### The after command event

The "after command" event is thrown after the command is executed. To listen to this event, use the `addAfterCommandListener` method. It works in the same way as the previously described for the before command event.

### The exception event

The "exception" event is thrown when an exception is thrown. To listen to this event, use the `addOnExceptionListener` method.
    
```php
use SitPHP\Commands\Events\ExceptionEvent;

// In your command application file ...
$command_manager->addOnExceptionListener(function(Exception event){
    $exception = $event->getException();
    // do something with the exception ...
    
    $request = $event->getRequest();
    // do something with the request ...
});
```

## Creating a custom tool

You can create you own command tools by creating two classes :
 - a manager class to create your tool instance 
 - and a tool class for your tool logic. 

The tool class should extend the `SitPHP\Commands\Tool` class. Inside your tool class you have access to the command with the `getCommand` method, the request with the `getRequest` method, the tool input (which may differ from the request input) with the `getInput` method, the tool output (which may differ from the request output) with the `getOutput` method and the tool error output (which may differ from the request error output) with the `getErrorOutput` method and the `tool` method which give you access to other tools.

First create your tool class which you extend the `\SitPHP\Commands\Tool` class :

```php
class YourTool extends \SitPHP\Commands\Tool{
    
    function doSomething(){
        $output = $this->getOutput();
        $output->writeLn('doing something ...');
    }
    
}
```

Then create your tool manager class. It should extend the `SitPHP\Commands\ToolManager` class and implement the `make` method.
    
```php
class YourToolManager extends \SitPHP\Commands\ToolManager{
        
    function make(Command $command){
        $your_tool = new YourTool($command);
    }

}
```
    
After creating your tool class and your tool manager class, you should register your tool manager inside to command manager.
    
```php
// In your command application file ...
$command_manager->setToolManager('your_tool', YourToolManager::class);
```

You can then use your newly created tool inside your command with the `tool` method.

```php
// In the "handle" method of your command class ...
function handle(){
    $your_tool = $this->tool('your_tool');
    $your_tool->doSomething();
}
```        
    
## Testing commands

The command manager also allows you to test commands with `test` method. You can define question answers with the `setAnswer` method. 

Once your test is prepared, you can then run your command with the `run` method. The first argument takes the name or the class of the the command to test. And the second argument takes an array of arguments and options to pass to the command. It will return the output of the command you are testing.

```php
class MyCommandTest extends TestCase {
    // You must implement this yourself
    $command_manager = $this->getCommandManager();
    
    $command_test = $command_manager->test();
    $command_test->setAnswer('What is your name ?', 'Alex');
    $output = $command_test->call('your_command', ['argument' , '--option']);
    
    $this->assertContains('Your name is Alex', $response);
}
```