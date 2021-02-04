<?php

namespace app\socketServer\Flex;

use app\socketServer\Entities\AVLData;
use app\socketServer\Entities\GPSData;
use app\socketServer\Entities\ImeiNumber;
use app\socketServer\Entities\IOData;

class FlexToAVLConvertor {
    public function convert($imei, $data) {
        $imei = new ImeiNumber();
        $imei->setImei($imei);

        $avlData = new AVLData();
        $avlData->setImei($imei);

        $gpsData = new GPSData();
        $gpsData->setLatitude($data['lat']);
        $gpsData->setLongitude($data['lng']);
        $gpsData->setSpeed($data['speed']);
        $avlData->setGpsData($gpsData);

        $ioData = [];
        if (array_key_exists('odometer', $data)) {
            array_push($ioData, new IOData('16', $data['odometer']));
        }

        if (array_key_exists('lat', $data)) {
            array_push($ioData, new IOData('601', $data['lat']));
        }

        if (array_key_exists('lng', $data)) {
            array_push($ioData, new IOData('602', $data['lng']));
        }

        $avlData->setIOData($ioData);

        return $avlData;
    }
}