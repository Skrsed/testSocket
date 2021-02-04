<?php

namespace app\socketServer\Flex;

use app\models\TelemetricsStructureSmart;
use app\socketServer\Logger;

class TelemetricDecoder {

    private array $_bitmask;
    private ?array $_maskedParams;

    public function __construct()
    {
        $this->_maskedParams = null;
    }

    public function setMaskedParams(array $bitmask): void
    {
        $this->bitmask = $bitmask;
        foreach ($this->bitmask as $i => $v) {
            $paramRecord = TelemetricsStructureSmart::find()->where(['flex_id' => $i + 1])->asArray()->one();
            if ($v && $paramRecord) {
                $this->_maskedParams[] = array_merge($paramRecord, ['bitmaskVal' => $v, 'bitmaskId' => $i + 1]);
            }
        }
    }

    public function getMaskedParams(): ?array
    {
        return $this->_maskedParams;
    }

    public function decode($data, $size)
    {
        $hexData = bin2hex($data);
        $shift = 0;
        $res = [];

        foreach (range(0, $size - 1) as $i) {
            $packData = [];
            foreach ($this->_maskedParams as $param) {

                $len = $param['length'] * 2;
                $rawValue = mb_substr($hexData, $shift, $len);

                $binVal = hex2bin($rawValue);

                switch ($param['type']) {
                    case 'Float':
                        $decoded = unpack('G', strrev(hex2bin($rawValue)))[1];
                        break;
                    case 'U8':
                        $decoded = hexdec(bin2hex(strrev(hex2bin($rawValue))));
                        break;
                    case 'U16':
                        $decoded = hexdec(bin2hex(strrev(hex2bin($rawValue))));
                        break;
                    case 'U32':
                        $decoded = hexdec(bin2hex(strrev(hex2bin($rawValue))));
                        break;
                    case 'I32':
                        $decoded = hexdec(bin2hex(strrev(hex2bin($rawValue))));
                        break;
                }
                $packData[$param['alias']] = $decoded;

                if ($param['multiplier']) {
                    $packData[$param['alias']] *= $param['multiplier'];
                }

                if ($param['intmultiplier']) {
                    $packData[$param['alias']] *= $param['intmultiplier'];
                }
                $shift += $len;
            }

            $res[] = [
                'packNumber' => $i,
                'packData' => $packData
            ];
        }

        return $res;
    }
}