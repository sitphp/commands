<?php

namespace SitPHP\Commands\Commands;

use InvalidArgumentException;
use SitPHP\Commands\Command;

class CreateCommand  extends Command
{
    function prepare(){
        $this->setArgumentInfos('path', 0, 'The path to the bundle folder');
        $this->setArgumentInfos('name', 1, 'The name of the command');
    }

    function handle(){
        if(null === $path = $this->getArgument('path')){
            throw new InvalidArgumentException('Path argument is required');
        }
        if(null === $name = $this->getArgument('name')){
            throw new InvalidArgumentException('Command name argument is required');
        }

        $path = $this->getAbsolutePath($path);
        if(!is_dir($path)){
            throw new InvalidArgumentException('Bundle folder not found in "'.$path.'"');
        }
        $class_name = $this->getClassName($name);
        $command_file = $path.'/Commands/'.$class_name.'.php';
        if(file_exists($command_file)){
            throw new InvalidArgumentException('Command already created in "'.$command_file.'"');
        }
        if(!is_dir($path.'/Commands')){
            mkdir($path.'/Commands');
        }

        $bundle_parts = array_map('ucfirst',explode('/', $path));

        $namespace = implode('\\', $bundle_parts);
        $file_content = $this->getClassContent($namespace, $class_name);

        file_put_contents($command_file, $file_content);
    }

    function getAbsolutePath($path){
        return $_SERVER['DOCUMENT_ROOT'].trim($path, '/');
    }

    function getClassName($name){
        return str_replace(' ','', ucfirst(str_replace('-',' ',$name)));
    }

    function getClassContent($namespace, $name){
        $file_content = '<?php
        
        namespace {$namespace};
        
        use SitPHP\Commands\Command;
        
        Class {$name} {
        
            function handle(){
                
     
            }
        
        }';

        return $file_content;
    }
}