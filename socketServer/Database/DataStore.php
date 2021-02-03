<?php
/**
 * Created by PhpStorm.
 * User: Alvaro
 * Date: 18/07/2018
 * Time: 13:23
 */
namespace app\socketServer\Database;

use app\socketServer\Conf;
use Medoo\Medoo;
use app\socketServer\Logger;

class DataStore
{
    private $dataBaseInstance;

    /**
     * DataStore constructor.
     * @param $dataBaseInstance
     */
    public function __construct()
    {
        $this->dataBaseInstance = new Medoo([
            // required
            'database_type' => 'mysql',
            'database_name' => Conf::getDBName(),
            'server' => Conf::getDBHost(),
            'username' => Conf::getDBUser(),
            'password' => Conf::getDBPass(),
        ]);
    }

    public function storeDataFromDevice($AVLElement) {

        $timestamp = $AVLElement->getTimestamp();
        $lat = $AVLElement->getGpsData()->getLatitude();
        $lng = $AVLElement->getGpsData()->getLongitude();
        $speed = $AVLElement->getGpsData()->getSpeed();
        $imei = $AVLElement->getImei()->getImeiNumber();

        if ($lat && $lng && $timestamp) {
            // Записать данные gpszz
            $insertLocation = $this->dataBaseInstance->query("
                INSERT INTO location (imei, lng, lat, speed, timestamp)
                    VALUES ($imei, $lng, $lat, $speed, FROM_UNIXTIME($timestamp))
                ON DUPLICATE KEY UPDATE
                    lng = $lng,
                    lat = $lat,
                    speed = $speed;
            ");

            $this->insertOrUpdateLastStateRow([
                'imei' => $AVLElement->getImei()->getImeiNumber(),
                'io_parameter_id' => $this->uniqueParamIdByName('lat'),
                'value' => $lat,
                'timestamp' =>  $timestamp
            ]);

            $this->insertOrUpdateLastStateRow([
                'imei' => $AVLElement->getImei()->getImeiNumber(),
                'io_parameter_id' => $this->uniqueParamIdByName('lng'),
                'value' => $lng,
                'timestamp' =>  $timestamp
            ]);

            $this->insertOrUpdateLastStateRow([
                'imei' => $AVLElement->getImei()->getImeiNumber(),
                'io_parameter_id' => $this->uniqueParamIdByName('speed'),
                'value' => $speed,
                'timestamp' =>  $timestamp
            ]);
        }

        // сформировать массив io данных
        foreach ($AVLElement->getIOData() as $ioRecord) {
            $ioParameterId = $this->uniqueParamIdById($ioRecord->getID());

            if (!$ioParameterId) {
                continue;
            }

            $value = $ioRecord->getValue();

            if ($ioParameterId === $this->uniqueParamIdByName('trip')) {
                $this->dataBaseInstance->insert('trip', [
                    'imei' => $imei,
                    'timestamp' => Medoo::raw("FROM_UNIXTIME($timestamp)"),
                    'total_odometer' => $AVLElement->getIOData(16) ? $AVLElement->getIOData(16)->getValue() : null,
                    'trip_odometer' => $AVLElement->getIOData(199) ? $AVLElement->getIOData(199)->getValue() : null,
                    'trip_status' => $value
                ]);
            }
            // проверить не срабатывает ли триггер
            $insertIOs [] = $this->dataBaseInstance->query("
                INSERT INTO io_record (imei, io_parameter_id, value, timestamp)
                    VALUES ($imei, $ioParameterId, $value, FROM_UNIXTIME($timestamp))
                ON DUPLICATE KEY UPDATE
                    value = $value;
            ");
            $this->insertOrUpdateLastStateRow([
                'imei' => $AVLElement->getImei()->getImeiNumber(),
                'io_parameter_id' => $this->uniqueParamIdById($ioRecord->getID()),
                'value' => $ioRecord->getValue(),
                'timestamp' =>  $timestamp
            ]);

        }

        if (!isset($insertLocation) || !$insertLocation) {
            Logger::note('Null location data was not be added');
            return;
        }
        
        if ($insertLocation->errorInfo()[0] !== '00000') {
            Logger::note('errors:' . implode(', ', $insertLocation->errorInfo()));
            foreach ($insertIOs as $insertIO) {
                if ($insertIO->errorInfo()[0] !== '00000') {
                    Logger::note('errors:' . implode(', ', $insertIO->errorInfo()));
                }
            }
            return;
        }

    }

    public function lastOdometer($imei)
    {
        return $this->dataBaseInstance->get('io_last', '*', [
            'imei' => $imei,
            'io_parameter_id' => 17,
            'LIMIT' => 1
        ]);
    }

    public function nearestOdometerByTimestamp($imei, $timestamp)
    {
        return $this->dataBaseInstance->get('io_record', '*', [
            'imei' => $imei,
            'io_parameter_id' => 17,
            'ORDER' => Medoo::raw("ABS( TIMESTAMPDIFF(SECOND, timestamp, '$timestamp' ) )"),
            'LIMIT' => 1
        ]);
    }

    private function uniqueParamIdByName($paramName)
    {
        $paramuniqueId = $this->dataBaseInstance->select('io_parameter', 'id', [
            'LIMIT' => 1,
            'standard_name' => $paramName
        ]);

        if (!$paramuniqueId) {
            Logger::note("Param couldn't be found for property_name: $paramName and device: $device");
            return null;
        }

        return $paramuniqueId[0];
    }

    private function uniqueParamIdById($ioParameterId)
    {
        $paramuniqueId = $this->dataBaseInstance->select('io_parameter', 'id', [
            'LIMIT' => 1,
            'parameter_id' => $ioParameterId
        ]);

        if (!$paramuniqueId) {
            Logger::note("Param couldn't be found for io_paramter_id: $ioParameterId and device: $device");
            return null;
        }

        return $paramuniqueId[0];
    }

    private function insertOrUpdateLastStateRow($args)
    {
        $imei = $args['imei'];
        $parameterId = $args['io_parameter_id'];
        $timestamp = $args['timestamp'];
        $value = $args['value'];

        $query = <<<SQL
            INSERT INTO io_last (imei, io_parameter_id, timestamp, value)
                VALUES ($imei, $parameterId, FROM_UNIXTIME($timestamp), $value)
            ON DUPLICATE KEY UPDATE
                timestamp = FROM_UNIXTIME($timestamp),
                value = $value;
        SQL;

        $anyNewerRecord = $this->dataBaseInstance->count('io_last', [
            'imei' => $imei,
            'io_parameter_id' => $parameterId,
            'timestamp[>=]' => Medoo::raw("FROM_UNIXTIME($timestamp)")
        ]);

        if ($anyNewerRecord != 0) {

            return;
        }

        $res = $this->dataBaseInstance->query($query);

        if ($res->errorInfo()[0] !== '00000') {
            Logger::note("imei: $imei, errors:" . implode(', ', $res->errorInfo()));
        }

        if ($res->errorInfo()[0] === '42000') {
            Logger::note("query with error: $query");
        }

    }
}
