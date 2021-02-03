<?php
namespace app\socketServer;

class Conf
{
    private static ?array $_appConfig = null;

    public static function readConfig()
    {
        if (is_null(self::$_appConfig)) {
            self::$_appConfig = json_decode(file_get_contents(__DIR__ . '/../appConfig.json'), true);
        }

        return self::$_appConfig;
    }

    public static function getGeoHost(): string { return self::readConfig()['geo_host']; }

    public static function getGeoPort(): string { return self::readConfig()['geo_port']; }

    public static function getControllHost(): string { return self::readConfig()['controll_host']; }

    public static function getControllPort(): string { return self::readConfig()['controll_port']; }

    public static function getDBUser(): string { return self::readConfig()['db_user']; }

    public static function getDBPass(): string { return self::readConfig()['db_pass']; }

    public static function getDBName(): string { return self::readConfig()['db_name']; }

    public static function getDBHost(): string { return self::readConfig()['db_host']; }
}
