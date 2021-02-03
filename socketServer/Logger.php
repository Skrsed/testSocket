<?php

namespace app\socketServer;
class Logger
{
    private static $title = "TELTONIKA SERVER";
    private static $frame = "*****************************";

    public static function logo()
    {
        echo "\n\n" . self::$frame . self::$title . self::$frame . "\n";
    }

    public static function exit($error = '')
    {
        if ($error) {
            echo "\n" . $error . "\n";
        }
        echo "\n" . self::$frame . str_pad("DIE", strlen(self::$title), "*", STR_PAD_BOTH) . self::$frame . "\n\n";
        
    }

    public static function note($message, $tabs = 0)
    {
        $bt = debug_backtrace();
        $caller = array_shift($bt);

        $message = trim($message);
        $message = preg_replace("/[^a-zA-Z0-9 :,.]/", "", $message);
        $date = new \DateTime("now", new \DateTimeZone('Asia/Yekaterinburg'));
        echo $date->format('Y-m-d H:i:s'). ': ' . $caller['file'] . ':' . $caller['line'] . " $message \n";
    }

}
