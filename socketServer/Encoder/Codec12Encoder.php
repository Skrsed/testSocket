<?php

namespace app\socketServer\Encoder;

use Crc16\Crc16;

class Codec12Encoder {

    const ZERO_BYTES = "00000000";
    const CODEC = "OC";
    const RESP_QUANTITY_1 = "01";
    const COMMAND_TYPE = "05";
    const RESP_QUANTITY_2 = "01";
    /*
     * encode command response text to valid codec12 packet
     */
    public function encode(string $command)
    {

        $command_str = $command;
        $head = "00000000";
        $codec = "0C";
        $cq1 = "01";
        $command_type = "05";
        $command_size = str_pad(dechex(strlen($command_str)), 8, '0', STR_PAD_LEFT);
        $command = bin2hex($command_str);
        $cq2 = "01";
                        

        $data_size = strlen($codec . $cq1 . $command_type . $command_size . $command . $cq2) / 2;
        $data_size = str_pad(dechex($data_size), 8, '0', STR_PAD_LEFT);
        $crc16 = Crc16::IBM(hex2bin($codec . $cq1 . $command_type . $command_size . $command . $cq2));
        $crc16 = str_pad(dechex($crc16), 8, '0', STR_PAD_LEFT);
        $full = $head . $data_size . $codec . $cq1 . $command_type . $command_size . $command . $cq2 . $crc16;
        return $full;
    }
}