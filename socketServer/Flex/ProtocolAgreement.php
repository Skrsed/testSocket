<?php

namespace app\socketServer\Flex;

use app\socketServer\Security\CSd;

class ProtocolAgreement {

    const HEAD_LENGTH = 32;
    const TYPE_LENGTH = 12;

    const PROTOCOL_LENGTH = 2;
    const PROTOCOL_VER_LENGTH = 2;
    const STRUCT_VER_LENGTH = 2;
    const DATA_SIZE_LENGTH = 2;
    
    const AGREEMENT_IN = '*>FLEX';
    const AGREEMENT_OUT = '*<FLEX';

    private string $data;

    public function __construct(string $data)
    {
        $this->data = bin2hex($data);
    }

    public function getAnswer(): string
    {
        $headGen = new HeadGenerator();
        $afterHead = self::AGREEMENT_OUT . $this->getProtocol() . $this->getProtocolVer() . $this->getStructVer();

        return hex2bin($headGen->generateAnswer($this->data, $afterHead)) . $afterHead;
    }

    public function getProtocol(): string
    {
        return hex2bin(mb_substr($this->data, $this->getProtocolShift(), self::PROTOCOL_LENGTH));
    }

    public function getProtocolVer(): string
    {
        return hex2bin(mb_substr($this->data, $this->getProtocolVerShift(), self::PROTOCOL_VER_LENGTH));
    }

    public function getStructVer(): string
    {
        return hex2bin(mb_substr($this->data, $this->getStructShift(), self::STRUCT_VER_LENGTH));
    }

    public function getType(): string
    {
        return hex2bin(mb_substr($this->data, self::HEAD_LENGTH, self::TYPE_LENGTH));
    }

    public function getBitfield(): string
    {
        return hex2bin(mb_substr($this->data, $this->getBitFieldShift()));
    }

    public function getBitfieldArray(): array
    {
        return str_split($this->hex2binR(bin2hex($this->getBitfield())));
    }

    public static function is($data): bool
    {
        return self::AGREEMENT_IN === (new self($data))->getType() ? true : false;
    }

    private function getProtocolShift(): int
    {
        return self::HEAD_LENGTH + self::TYPE_LENGTH;
    }

    private function getProtocolVerShift(): int
    {
        return $this->getProtocolShift() + self::PROTOCOL_LENGTH;
    }

    private function getStructShift(): int
    {
        return $this->getProtocolVerShift() + self::PROTOCOL_VER_LENGTH;
    }

    private function getDataSizeShift(): int
    {
        return $this->getStructShift() + self::STRUCT_VER_LENGTH;
    }

    private function getBitFieldShift(): int
    {
        return $this->getDataSizeShift() + self::DATA_SIZE_LENGTH;
    }

    private function hex2binR($hex) {
        $table = ['0000', '0001', '0010', '0011', 
                  '0100', '0101', '0110', '0111',
                  '1000', '1001', '1010', 'a' => '1011', 
                  'b' => '1100', 'c' => '1101', 'e' => '1110', 
                  'f' => '1111'];
        $bin = '';
    
        for($i = 0; $i < strlen($hex); $i++) {
            $bin .= $table[strtolower(substr($hex, $i, 1))];
        }
    
        return $bin;
    }

}