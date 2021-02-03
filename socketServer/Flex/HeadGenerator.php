<?php

namespace app\socketServer\Flex;

use app\socketServer\Security\CSd;

class HeadGenerator {

    const PREAMBLE_LENGTH = 8;
    const IDS_LENGTH = 8;

    public function generateAnswer(string $headBase, string $afterHeadData)
    {
        $preamble = mb_substr($headBase, 0, self::PREAMBLE_LENGTH);
        $recipient = mb_substr($headBase, self::PREAMBLE_LENGTH, self::IDS_LENGTH);
        $sender = mb_substr($headBase, self::PREAMBLE_LENGTH + self::IDS_LENGTH, self::IDS_LENGTH);

        $strMerge = $preamble . $sender . $recipient . bin2hex(pack('S*', strlen($afterHeadData)));
        $strMerge .= CSd::xorSum($afterHeadData); // Сумма данных
        $strMerge .= CSd::xorSum(hex2bin($strMerge)) . bin2hex($afterHeadData);

        return $strMerge;
    }

}