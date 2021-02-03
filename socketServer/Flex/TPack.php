<?php

namespace app\socketServer\Flex;

use app\socketServer\Security\CRC8;
use app\socketServer\Logger;

class TPack {


    const TYPE_LENGTH = 4;
    const EVENT_I_LENGTH = 8;
    const CRC8_LENGTH = 2;
   
    const TYPE = '~T';

    private string $data;

    public function __construct(string $data)
    {
        $this->data = bin2hex($data);
    }

    public function getAnswer(): string
    {
        $returnData = self::TYPE . $this->getEventIndex();
        Logger::note('Tpack would be answerd');
        return $returnData . hex2bin(CRC8::calculate($returnData));
    }

    public function getType(): string
    {
        return hex2bin(mb_substr($this->data, 0, self::TYPE_LENGTH));
    }

    public function getEventIndex(): string
    {
        return hex2bin(mb_substr($this->data, self::TYPE_LENGTH, self::EVENT_I_LENGTH));
    }

    public function getCRC8(): string
    {
        // !negative shift!
        return hex2bin(mb_substr($this->data, -self::CRC8_LENGTH));
    }

    public static function is($data): bool
    {
        return self::TYPE === (new self($data))->getType() ? true : false;
    }
}