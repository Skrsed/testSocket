<?php

namespace app\models;

use Yii;

/**
 * Класс модели таблицы последних io записей для imei.
 *
 * @property int $imei IMEI устройства
 * @property int $io_parameter_id id IO параметра
 * @property string $value Значение записи
 * @property timestamp $timestamp
 */
class IOLast extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'io_last';
    }

    public static function getDb()
    {
        return Yii::$app->get('dbLocation');
    }

    public function fields()
    {
        return [
            'io_parameter_id',
            'value',
            'timestamp'
        ];
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
