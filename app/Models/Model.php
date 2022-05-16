<?php

namespace App\Models;

abstract class Model
{
    protected $dbhandler;


    public function __construct($req, $res, $arg)
    {
        $this->dbhandler = $this->dbh;
    }
    
    
}