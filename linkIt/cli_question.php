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
class CliQuestion 
{
    protected $question;
    
    protected $answers;

    protected $default;

    public function __construct( $question, $answers, $default )
    {
        $this->question = $question;
        $this->answers  = $answers;
        $this->default  = $default;
    }

    public function ask() 
    {
        echo( $this->question );
        $answer = trim( fgets( STDIN ) );

        if ( $answer === "" ) 
        {
            return $this->default;
        }
        
        if ( !$this->isValidAnswer( $answer ) ) 
        {
            echo "Invalid answer. Please try again.\n";
            return $this->ask();
        }

        return $answer;
    }

    protected function isValidAnswer( $answer ) 
    {
        return ( array_reduce( 
            array_map( 
                function( $possibleAnswer ) use ( $answer ) 
                {
                    if ( strtolower( $possibleAnswer ) == strtolower( $answer ) ) 
                    {
                        return 1;
                    }
                    return 0;
                },
                $this->answers
            ),
            function( $reduced, $current ) 
            {
                return $reduced + $current;
            },
            0
        ) > 0 );
    }
}
