<?php

namespace app\socketServer\CommandTask;

use app\socketServer\Logger;
use app\socketServer\Database\DataStore;

class OdometerGetTask implements CommandTaskInterface
{
    private $_imei;
    private $_isSolved;
    private $_solutionTime;

    const MAX_LIFETIME = 15 * 60; // 15 minutes (in seconds) 

    public function __construct(int $imei, array $data, string $handlerName, int $id)
    {
        $this->_imei = $data->imei;
    }

    public function getCommand(): string
    {
        return 'getparam 11807';
    }

    public function isSolved(): bool
    {
        return $this->_solutionTime && (time() - $this->_solutionTime < self::MAX_LIFETIME) ? $this->_isSolved : false;
    }

    public function solve(string $solution): bool
    {
        $this->_solutionTime = time();
        $this->_isSolved = $this->_isSolved || str_contains($solution, 'Param ID:11807 Value:');
        return $this->isSolved();
    }

    public function getImei(): int
    {
        return $this->_imei;
    }
}