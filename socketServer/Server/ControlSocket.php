<?php

namespace app\socketServer\Server;

use React\Socket\ConnectionInterface;
use app\socketServer\Logger;
use app\socketServer\RPC;

class ControlSocket implements SubscribeInterface
{
    use EmitSubscriber;

    private $host;
    private $port;
    private $connection;
    private $socket;
    
    public function __construct($host, $port, &$loop)
    {
        $this->host = $host;
        $this->port = $port;
        $this->loop = $loop;
        
        //Creation of new TCP socket
        $socket = new \React\Socket\Server($this->host . ":" . $this->port, $this->loop);

        $socket->on('connection', function(ConnectionInterface $connection) use (&$geo_socket) {
            // store current connection
            Logger::note('Control has been connected');
            $this->connection = $connection;

            //We set a react event for every time we get data on our socket.
            $connection->on('data', function($data) use ($connection, &$hexDataGPS, &$imei, &$geo_socket) {
                Logger::note("control data is " . $data);
                $data = json_decode($data);
                $this->executeCommand($data);
            });
        });
    }

    public function sendAnswer($answer)
    {
        $answer = RPC::responseJSON(['response' => $answer]);
        if (!$this->connection) {
            // !!! Ответ может потеряться !!!
            // TODO: либо записывать ответы в базу, либо отправлять их позже
            return;
        }
        Logger::note("$answer has been answered");
        $this->connection->write($answer);
    }
    
    public function executeCommand($data)
    {
        $this->mediator->emit($this, $data->method, $data->params);
    }

}