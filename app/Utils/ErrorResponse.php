<?php

namespace App\Utils;

class ErrorResponse
{
    public $success;

    public $message;
    public $error_message;


    public function __construct($success, $message, $error_message)
    {
        $this->success = $success;
        $this->message = $message;
        $this->error_message = $error_message;
    }
}
