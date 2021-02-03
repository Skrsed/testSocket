<?php

namespace app\socketServer\Flex;

use app\socketServer\Security\CSd;

class Handshake {

    const HEAD_LENGTH = 32;
    const TYPE_LENGTH = 6;
    const IMEI_LENGTH = 30;
    const COLON_LENGTH = 2;
    const PREAMBLE_LENGTH = 8;
    const IDS_LENGTH = 8;
    const HANDSHAKE_STR_IN = '*>S';
    const HANDSHAKE_STR_OUT = '*<S';

    private string $data;

    public function __construct(string $data)
    {
        $this->data = bin2hex($data);
    }

    public function getAnswer(): string
    {
        $headGen = new HeadGenerator();

        return hex2bin($headGen->generateAnswer($this->data, self::HANDSHAKE_STR_OUT)) . self::HANDSHAKE_STR_OUT;
    }

    public function getImei(): string
    {
        return hex2bin(mb_substr($this->data, self::HEAD_LENGTH + self::TYPE_LENGTH + self::COLON_LENGTH));
    }

    public function getHead(): string
    {
        return hex2bin(mb_substr($this->data, 0, self::HEAD_LENGTH));
    }

    public function getType(): string
    {
        return hex2bin(mb_substr($this->data, self::HEAD_LENGTH, self::TYPE_LENGTH));
    }

    public static function is($data): bool
    {
        return self::HANDSHAKE_STR_IN === (new self($data))->getType() ? true : false;
    }
}