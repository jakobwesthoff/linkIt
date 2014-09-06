<?php
class InvalidLinkException extends Exception
{
    public function __construct( $linkfile, $source, $target ) 
    {
        parent::__construct( 
            "The source '$source' defined in '$linkfile' is invalid." 
        );
    }
}
