<?php
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
