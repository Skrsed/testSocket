<?php

namespace app\socketServer\Security;

class CRC8
{
    public static function calculate($data)
    {
        $crc = 0xFF;
        foreach (unpack('C*', $data) as $symbol) {
            $crc ^= $symbol;
            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x80) != 0 ? ($crc << 1) ^ 0x31 : $crc << 1;
            }
        }

        return substr(dechex($crc), -2); // нужен только последний байт crc8 ? остальные лишние : (???)
    }
}

