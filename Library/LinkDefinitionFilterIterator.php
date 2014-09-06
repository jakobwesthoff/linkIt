<?php
namespace Westhoffswelt\LinkIt;

/**
 * FilterIterator implementation to scan for link definition files
 */
class LinkDefinitionFilterIterator extends \FilterIterator
{
    /**
     * Check the currently iterated element for the correct filename
     *
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();
        if ( !( $file instanceof \SplFileInfo ) )
        {
            return false;
        }

        return ( $file->getBasename() == ".linkdefinition" || $file->getBasename() == "link.definition" );
    }
}
