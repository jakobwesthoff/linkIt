<?php
/**
 * Copyright (c) 2010, Jakob Westhoff
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. Neither the name of Jakob Westhoff nor the names of its contributors may
 *    be used to endorse or promote products derived from this software without
 *    specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Representation of one link entry
 * 
 * @package LinkIt 
 * @version $id$
 * @copyright 2010 Jakob Westhoff
 * @author Jakob Westhoff <jakob@php.net> 
 */
class Link
{
    protected $parent;

    protected $source;

    protected $target;

    public function __construct( $data, $parent )
    {
        $this->parent = $parent;

        if ( ( $this->source = $this->getRealSourcePath( $data[0] ) ) === false ) 
        {
            throw new InvalidLinkException( 
                $parent->getLinkFile()->getRealPath(),
                $data[0],
                $data[1]
            );
        }
        $this->target = $this->getRealTargetPath( $data[1] );
    }

    public function getParent() 
    {
        return $this->parent;
    }

    public function getSource() 
    {
        return $this->source;
    }

    public function getTarget() 
    {
        return $this->target;
    }

    public function realize() 
    {
        if( file_exists( $this->target ) 
        || !is_writable( dirname( $this->target ) ) 
        || !symlink( $this->source, $this->target ) ) 
        {
            throw new LinkRealizationException( $this->source, $this->target );
        }
    }

    protected function getRealSourcePath( $path ) 
    {
        // The source path is most likely relative to the basepath of the 
        // linkList definition file
        $oldWorkingDirectory = getcwd();
        chdir( $this->parent->getBasePath()->getRealPath() );
        $source = realpath( $path );
        chdir( $oldWorkingDirectory );
        return $source;
    }

    protected function getRealTargetPath( $path ) 
    {
        /*
         * Determining the real target path is a little bit tricky, as it is 
         * a file which does most likely not exist yet. Therefore realpath can not 
         * be used.
         */

        // If the Path does not start with a / or ~ character it is a realative 
        // path to the link file basepath
        if ( $path[0] !== "/" && $path[0] !== "~" ) 
        {
            $path = $this->parent->getBasePath()->getRealpath() . "/" . $path;
        }

        // Split path into segments to ease the processing
        $segments = $this->splitIntoPathSegments( $path );

        // First path segment could be ~-Character, which indicates the home 
        // directory of the current user
        if ( $segments[0] === "~" ) 
        {
            array_splice( 
                $segments,
                0,
                1,
                $this->splitIntoPathSegments( 
                    getenv( "HOME" )
                )
            );
        }

        // Handling relative path elements (.. and .) is neccessary
        return "/" . implode( "/", $this->canonicalizePathSegments( $segments ) );
    }

    protected function canonicalizePathSegments( $segments ) 
    {
        return array_reduce( 
            $segments,
            function( $reduced, $current ) 
            {
                $reduced = ( $reduced === null )
                    ? array()
                    : $reduced;

                switch( $current ) 
                {
                    case ".":
                        // Just ignore . references
                    break;
                    case "..":
                        array_pop( $reduced );
                    break;
                    default:
                        array_push( $reduced, $current );
                }

                return $reduced;
            }
        );
    }

    protected function splitIntoPathSegments( $path ) 
    {
        // Split the path into segments removing empty parts (eg. foo///bar)
        return array_values( 
            array_filter( 
                explode( "/", $path ),
                'strlen'
            )
        );
    }
}
