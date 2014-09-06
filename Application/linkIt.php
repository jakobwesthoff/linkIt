#!/usr/bin/env php
<?php
include __DIR__ . "/linkIt/cli_question.php";
include __DIR__ . "/linkIt/invalid_link_exception.php";
include __DIR__ . "/linkIt/link.php";
include __DIR__ . "/linkIt/link_definition_filter_iterator.php";
include __DIR__ . "/linkIt/link_file_read_exception.php";
include __DIR__ . "/linkIt/link_list.php";
include __DIR__ . "/linkIt/link_realization_exception.php";

function rmtree( $path ) 
{
    $directoryIterator = new RecursiveDirectoryIterator(
        $path,
        FilesystemIterator::SKIP_DOTS
    );

    $files = new RecursiveIteratorIterator(
        $directoryIterator,
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach( $files as $file ) 
    {
        if ( $file->isDir() ) 
        {
            rmdir( $file->getRealPath() );
        }
        else if( $file->isFile() ) 
        {
            unlink( $file->getRealPath() );
        }
    }
}

function main( &$argv ) 
{
    echo "LinkIt (c) 2010 Jakob Westhoff\n";

    $options = getopt( 
        "",
        array(
            "help"
        )
    );

    // Remove all parsed options from the arguments array
    $optionsToRemove = 0;
    foreach( $options as $key => $value ) 
    {
        ++$optionsToRemove;
        if ( $value !== false ) 
        {
            ++$optionsToRemove;
        }
    }

    array_splice(
        $argv,
        1,
        $optionsToRemove
    );

    // If help has been requested display it and exit the application
    if ( array_key_exists( "help", $options ) === true ) 
    {
        printf( 
            "Usage: %s [<root>]\n",
            basename( $argv[0] )
        );
        echo "\n";
        echo "A root directory may be specified to use for processing. If none is \n";
        echo "given the current working dir will be used.\n";
        echo "\n";
        echo "Options:\n";
        echo "  --help    Display this help text.\n";
        exit( 1 );
    }

    $rootDirectory = ( count( $argv ) > 1 && realpath( $argv[1] ) !== false )
        ? ( realpath( $argv[1] ) )
        : ( getcwd() );

    $linkIterator = new LinkDefinitionFilterIterator( 
        new RecursiveIteratorIterator( 
            new RecursiveDirectoryIterator( 
                $rootDirectory
            )
        )
    );

    foreach( $linkIterator as $linkfile ) 
    {
        $baseDirectory = $linkfile->getPathInfo()->getPathname();
        $linkList = new LinkList( $linkfile );

        foreach( $linkList as $link )
        {
            if ( file_exists( $link->getTarget() ) || is_link( $link->getTarget() ) ) 
            {
                // If target is already a link to the correct file just skip 
                // this one.
                if ( is_link( $link->getTarget() ) 
                  && readlink( $link->getTarget() ) === $link->getSource() )
                {
                    printf(
                        "Already existent: %s\n",
                        $link->getTarget()
                    );
                    continue;
                }

                $question = new CliQuestion( 
                    "The target file '" . $link->getTarget() . "' exists. Do you want to overwrite it? [y/N] ",
                    array( "y", "n" ),
                    "n"
                );
                $answer = $question->ask();
                
                if( strtolower( $answer ) !== "y" ) 
                {
                    printf( 
                        "Skipping: %s\n",
                        $link->getTarget()
                    );
                    continue;
                }

                printf( 
                    "Removing previous instance: %s\n", 
                    $link->getTarget() 
                );

                if( is_dir( $link->getTarget() ) ) 
                {
                    rmtree( $link->getTarget() );
                }
                else 
                {
                    unlink( $link->getTarget() );
                }
            }

            printf( 
                "Linking: %s => %s\n",
                substr( $link->getSource(), strlen( $rootDirectory ) + 1 ),
                $link->getTarget()
            );
            
            $link->realize();
        }
    }
}

try 
{
    main( $argv );
}
catch( Exception $e ) 
{
    printf( 
        "\nAn error occured:\n%s\nProcessing aborted.\n",
        $e->getMessage()
    );
}
