#!/usr/bin/env php
<?php
namespace Westhoffswelt\LinkIt;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . "/../vendor/autoload.php";

class LinkIt extends Application
{
    protected function getCommandName(InputInterface $input)
    {
        // We only have one command ;)
        return 'link';
    }

    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new Command\Link();
        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}

$output = new ConsoleOutput();
$output->writeln("LinkIt (c) Jakob Westhoff");

try
{
    $application = new LinkIt();
    $application->add(new Command\Link());
    $application->run();
}
catch( \Exception $e )
{
    $output->writeln("");
    $output->writeln("<error>An error occured:</error>");
    $output->writeln("<error>{$e->getMessage()}</error>");
    $output->writeln("<error>Processing aborted.</error>");
}
