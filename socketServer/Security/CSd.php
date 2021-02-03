<?php

namespace app\socketServer\Security;

class CSd
{
    public static function xorSum($data)
    {
        $res = array_reduce(unpack('C*', $data), function ($sum, $symbol) {
          $sum ^= $symbol;
 
          return $sum;
        });

        return dechex($res);
    }
}

