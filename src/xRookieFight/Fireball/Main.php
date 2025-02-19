<?php

namespace xRookieFight\Fireball;

use pocketmine\plugin\PluginBase;
use xRookieFight\Fireball\events\EventListener;

class Main extends PluginBase
{

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

}