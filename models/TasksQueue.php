<?php

namespace app\models;

use Yii;

/**
 * Класс модели таблицы для хранения очередей задач.
 *
 * @property int $id
 * @property string $classname Имя класса обработчика
 * @property string|json $data Данные для инициализации класса
 * @property string $status Статус задачи (solved, pending, reverted...)
 * @property string|timestamp $created_at Дата/ время создания
 * @property string|timestamp $restated_at Дата/ время последнего изменения статуса
 */

class TasksQueue extends \yii\db\ActiveRecord
{
    const SOLVED_STATUS = 'SOLVED_STATUS';
    const PENDING_STATUS = 'PENDING_STATUS';

    public function rules()
    {
        return [
            [['classname', 'data', 'status', 'changed_at'], 'safe']
        ];
    }
    public static function tableName()
    {
        return 'tasks_queue';
    }

    public function setSolved()
    {
        if ($this->status === self::SOLVED_STATUS) {
            return;
        }
        $this->status = self::SOLVED_STATUS;
        $this->restated_at = date("Y-m-d H:i:s");
        $this->save();
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'classname' => 'Класс задачи',
            'data' => 'Данные задачи',
            'status' => 'Статус'
        ];
    }
}
