<?php

namespace App\Core;

use RuntimeException;

class Database
{
    private $connection;

    public function __construct(array $config)
    {
        $connectionString = sprintf(
            '%s:%s/%s',
            $config['host'],
            $config['port'],
            $config['service']
        );

        $this->connection = oci_connect(
            $config['user'],
            $config['password'],
            $connectionString,
            'AL32UTF8'
        );

        if ($this->connection === false) {
            throw new RuntimeException('Pripojenie k databáze zlyhalo.');
        }
    }

    public function connection()
    {
        return $this->connection;
    }

    public function __destruct()
    {
        if ($this->connection !== false) {
            oci_close($this->connection);
        }
    }
}