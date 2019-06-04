<?php

namespace SitPHP\Commands\Commands;

use InvalidArgumentException;
use SitPHP\Commands\Command;

class HelpCommand extends Command
{

    function prepare(){
        $this->setDescription('Shows command help');
        $this->setArgumentInfos('command', 0, 'The command to show help for');
    }

    function handle(){

        $command_name = $this->getArgument('command');
        if($command_name === null){
            throw new InvalidArgumentException('Command name argument is required');
        }

        $command_manager = $this->getManager();
        $command = $command_manager->resolveCommand($command_name);

        if($command === null){
            throw new InvalidArgumentException('Command "'.$command_name.'" not found');
        }

        $options = $command->getAllOptionsInfos();
        $args = $command->getAllArgumentsInfos();

        // Display help message
        $this->writeLn('------------------------'.PHP_EOL.'<cs color="green">Help for "'.$command_name.'" command</cs>'.PHP_EOL.'------------------------');

        if(null !== $help = $command->getDescription()){
            $this->writeLn(PHP_EOL.'<cs color="yellow">Description</cs>');
            $this->writeLn('<cs color="cyan">'.$help.'</cs>');
        }

        // Display usage
        $usage = PHP_EOL.'<cs color="yellow">Usage</cs>'.PHP_EOL.$command_name;
        if(!empty($args)){
            $usage .= ' [ARGUMENTS ..]';
        }
        if(!empty($options)){
            $usage .= ' [OPTIONS ...]';
        }
        $this->writeLn('<cs color="cyan">'.$usage.'</cs>');

        // Display arguments help
        if(!empty($args)){
            usort($args, function ($a, $b){
                if($a['position'] == $b['position']){
                    return 0;
                }
               return  $a['position'] > $b['position'];
            });
            $this->writeLn(PHP_EOL.'<cs color="yellow">Arguments</cs>');
            $args_table = $this->table()
                ->setColumnWidth(1, 20)
                ->setStyle('transparent');
            foreach($args as $name => $arg){
                $args_table->addRow(['<cs color="cyan">'.$name.'</cs>', $arg['description']]);
            }
            $args_table->display();
        }


        // Display options help
        if(!empty($options)){
            $this->writeLn(PHP_EOL.'<cs color="yellow">Options</cs>');
            $options_table = $this->table()
                ->setStyle('transparent')
                ->setColumnWidth(1, 20);
            foreach($options as $name => $option){
                $title = '--'.$name;
                if(!empty($option['flags'])){
                    $title .= ' (-'.implode('|',$option['flags']).')';
                }
                $options_table->addRow(['<cs color="cyan">'.$title.'</cs>', $option['description']]);
            }
            $options_table->display();
        }
    }
}