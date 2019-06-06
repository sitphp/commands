# Quick start

## Creating a command

To build a new command, you should create a new class extending the `\SitPHP\Commands\Command` class in the "Commands" folder of your library or application. This class should implement the `handle` method. Let's create, for example, a "YourCommand" class :

```php
namespace App\Commands;

class YourCommand extends \SitPHP\Commands\Command {

    function handle(){
        $this->write('hello');
    }

}
```

## Running a command 

To run your command, you should use the `command` application located in the `/vendor/bin` folder. To run our previously created "YourCommand" command for example, use the shorthand notation (Namespace:CommandName) : 

```bash
vendor/bin/command App:YourCommand
```

or use the full path (Class name with slashes "/" instead of backslashes "\")

```bash
vendor/bin/command App/Commands/YourCommand
```
    
## Writing text messages

To write a message in your terminal, use the `write` or the `writeLn` method. The `writeLn` method will write the message on a new line whereas the `write` method will write the message on the same line.
 
You can also use the `lineBreak` method to display line breaks. This method can receive an integer argument to specify how many line breaks you wish to write.
    
```php
namespace App\Commands;

class YourCommand extends \SitPHP\Commands\Command {

    function handle(){
        $this->write('Hello,');
        
        // Single line break
        $this->lineBreak();

        $this->write('I am ');
        $this->write('Alex');
        
        // Double line break
        $this->lineBreak(2);

        $this->write('I code with PHP');
    }

}
```
    
![command write](img/command_write.png)


## Arguments and options

In order to retrieve options and arguments passed to your command, you must first register them in the `prepare` method of your command class. 
- To register an argument, use the `setArgumentInfos` method with name of the argument and its position (0 if it is the first argument, 1 if it is the second argument and so on ...)
- To register an option, use the `setOptionInfos`  method with the name of the option.
Here, for example, we will register "name" argument and a "color" option.

  
    
```php
// In your command class ...

function prepare()
{
   // Register "name" argument at position "0"
   $this->setArgumentInfos('name', 0);

   // Register "color" option
   $this->setOptionInfos('color');
}

function handle()
{
   // Retrieve name argument value
   $name = $this->getArgument('name');
   if ($name === null) {
       throw new \Exception('The "name" argument is required');
   }
   $message = 'My name is ' . $name;
   
   // Retrieve color option value
   $color = $this->getOption('color');
   if ($color !== null) {
       $message .= ' and I like the ' . $color . ' color';
   }

   $this->writeLn($message);
}
```

To send the arguments to your command, just type their value in your terminal. Options should preceded with two hyphens (ex : `--color`). Options can take values like so `--color=red`. If no value is specified, the option value will be `true`.

You could run our previous command typing something like this in the terminal :

```bash
vendor/bin/command App:YourCommand Alex --color=red
```
    
This would write : "My name is Alex and I like the red color".

## Styling

Anything written in the terminal can be easily styled using the `<cs>` tag.

- You can change the color of your text with the `color` attribute. Available colors are :  'black','white','red','green','yellow','blue','purple','cyan','light_grey','dark_grey','light_red','light_green','light_yellow','light_blue','pink','light_cyan'.
- You can change the background color of your text with the `background-color` attribute. Available colors are : 'black','white','red','green','yellow','blue','purple','cyan','light_grey',dark_grey','light_red','light_green','light_yellow','light_blue','pink','light_cyan'.
- You can make your text bold with the `bold` parameter of the `style` attribute
- You can highlight your text with `highlight` parameter of the `style` attribute
- You can underline your text with `underline` parameter of the `style` attribute
- You make your text blink with `blink` parameter of the `style` attribute (some terminals do not support blink)

Here are a few styling examples :
    
```php
// In the "handle" method of your command class ...
$this->writeLn('This will display in <cs color="blue">blue</cs>');
$this->writeLn('This will display <cs style="bold;highlight">highlighted and bold</cs>');
$this->writeLn('This will display <cs color="white" background-color="blue">with a white text in a blue background</cs>');
```
![command style](img/command_style.png)


## Tools

This package comes with some useful tools. It's also easy to build your own if you are using your own command application.

### Bloc tool

The bloc tool can display content in a box. A bloc is created with the `bloc` method and displayed with the `display` method. The width of the bloc will automatically adjust to the width of the content.

```php
// In the "handle" method of your command class ...
$this->bloc('I am a simple bloc ...')
    ->display();
```   

### Progress bar tool

To create a progress bar, use the `progress` method with an argument to specify the number of steps of your progress bar. Then display it with the `display` method. You can then move the progress line forward with the `progress` method. 
You might want to "stick" your progress bar with the `placeHere` method so that it does'nt show on a new line on each progress.
    
```php
// In the "handle" method of your command class ...

// Create a 5 steps progress bar
$progress_bar = $this->progressBar(5)
    ->placeHere()
    ->display();

for($i = 1; $i <= 5; $i++){
    sleep(1);
    $progress_bar->progress();
}
```

![command progress bar](img/progress_basic.gif)

### The question tool

The question tool allows to ask for user input. Use the `question` method to create a new question. This method can take two arguments : the question prompt and an array of autocomplete values.

```php
// In the "handle" method of your command class ...
function handle(){
    $genres = ['pop', 'rock', 'hip hop', 'classical'];
    $genre = $this->question('Which music genre do you like ?', $genres)
        ->ask();
    
    $this->lineBreak();
    $this->writeLn('Your favorite music genre is : '.$genre);
}
```
    
![command question](img/question_basic.gif)


### The choice tool

The choice tool allows you to ask the user to choose within a predefined set of choices. Use the `choice` method to create a new choice and ask for the user choice using the `ask` method. You might also want to let user quit without answering with the `enableQuit` method. The choice question will be re-displayed until the user has given a correct choice or has quit if possible.
When the user chooses to quit, the choice method will return `null`.

The `choice` method can take up to three arguments : an array of choices, the question prompt, and the title. 
    
```php
// In the "handle" method of your command class ...
function handle(){
    $choices = ['red', 'blue', 'green'];
    $color_index = $this->choice($choices, 'Which color do you like best ?', 'Colors')
        ->enableQuit()
        ->ask();
        
    if($color_index !== null){   
        $this->lineBreak(); 
        $this->writeLn('You like the '.$choices[$color_index].' color the best');
    }
}
```

![command choice](img/choice_basic.gif)

### Section tool

The section is used to update or move content at a predefined position on the screen. You can create a section with the `section` method and place it where you decide with the `placeHere` method. Every content in the section will written at the placed position. Here is an example to illustrate this :

```php
// In the "handle" method of your command class ...
$this->writeLn('This goes before');
$section = $this->section()->placeHere();
$this->writeLn('This goes after');

$section->writeLn('This goes in the <cs color="blue">middle</cs>');
sleep(1);
$section->overwriteLn('This goes in the <cs color="red">middle</cs>');
```

![command section](img/section_basic.gif)

### Table tool

You can use the table tool to display content organized in rows and columns. Use the `table` method to create a table. Then define every table row in an array. You can also insert a table with a `line` item.

```php
// In the "handle" method of your command class ...
$this->table([
    ['<cs style="bold">Animal</cs>', '<cs style="bold">Classification</cs>'],
    'line',
    ['elephant', 'mammal'],
    ['parrot', 'bird']
])->display();
```

![table command](img/table_basic.png)   