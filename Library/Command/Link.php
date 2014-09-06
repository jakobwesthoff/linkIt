<?php
namespace Westhoffswelt\LinkIt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Westhoffswelt\LinkIt\LinkDefinitionFilterIterator;
use Westhoffswelt\LinkIt\LinkList;

class Link extends Command
{
    protected function configure()
    {
        $this
            ->setName("link")
            ->setDescription("Link files based on link.definition information")
            ->addArgument(
                "rootpath",
                InputArgument::OPTIONAL,
                "Path to scan and link (Default: CWD)"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDirectory = $input->getArgument("rootpath");
        if ($rootDirectory) {
            if (($rootDirectory = \realpath($rootDirectory)) === false) {
                $output->writeln("<error>Given directory can't be found</error>");
                return 1;
            }
        } else {
            $rootDirectory = \getcwd();
        }

        $output->writeln("<info>Reading '{$rootDirectory}'</info>");

        $linkIterator = new LinkDefinitionFilterIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $rootDirectory
                )
            )
        );

        foreach ($linkIterator as $linkfile) {
            $baseDirectory = $linkfile->getPathInfo()->getPathname();
            $linkList = new LinkList($linkfile);

            foreach ($linkList as $link) {
                if (file_exists($link->getTarget()) || is_link($link->getTarget())) {
                    // If target is already a link to the correct file just skip
                    // this one.
                    if (is_link($link->getTarget())
                        && readlink($link->getTarget()) === $link->getSource()
                    ) {
                        $output->writeln("Already in place: {$link->getTarget()}. Skipping.");
                        continue;
                    }

                    $questionHelper = $this->getHelper('question');
                    $question = new ConfirmationQuestion(
                        "The target file '{$link->getTarget()}' exists. Overwrite it? [y/N] ",
                        false
                    );
                    if ($questionHelper->ask($input, $output, $question) === false) {
                        $output->writeln("Skipping: '{$link->getTarget()}'");
                        continue;
                    }

                    $output->writeln("Removing previous instance: '{$link->getTarget()}'");

                    if (is_dir($link->getTarget())) {
                        $this->rmtree($link->getTarget());
                    } else {
                        \unlink($link->getTarget());
                    }
                }

                $relativeSource = \substr($link->getSource(), strlen($rootDirectory) + 1);
                $output->writeln(
                    "Linking: '{$relativeSource}' => '{$link->getTarget()}'"
                );

                $link->realize();
            }
        }

        return 0;
    }

    private function rmtree($path)
    {
        $directoryIterator = new \RecursiveDirectoryIterator(
            $path,
            \FilesystemIterator::SKIP_DOTS
        );

        $files = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach( $files as $file )
        {
            if ( $file->isDir() )
            {
                \rmdir( $file->getRealPath() );
            }
            else if( $file->isFile() )
            {
                \unlink( $file->getRealPath() );
            }
        }
    }

    /**
     * Returns the synopsis for the command.
     *
     * @return string The synopsis
     */
    public function getSynopsis()
    {
        global $argv;
        return trim(
            sprintf('%s [rootpath]', basename($argv[0]))
        );
    }
}
