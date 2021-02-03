<?php
/**
 * Created by PhpStorm.
 * User: Alvaro
 * Date: 16/07/2018
 * Time: 13:23
 */

namespace app\socketServer\Server;

use app\socketServer\Logger;
use app\socketServer\Conf;
use Crc16\Crc16;

require __DIR__."/../../vendor/autoload.php";

class Server
{
    const QUEUE_PERIOD = 10; // seconds

    private $sockets = [];

    public function __construct(){

        Logger::logo();
        Logger::note("starting server...");
        $loop = \React\EventLoop\Factory::create();

        $geo = new GeoSocket(Conf::getGeoHost(), Conf::getGeoPort(), $loop);
        $control = new ControlSocket(Conf::getControllHost(), Conf::getControllPort(), $loop);

        $commandTaskSet = new CommandTasksSet($loop, self::QUEUE_PERIOD);

        $mediator = new Mediator($geo, $control, $commandTaskSet);
        register_shutdown_function(function () {
            Logger::exit(print_r(error_get_last(), true));
        });
        $loop->run();
    }
}

