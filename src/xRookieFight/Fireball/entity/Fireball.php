<?php

declare(strict_types=1);

namespace xRookieFight\Fireball\entity;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\HugeExplodeParticle;

class Fireball extends Throwable {


    protected float $gravity = 0;

    protected float $drag = 0;

    protected float $damage = 1;

    protected $shouldExplode = false;

    protected function getInitialSizeInfo() : EntitySizeInfo { return new EntitySizeInfo(0.50, 0.50); }

    public function isShouldExplode(): bool{
        return $this->shouldExplode;
    }

    public function setShouldExplode(bool $bool){
        $this->shouldExplode = $bool;
    }

    public function getName(): string{
        return "Fireball";
    }

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);

    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->closed){
            return false;
        }

        $this->timings->startTiming();

        $hasUpdate = parent::onUpdate($currentTick);

        if (!$this->shouldExplode){
            $this->getWorld()->addParticle($this->getLocation()->add(0.50 / 2 + rand(-100, 100) / 500, 0.50 / 2 + rand(-100, 100) / 500, 0.50 / 2 + rand(-100, 100) / 500), new CriticalParticle(2));
        }

        if ($this->onGround){
            $this->kill();
        }

        if ($this->ticksLived > 1200 || $this->shouldExplode){
            $this->kill();
            $hasUpdate = true;
        }
        $this->timings->stopTiming();

        return $hasUpdate;
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source->getCause() === $source::CAUSE_VOID) {
            parent::attack($source);
        }
        if ($source instanceof EntityDamageByEntityEvent){
            $damager = $source->getDamager();
            $this->setMotion($damager->getDirectionVector()->add(0, 0, 0)->multiply(0.5));
        }
    }

    public function getResultDamage(): int
    {
        $base = parent::getResultDamage();

        return 1;
    }

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        parent::onHitEntity($entityHit, $hitResult);
        $this->setShouldExplode(true);
    }

    public function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        $p = $blockHit->getPosition();
        parent::onHitBlock($blockHit, $hitResult);
        $this->setShouldExplode(true);
        $p->getWorld()->addParticle(new Vector3($p->getX(), $p->getY(), $p->getZ()), new HugeExplodeParticle());
        $p->getWorld()->addParticle(new Vector3($p->getX() +1 , $p->getY(), $p->getZ()), new HugeExplodeParticle());
        $p->getWorld()->addParticle(new Vector3($p->getX() -1 , $p->getY(), $p->getZ()), new HugeExplodeParticle());
        $p->getWorld()->addParticle(new Vector3($p->getX() , $p->getY(), $p->getZ() +1), new HugeExplodeParticle());
        $p->getWorld()->addParticle(new Vector3($p->getX() , $p->getY(), $p->getZ() -1), new HugeExplodeParticle());
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FIREBALL;
    }
}