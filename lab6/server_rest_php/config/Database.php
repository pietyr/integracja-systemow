<?php

namespace config;

use mysqli;

class Database
{
    private $host = "localhost";
    private $database = "world";
    private $user = "test";
    private $password = "";

    public function getConnection()
    {
        $conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        if ($conn->connect_error) {
            die("Error failed to connect to MySQL: " .
                $conn->connect_error);
        }

        return $conn;
    }
}