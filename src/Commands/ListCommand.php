<?php

namespace SitPHP\Commands\Commands;

use SitPHP\Commands\Command;
use SitPHP\Commands\CommandManager;

class ListCommand extends Command
{

    function prepare(){
        $this->setDescription('This command provides a list of registered commands');
    }

    function handle(){
        $command_manager = $this->getManager();
        $commands = $command_manager->getAllCommands();

        $global_commands = [];
        $grouped_commands = [];
        /**
         * @var Command $command
         */
        foreach($commands as $name => $command){
            if($command->isHidden()){
                continue;
            }
            $parsed_command = $this->parseCommand($name, $command);
            if($parsed_command['group'] !== null){
                $grouped_commands[$parsed_command['group']][] = $parsed_command;
            } else {
                $global_commands[] = $parsed_command;
            }
        }

        // Sort global commands
        sort($global_commands);
        // Sort grouped commands
        ksort($grouped_commands);
        foreach ($grouped_commands as $name => $commands){
            usort($grouped_commands[$name], function ($a, $b){
                return strcmp($a['name'], $b['name']);
            });
        }
        $this->writeLn('------------------------'.PHP_EOL.'<cs color="green">Registered commands</cs>'.PHP_EOL.'------------------------'.PHP_EOL);

        $list_table = $this->table()
            ->setStyle('transparent');
        foreach ($global_commands as $global_command){
            $list_table->addRow(['<cs color="cyan">'.$global_command['name'].'</cs>', $global_command['description']]);
        }
        foreach ($grouped_commands as $group => $commands){
            $list_table->addRow(['{colspan=2}'.'<cs color="yellow">'.$group.'</cs>']);
            foreach ($commands as $command){
                $list_table->addRow(['<cs color="cyan">'.$command['name'].'</cs>', $command['description']]);
            }
        }
        $list_table->display();
    }

    protected function parseCommand(string $name, Command $command){
        $command_name_parts = explode(':', $name, 2);
        if(isset($command_name_parts[1])){
            $group = $command_name_parts[0];
            $name = $group.':'.$command_name_parts[1];
        } else {
            $group = null;
            $name = $command_name_parts[0];
        }
        /**
         * @var CommandManager $command_manager
         * @var Command $command
         */
        $description = $command->getDescription();
        return ['name' => $name, 'group' => $group, 'description' => $description];
    }
}