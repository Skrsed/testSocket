<?php

namespace app\models;

use Yii;

/**
 * Класс модели таблицы для хранения.
 *
 * @property int $id
 * @property string $alias Псевдоним
 * @property int $flex_id id записи в таблице FLEX (см. документацию)
 * @property string $description Описание
 * @property int $length Длина параметра в байтах
 */

class TelemetricsStructureSmart extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [['alias', 'flex_id', 'description', 'length'], 'safe']
        ];
    }
    public static function tableName()
    {
        return 'telemetrics_structure_smart';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alias' => 'Псевдоним',
            'flex_id' => 'ID записи FLEX',
            'description' => 'Описание',
            'length' => 'Длина блока данных'
        ];
    }
}