<?php


/**
* RequestErrorException
*/
class RequestErrorException extends Exception
{
    public function __construct($message, $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        $string = "<html>
                       <head>
                            <title>Error: {$this->code}</title>
                       </head>
                       <body>
                            <h1>Error: {$this->code}</h1>
                            <hr>
                            <p>{$this->message}</p>
                       </body>
                    </html>";
         return $string;
    }

    public function viewError()
    {
        Web::httpHeader($this->code);
        echo $this;
    }

    public function errorCode()
    {
        return $this->code;
    }

}


