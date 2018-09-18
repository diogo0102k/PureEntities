<?php

declare(strict_types=1);

namespace leinne\pureentities\entity\animal;

use pocketmine\math\Facing;
use pocketmine\math\Vector3;

abstract class WalkAnimal extends Animal{

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->closed){
            return \false;
        }

        $hasUpdate = parent::entityBaseTick($tickDiff);

        if(!$this->isMovable()){
            return $hasUpdate;
        }

        $target = $this->checkTarget();

        $x = $target->x - $this->x;
        $y = $target->y - $this->y + 0.0;
        $z = $target->z - $this->z;

        $diff = \abs($x) + \abs($z);
        $needJump = \false;
        if(!$this->interactTarget() && $diff !== 0.0){
            $hasUpdate = \true;
            $needJump = $this->onGround;
            $ground = $this->onGround ? 0.12 : 0.008;
            $this->motion->x += $this->getSpeed() * $ground * $x * $tickDiff / $diff;
            $this->motion->z += $this->getSpeed() * $ground * $z * $tickDiff / $diff;
        }

        if($needJump){
            $hasUpdate = $this->checkJump($tickDiff) && !$hasUpdate ? \true : $hasUpdate;
        }

        $this->yaw = \rad2deg(\atan2($z, $x)) - 90.0;
        $this->pitch = $y === 0.0 ? $y : \rad2deg(-\atan2($y, \sqrt($x ** 2 + $z ** 2)));

        return $hasUpdate;
    }

    protected function checkJump(int $tickDiff) : bool{
        $block = $this->getLevel()->getBlock(new Vector3(
            (int) ((($dx = $this->motion->x) > 0 ? $this->boundingBox->maxX : $this->boundingBox->minX) + $dx * $tickDiff * 2),
            $this->y,
            (int) ((($dz = $this->motion->z) > 0 ? $this->boundingBox->maxZ : $this->boundingBox->minZ) + $dz * $tickDiff * 2)
        ));
        if(
            ($aabb = $block->getBoundingBox()) !== \null
            && $block->getSide(Facing::UP)->getBoundingBox() === \null //밟고있는게 반블럭 그리고 그 위로 반블럭 한개로 1칸블럭, 점프가능(추후 예외설정)
            && $block->getSide(Facing::UP, 2)->getBoundingBox() === \null
        ){
            if($aabb->maxY - $aabb->minY > 1 || $aabb->maxY === $this->y){ //울타리 or 반블럭 위
                return \false;
            }elseif($aabb->maxY - $this->y === 0.5){ //반블럭
                $this->motion->y = 0.36;
                return \true;
            }
            $this->motion->y = 0.52;
            return \true;
        }
        return \false;
    }

}