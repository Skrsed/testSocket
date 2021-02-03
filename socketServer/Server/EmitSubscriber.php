<?php

namespace app\socketServer\Server;

trait EmitSubscriber // implements SubscribeInterface
{
    protected $mediator;

    public function setMediator(Mediator $mediator): void
    {
        $this->mediator = $mediator;
    }

    public function getMediator()
    {
        return $this->mediator;
    }
}
