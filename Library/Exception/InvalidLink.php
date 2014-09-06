<?php
namespace Westhoffswelt\LinkIt\Exception;

class InvalidLink extends \Exception
{
    public function __construct($linkfile, $source, $target)
    {
        parent::__construct(
            "The source '$source' defined in '$linkfile' is invalid."
        );
    }
}
