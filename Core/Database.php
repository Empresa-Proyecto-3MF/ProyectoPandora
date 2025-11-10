<?php
class Database
{
    private $connection;
    function connectDatabase()
    {
        $host = '10.199.45.247';
        $user = 'bruno';
        $password = 'Bruno200@';
        $database = 'pandoraDB';

        $this->connection = new mysqli($host, $user, $password, $database);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
    public function getConnection()
    {
        return $this->connection;
    }
}
