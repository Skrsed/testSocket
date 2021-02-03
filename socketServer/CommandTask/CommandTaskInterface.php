<?php

namespace app\socketServer\CommandTask;

use app\socketServer\Server\Mediator;

interface CommandTaskInterface
{
    public function  isSolved(): bool;

    public function solve(string $solution): bool;

    public function getImei(): int;

    public function getId(): int;
}