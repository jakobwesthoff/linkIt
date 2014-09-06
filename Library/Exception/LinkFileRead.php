<?php
namespace Westhoffswelt\LinkIt\Exception;

class LinkFileRead extends \Exception
{
    public function __construct(\SplFileInfo $file)
    {
        parent::__construct("The link file '" . $file->getPathname() . "could not be read properly.");
    }
}
