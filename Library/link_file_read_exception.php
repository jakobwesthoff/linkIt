<?php
class LinkFileReadException extends Exception
{
    public function __construct( $file ) 
    {
        parent::__construct( "The link file '" . $file->getPathname() . "could not be read properly." );
    }
}
