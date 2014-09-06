<?php
/**
 * Management object providing all neccessary information and methods to handle
 * Files with lists of Links to be created
 */

namespace Westhoffswelt\LinkIt;

class LinkList implements \Iterator
{
    protected $linkFile;

    protected $list;

    public function __construct( \SplFileInfo $linkFile )
    {
        $this->linkFile = $linkFile;
        if ( !$linkFile->isReadable() || !$linkFile->isFile() )
        {
            throw new LinkFileReadException( $linkFile );
        }

        $this->list = \array_map(
            function( $line )
            {
                return \array_map(
                    'trim',
                    \explode( "=>", $line, 2 )
                );
            },
            \file(
                $linkFile->getRealPath()
            )
        );
    }

    public function getBasePath()
    {
        return $this->linkFile->getPathInfo();
    }

    public function getLinkFile()
    {
        return $this->linkFile;
    }

    public function current()
    {
        return new Link(
            \current( $this->list ),
            $this
        );
    }

    public function key()
    {
        return \key( $this->list );
    }

    public function next()
    {
        \next( $this->list );
    }

    public function rewind()
    {
        \reset( $this->list );
    }

    public function valid()
    {
        return ( \current( $this->list ) !== false );
    }
}
