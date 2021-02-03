<?php

namespace app\models;

use Yii;

/**
 * Класс модели таблицы io записей для конкретной геопозиции.
 *
 * @property int $imei IMEI устройства
 * @property int $io_parameter_id id IO параметра
 * @property string $value Значение записи
 * @property timestamp $timestamp
 */
class IORecord extends \yii\db\ActiveRecord
{
    const ODOMETER_PROP_NAME = 'Total Odometer';
    const IGNITION_PROP_NAME = 'Ignition';


    public static function tableName()
    {
        return 'io_record';
    }

    public static function getDb()
    {
        return Yii::$app->get('dbLocation');
    }

    /**
     * Первое значение одометра после т/о
     */
    public static function onServiceOdometerValue($imei)
    {
        $car = Car::findOne(['imei' => $imei]);
        $service = Service::find()->where(['car_id' => $car->id])->orderBy(['end_datetime' => SORT_DESC])->one();

        if (!$service) {
            return 0;
        }

        $onServiceOdometer = self::find()
            ->leftJoin('io_parameter',
                '`io_parameter`.`id` = `io_record`.`io_parameter_id`'
            )
            ->where([
                'imei' => $imei,
                'property_name' => self::ODOMETER_PROP_NAME,
                'firmware_id' => $car->trackingDevice->firmware_id
            ])
            ->andWhere(['>', 'timestamp', $service->end_datetime])
            ->one();
        return $onServiceOdometer ? $onServiceOdometer->value : 0;
    }

    /**
     * Последнее значение одометра
     */
    public static function lastOdometerValue($imei)
    {
        $totalOdometer = self::find()
            ->leftJoin('io_parameter',
                '`io_parameter`.`id` = `io_record`.`io_parameter_id`'
            )
            ->where([
                'imei' => $imei,
                'io_parameter.property_name' => self::ODOMETER_PROP_NAME
            ])
            ->orderBy(['timestamp' => SORT_DESC])
            ->one();
        if (!$totalOdometer) {
            return;
        }
        return $totalOdometer ? $totalOdometer->value : 0;

    }

    /**
     * Последнее значение зажигания
     */
    public static function lastIgnitionValue($imei)
    {
        $ignition = self::find()
            ->leftJoin('io_parameter',
                '`io_parameter`.`id` = `io_record`.`io_parameter_id`'
            )
            ->where([
                'imei' => $imei,
                'io_parameter.property_name' => self::IGNITION_PROP_NAME
            ])
            ->orderBy(['timestamp' => SORT_DESC])
            ->one();
        return $ignition ? $ignition->value : null;
    }

    public function attributeLabels()
    {
        return [
            'imei' => 'IMEI устройства',
            'io_parameter_id' => 'id IO параметра',
            'value' => 'Значение записи',
            'timestamp' => 'Временная метка',
        ];
    }
}
