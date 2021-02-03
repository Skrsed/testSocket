<?php

namespace app\models;

use Yii;

/**
 * Класс модели таблицы io записей для конкретной геопозиции.
 *
 * @property int $id
 * @property int $parameter_id Ключ значения используемый в протоколе передачи устройства
 * @property string $property_name Имя значения используемое в протоколе
 * @property string $value_type Тип значения параметра
 * @property string element_type Тип параметра
 * @property int|null $multiplier Множитель значения
 * @property string|null $units ЕИ
 * @property string|null $description Описание параметра
 * @property string $device Прошивка устройства/ устройство
 * @property string $standard_name Идентификатор типа значения
 *                                 (Стандартное имя параметра не зависящее от прошивки/ устройства)
 */

class IOParameter extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'io_parameter';
    }

    public static function getDb()
    {
        return Yii::$app->get('dbLocation');
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parameter_id' => 'Ключ значения используемый в протоколе передачи устройства',
            'property_name' => 'Имя значения используемое в протоколе',
            'value_type' => 'Тип значения параметра',
            'element_type' => 'Тип параметра',
            'multiplier' => 'Множитель значения',
            'units' => 'Единицы измерения',
            'description' => 'Описание параметра',
            'device' => 'Прошивка устройства/ устройство',
            'standard_name' => 'Идентификатор типа значения'
        ];
    }
}
