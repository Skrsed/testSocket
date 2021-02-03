<?php

namespace app\socketServer\Encoder;

use Crc16\Crc16;

class Commands {
    public static function setDigOut($inputNumber, $time)
    {
        return "setdigout $inputNumber $time";
    }
}