<?php
namespace Westhoffswelt\LinkIt\Exception;

class LinkRealization extends \Exception
{
    public function __construct($source, $target)
    {
        parent::__construct(
            "The symlink between '$source' and '$target' could not be realized."
        );
    }
}
