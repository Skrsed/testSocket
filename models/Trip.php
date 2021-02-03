<?php

namespace app\models;

use Yii;

/**
 * Класс модели поездок.
 *
 * @property int $imei IMEI устройства
 * @property timestamp $timestamp Временная метка начала/ окончания поездки
 * @property int $total_odometer Текущее общее значение одометра
 * @property int $trip_odometer Значение одометра для поездки
 * @property boolean $trip_status Статус поездки (В процессе/ Окончена)
 */
class Trip extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'trip';
    }

    public static function getDb()
    {
        return Yii::$app->get('dbLocation');
    }

    public function attributeLabels()
    {
        return [
            'imei' => 'IMEI устройства',
            'timestamp' => 'Временная метка',
            'total_odometer' => 'Текущее общее значение одометра',
            'trip_odometer' => 'Значение одометра для поездки',
            'trip_status' => 'Статус поездки'
        ];
    }
}
