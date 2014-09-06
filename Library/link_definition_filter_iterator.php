<?php
/**
 * FilterIterator implementation to scan for link definition files 
 * 
 * @package LinkIt
 * @version $id$
 * @copyright 2010 Jakob Westhoff
 * @author Jakob Westhoff <jakob@php.net> 
 * @license New BSD License
 */
class LinkDefinitionFilterIterator extends FilterIterator 
{
    /**
     * Check the currently iterated element for the correct filename
     * 
     * @return bool
     */
    public function accept() 
    {
        $file = $this->getInnerIterator()->current();
        if ( !( $file instanceof SplFileInfo ) ) 
        {
            return false;
        }

        return ( $file->getBasename() == ".linkdefinition" );
    }
}
