<?php

namespace app\socketServer\Server;

interface SubscribeInterface
{
    public function setMediator(Mediator $mediator);

    public function getMediator();
}