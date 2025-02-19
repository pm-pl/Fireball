<?php

namespace xRookieFight\Fireball\events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\sound\GhastShootSound;
use xRookieFight\Fireball\entity\Fireball;

class EventListener implements Listener
{
    /** @var array */
    private array $status = [];

     function onJoin(PlayerJoinEvent $e): void
     {
        $p = $e->getPlayer();

        $this->status[$p->getName()] = false;
    }

     function onQuit(PlayerQuitEvent $e): void
     {
        $p = $e->getPlayer();

        unset($this->status[$p->getName()]);
    }

     function onInteract(PlayerInteractEvent $e): void
     {
        $p = $e->getPlayer();
            if ($e->getItem()->getTypeId() == ItemTypeIds::FIRE_CHARGE) {
                $this->status[$p->getName()] = true;
                $e->getItem()->setCount($e->getItem()->getCount());
            }
    }

    function onItemUse(PlayerItemUseEvent $e) : void
    {
        $p = $e->getPlayer();
        if ($e->getItem()->getTypeId() == ItemTypeIds::FIRE_CHARGE) {
            $location = $p->getLocation();
            $direction = $p->getDirectionVector();

            $entity = new Fireball($location, null, new CompoundTag());
            $entity->setOwningEntity($p);
            $entity->setMotion($direction);
            $p->broadcastSound(new GhastShootSound(), [$p]);
            $entity->spawnToAll();
        }
    }

     function onMove(PlayerMoveEvent $e): void
     {
        $p = $e->getPlayer();

        $from = $e->getFrom();
        $to = $e->getTo();
        if ($this->status[$p->getName()]) {
            $distance = 10;
            $x = ($to->x - $from->x) * ($distance / 2);
            $z = ($to->z - $from->z) * ($distance / 2);
            $p->setMotion(new Vector3($x, 0.8, $z));
            $p->getWorld()->addParticle(new Vector3($p->getPosition()->getX(), $p->getPosition()->getY(), $p->getPosition()->getZ()), new HugeExplodeParticle());
            $p->getWorld()->addParticle(new Vector3($p->getPosition()->getX() +1 , $p->getPosition()->getY(), $p->getPosition()->getZ()), new HugeExplodeParticle());
            $p->getWorld()->addParticle(new Vector3($p->getPosition()->getX() -1 , $p->getPosition()->getY(), $p->getPosition()->getZ()), new HugeExplodeParticle());
            $p->getWorld()->addParticle(new Vector3($p->getPosition()->getX() , $p->getPosition()->getY(), $p->getPosition()->getZ() +1), new HugeExplodeParticle());
            $p->getWorld()->addParticle(new Vector3($p->getPosition()->getX() , $p->getPosition()->getY(), $p->getPosition()->getZ() -1), new HugeExplodeParticle());
            $p->broadcastSound(new GhastShootSound(), [$p]);
            $this->status[$p->getName()] = false;
        }
    }
}