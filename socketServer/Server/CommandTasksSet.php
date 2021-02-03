<?php

namespace app\socketServer\Server;

use app\models\TasksQueue;
use app\socketServer\CommandTask\OdometerActualizeTask;

class CommandTasksSet implements SubscribeInterface
{
    use EmitSubscriber;

    private $_tasks = [];

    private $_timer = null;

    public function __construct($loop, $queuePeriod)
    {
        $this->_timer = $loop->addPeriodicTimer($queuePeriod, function () {
            $this->runAll();
        });
    }

    public function addOdometerActualizeTask(int $imei, string $datetime, int $actualOdometer): void
    {
        (new TasksQueue([
            'status' => TasksQueue::PENDING_STATUS,
            'classname' => OdometerActualizeTask::class,
            'data' => json_encode((object) [
                'imei' => $imei,
                'datetime' => $datetime,
                'actualOdometer' => $actualOdometer
            ])
        ])
        )->save();
    }

    public function runAll(): void
    {
        $tasksRows = TasksQueue::find()->where([
            'status' => TasksQueue::PENDING_STATUS,
        ])
            ->andWhere(['not', ['id' => $this->getIds()]])
            ->all();

        foreach ($tasksRows as $taskRow) {
            $taskRow->data = json_decode($taskRow->data);
            switch ($taskRow->classname) {
                case OdometerActualizeTask::class:
                    $this->_tasks[] = new OdometerActualizeTask($taskRow->data->imei, $taskRow->id, $this->mediator, (object) [
                        'datetime' => $taskRow->data->datetime,
                        'actualOdometer' => $taskRow->data->actualOdometer
                    ]);
                    break;
                // ...
            }
        }

        foreach ($this->_tasks as $index => $task) {
            if ($task->isSolved()) {
                TasksQueue::findOne($task->getId())->setSolved();
                unset($this->_tasks[$index]);
                return;
            }
            $task->run();
        }
    }

    public function getIds(): ?array
    {
        return array_map(function ($task) {
            return $task->getId();
        }, $this->_tasks);
    }
}
