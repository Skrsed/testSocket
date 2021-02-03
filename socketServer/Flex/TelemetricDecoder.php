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
        var_dump($this->bitmask);
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
        var_dump($this->_maskedParams);
        var_dump($hexData);
        foreach (range(0, $size - 1) as $i) {
            $packData = [];
            foreach ($this->_maskedParams as $param) {
                // var_dump($param);
                $len = $param['length'] * 2;
                $rawValue = mb_substr($hexData, $shift, $len);
                
                $binVal = hex2bin($rawValue);

                switch ($param['type']) {
                    case 'Float':
                        $packData[$param['alias']] = $rawValue;
                        break;
                    case 'U8':
                        $packData[$param['alias']] = $rawValue;
                        break;
                    case 'U16':
                        $packData[$param['alias']] = $rawValue;
                        break;
                    case 'U32':
                        $packData[$param['alias']] = $rawValue;
                        break;
                    case 'I32':
                        $packData[$param['alias']] = $rawValue;
                        break;
                }
                // var_dump($rawValue);
                $shift += $len;
            }
            // $shift += $len; // ?
            $res[] = [
                'packNumber' => $i,
                'packData' => $packData
            ];
        }

        return $res;
    }
}