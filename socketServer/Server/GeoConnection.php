<?php

namespace app\socketServer\Server;

#region dependences
use React\Socket\ConnectionInterface;
use app\socketServer\Entities\ImeiNumber;
use app\socketServer\Database\DataStore;
use app\socketServer\Server\Requeset;
use app\socketServer\Parser\ImeiParser;
use app\socketServer\Parser\Codec8Parser;
use app\socketServer\Parser\Codec12Parser;
use app\socketServer\Encoder\Codec12Encoder;

use app\socketServer\Flex\Handshake;
use app\socketServer\Flex\ProtocolAgreement;
use app\socketServer\Flex\TPack;
use app\socketServer\Flex\APack;
use app\socketServer\Flex\HeadGenerator;
use app\socketServer\Flex\TelemetricDecoder;
use app\socketServer\Flex\FlexToAVLConvertor;

use app\socketServer\Logger;
#endregion

class GeoConnection extends AbstractConnection implements SubscribeInterface
{
    use EmitSubscriber;

    private $imei;
    private $dataBase;
    private $unresolvedId;

    private $command;

    private $_isCommandable;

    private TelemetricDecoder $_telemetricDecoder;

    private $_head;

    public function getCommand()
    {
        return $command;
    }

    public function getUnresolvedId()
    {
        return $this->unresolvedId;
    }

    public function copyState($connection)
    {
        $this->command = $connection->getCommand();
        $this->unresolvedId = null;
    }

    public function __construct($socket, &$connection, $unresolvedId)
    {
        parent::__construct($socket, $connection);
        $this->dataBase = new DataStore();
        $this->unresolvedId = $unresolvedId;
        $this->_telemetricDecoder = new TelemetricDecoder();
    }

    protected function onData($data)
    {
        // var_dump($data);
        if (Handshake::is($data)) {
            return $this->handShakeCase($data);
        }

        if (ProtocolAgreement::is($data)) {
            return $this->protocolAgreementCase($data);
        }

        if (TPack::is($data)) {
            return $this->tPackCase($data);
        }

        if (APack::is($data)) {
            return $this->aPackCase($data);
        }

        // probably unusable
        $this->connection->write('b-b-bakana!');
    }

    public function getImei()
    {
        return $this->imei;
    }

    public function handShakeCase($data): void
    {
        $hs = new Handshake($data);
        $this->imei = $hs->getImei();
        $this->_head = $hs->getHead();
        $this->connection->write($hs->getAnswer());
    }

    public function protocolAgreementCase($data): void
    {
        $pa = new ProtocolAgreement($data);
        $bitmask = $pa->getBitfieldArray();

        $this->_telemetricDecoder->setMaskedParams($bitmask);

        $this->connection->write($pa->getAnswer());
    }

    public function tPackCase($data): void
    {
        $tp = new TPack($data);
        $this->connection->write($tp->getAnswer());
    }

    public function aPackCase($data): void
    {
        $ap = new APack($data);
        $decoded = $this->_telemetricDecoder->decode($ap->getPayload(), hexdec(bin2hex($ap->getSize())));

        $convertor = new FlexToAVLConvertor();
        $avlData = [];
        foreach ($decoded as $record) {
            $avlData[] = $convertor->convert($this->getImei(), $record);
        }
        
        var_dump($avlData);
        
        /*
        Так можно отправлять команды
        $headGen = new HeadGenerator();
        $command = hex2bin($headGen->generateAnswer(bin2hex($this->_head), '*!1Y')) . '*!1Y';
        $this->connection->write($command);
        */
    }
}