<?php

namespace app\socketServer\Server;

use app\socketServer\Logger;

class DevicePack
{
    const CODEC_8 = 0x08;
    const CODEC_12 = 0x0C;
    const IMEI = 0x000F;

    private $rawData;

    public static function getType($rawData) :int
    {

        if (hexdec(substr($rawData, 0, 4)) === self::IMEI) {
            return self::IMEI;
        }

        if (hexdec(substr($rawData, 16, 2)) === self::CODEC_8) {
            return self::CODEC_8;
        }

        if (hexdec(substr($rawData, 16, 2)) === self::CODEC_12) {
            return self::CODEC_12;
        }

        Logger::note( 'Codec can\'t be recognized, id data is ' . substr($rawData, 16, 2) );
    }
}