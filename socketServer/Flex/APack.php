<?php

namespace app\socketServer\Flex;

use app\socketServer\Security\CRC8;
use app\socketServer\Logger;

class APack {

    const TYPE_LENGTH = 4;
    const SIZE_LENGTH = 2;
    const CRC8_LENGTH = 2;
   
    const TYPE = '~A';

    private string $data;

    public function __construct(string $data)
    {
        $this->data = bin2hex($data);
    }

    public function getAnswer(): string
    {
        $returnData = self::TYPE . $this->getSize();
        Logger::note('Apack would be answerd');
        return $returnData . hex2bin(CRC8::calculate($returnData));
    }

    public function getType(): string
    {
        return hex2bin(mb_substr($this->data, 0, self::TYPE_LENGTH));
    }

    public function getSize(): string
    {
        return hex2bin(mb_substr($this->data, self::TYPE_LENGTH, self::SIZE_LENGTH));
    }

    public function getPayload()
    {
        return hex2bin(mb_substr($this->data, self::TYPE_LENGTH + self::SIZE_LENGTH,
            strlen($this->data) - (self::TYPE_LENGTH + self::SIZE_LENGTH + self::CRC8_LENGTH)
        ));
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