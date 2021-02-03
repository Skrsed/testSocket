<?php

namespace app\models;

use Yii;

/**
 * Класс модели для записей прошивок
 *
 * @property int $id ID
 * @property string $name Название
 */
class Firmware extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'firmware';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название'
        ];
    }
}
