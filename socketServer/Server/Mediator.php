<?php

namespace app\socketServer\Server;

class Mediator
{
    private $geoSocket = null;

    private $controlSocket = null;

    private $commandTasksSet = null;

    private $listeners = [];

    private $subscribers = [];

    public function __construct(GeoSocket $geoSocket, ControlSocket $controlSocket,
        CommandTasksSet $commandTasksSet, SubscribeInterface ...$subscribers)
    {
        $this->geoSocket = $geoSocket;
        $this->geoSocket->setMediator($this);

        $this->controlSocket = $controlSocket;
        $this->controlSocket->setMediator($this);

        $this->commandTasksSet = $commandTasksSet;
        $this->commandTasksSet->setMediator($this);

        foreach ($subscribers as $subscriber) {
            $this->subscribers[] = $subscriber;
            $subscriber->setMediator($this);
        }
    }

    public function emit(?object $sender, string $event, ?object $args): void
    {
        if ($event === 'send command') {
            $this->geoSocket->sendCodec12Data($args->command, $args->imei);
        }
        if ($event === 'process answer') {
            $this->controlSocket->sendAnswer($args->answer);
        }
        if ($event === 'odometerActualizeTask') {
            $this->commandTasksSet->addOdometerActualizeTask($args->imei, $args->datetime, $args->actualOdometer);
        }
        if (!isset($this->listeners[$event])) {
            return;
        }
        foreach (array_values($this->listeners[$event]) as $listener) {
            // usage: mediator->on('event', fn($args, $sender) => /.../)
            $listener($args, $sender);
        }
    }

    public function on(string $eventName, \Closure &$callback): void
    {
        if ($eventName && $callback) {
            $this->listeners[$eventName][] = $callback;
        }

    }

    public function off(string $eventName, \Closure &$callback)
    {
        foreach ($this->listeners[$eventName] as $index => $value) {
            if ($callback === $value) {
                unset($this->listeners[$eventName][$index]);
            }
        }
    }
}