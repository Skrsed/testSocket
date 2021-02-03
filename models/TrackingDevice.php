<?php

namespace app\models;

/**
 * Класс модели для таблицы следящих устройств.
 *
 * @property int $id Идентификатор (первичный ключ)
 * @property string $name Имя датчика
 * @property string $imei IMEI
 * @property int $firmware_id [Прошивка]
 */
class TrackingDevice extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'tracking_device';
    }

    public function rules()
    {
        return [
            [['name', 'imei', 'firmware_id'], 'required']
        ];
    }

    public function getFirmware()
    {
        return $this->hasOne(Firmware::class, ['id' => 'firmware_id']);
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => 'Имя датчика',
            'imei' => 'IMEI',
            'firmware_id' => '[Прошивка]'
        ];
    }

    public function fields()
    {
        return array_merge(parent::fields(), ['firmware']);
    }
}
