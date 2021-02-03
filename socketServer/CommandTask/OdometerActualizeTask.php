<?php

namespace app\socketServer\CommandTask;

use app\socketServer\Database\DataStore;
use app\socketServer\Server\Mediator;
use app\socketServer\Server\SubscribeInterface;

class OdometerActualizeTask implements CommandTaskInterface, SubscribeInterface
{
    use \app\socketServer\Server\EmitSubscriber;

    const TIMEOUT = 60; // 60 seconds

    private ?int $_genarationTime = null;

    private int $_imei;

    private int $_id;

    private string $_datetime;

    private int $_actualOdometer;

    private int $_generatedOdometer;

    private string $_command = '';

    private \Closure $_solver;

    private bool $_isSolved = false;

    public function __construct(int $imei, int $id, Mediator $mediator = null, object $data = null)
    {
        $this->_imei = $imei;
        $this->_id = $id;
        $this->setMediator($mediator);
        $this->_actualOdometer = $data->actualOdometer;
        $this->_datetime = $data->datetime;

        $this->_solver = function ($args) {
            $this->solve($args->answer);
        };

        $this->mediator->on('solve OdometerActualizeTasks', $this->_solver);
    }

    public function run(): void
    {
        if (!$this->_genarationTime || time() - self::TIMEOUT < $this->_genarationTime) {
            $this->generateCommand();
        }
        if (!$this->_imei || !$this->_command) {
            return;
        }
        $this->mediator->emit($this, 'send command', (object) [
            'imei' => $this->_imei,
            'command' => $this->_command
        ]);
    }

    public function generateCommand(): string
    {
        $lastOdometer = (new DataStore())->lastOdometer($this->_imei);
        $odometerByTime = (new DataStore())->nearestOdometerByTimestamp($this->_imei, $this->_datetime);

        $this->_generatedOdometer = round($this->_actualOdometer + (($lastOdometer['value'] - $odometerByTime['value']) / 1000));

        $this->_genarationTime = time();
        $this->_command = 'setparam 11807: ' . (string) $this->_generatedOdometer;

        return $this->_command;
    }

    public function isSolved(): bool
    {
        return $this->_isSolved;
    }

    public function solve(string $solution): bool
    {
        if ($this->_isSolved) {
            return $this->isSolved();
        }

        $sampler = "New value 11807:   {$this->_generatedOdometer};";
        $purgePattern = '/[^A-zА-я0-9]/i';
        $purgedSampler = preg_replace($purgePattern, '', $sampler);
        $purgedSolution = preg_replace($purgePattern, '', $solution);

        if (stripos($purgedSampler, $purgedSolution) === 0) {
            $this->_isSolved = true;
            $this->mediator->off('solve OdometerActualizeTasks', $this->_solver);

            return $this->isSolved();
        }

        return $this->isSolved();
    }

    public function getImei(): int
    {
        return $this->_imei;
    }

    public function getId(): int
    {
        return $this->_id;
    }
}
