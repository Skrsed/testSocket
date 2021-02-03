<?php

namespace app\socketServer\Server;

use React\Socket\ConnectionInterface;
use app\socketServer\Logger;

abstract class AbstractConnection
{
    protected $connection;
    protected $socket;

    abstract protected function onData($data);

    public function getConnection()
    {
        return $this->connection;
    }

    public function __construct($socket, $connection)
    {
        $this->connection = $connection;
        $this->socket = $socket;
        $this->connection->on('data', function($data) {
            $this->onData($data);
        });

        $this->connection->on('drain', function () {
            Logger::note('Stream is now ready to accept more data');
        });

        $this->connection->on('close', function () {
        }, function ($error) {
            Logger::note((string) $error);
        });

        $this->connection->on('end', function () {
            
        });
        $this->connection->on('error', function (Exception $e) {
            Logger::note('Error: ' . $e->getMessage());
        });
    }


}