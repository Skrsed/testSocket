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
        return str_split($this->convBase(strtoupper(bin2hex($this->getBitfield())), '0123456789ABCDEF', '01'));
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

    /**
     * From https://www.php.net/manual/en/function.base-convert.php
     * function was realized by PHPCoder@niconet2k.com
     */
    private function convBase($numberInput, $fromBaseInput, $toBaseInput) {
        if ($fromBaseInput == $toBaseInput) {

            return $numberInput;
        }
        $fromBase = str_split($fromBaseInput,1);
        $toBase = str_split($toBaseInput,1);
        $number = str_split($numberInput,1);
        $fromLen=strlen($fromBaseInput);
        $toLen=strlen($toBaseInput);
        $numberLen=strlen($numberInput);
        $retval='';

        if ($toBaseInput == '0123456789') {
            $retval=0;
            for ($i = 1;$i <= $numberLen; $i++)
                $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));

            return $retval;
        }
        if ($fromBaseInput != '0123456789') {
            $base10= $this->convBase($numberInput, $fromBaseInput, '0123456789');
        }
        else {
            $base10 = $numberInput;
        }
        if ($base10<strlen($toBaseInput)) {

            return $toBase[$base10];
        }
        while($base10 != '0') {
            $retval = $toBase[bcmod($base10,$toLen)].$retval;
            $base10 = bcdiv($base10,$toLen,0);
        }

        return $retval;
    }

}