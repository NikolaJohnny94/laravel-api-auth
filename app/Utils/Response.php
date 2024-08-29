<?php

namespace App\Utils;

class Response
{
    public $success;
    public $message;
    public $data;


    public function __construct($success, $message, $data)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }
}
