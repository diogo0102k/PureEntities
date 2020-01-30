<?php

declare(strict_types=1);

namespace leinne\pureentities\block;

use pocketmine\block\MonsterSpawner as PMMonsterSpawner;
use pocketmine\entity\EntityFactory;
use pocketmine\player\Player;

class MonsterSpawner extends PMMonsterSpawner{

    public function onScheduledUpdate(): void{
        $spawner = $this->getPos()->getWorld()->getTile($this->getPos());
        if(!$spawner instanceof \leinne\pureentities\tile\MonsterSpawner || $spawner->closed){
            return;
        }
        if(!$spawner->hasValidEntityId()){
            $spawner->close();
            return;
        }

        if(++$spawner->delay >= mt_rand($spawner->getMinSpawnDelay(), $spawner->getMaxSpawnDelay())){
            $spawner->delay = 0;

            $list = [];
            $isValid = false;
            foreach($spawner->getPos()->getWorld()->getEntities() as $k => $entity){
                if($entity->getPosition()->distance($spawner->getPos()) <= $spawner->getRequiredPlayerRange()){
                    if($entity instanceof Player){
                        $isValid = true;
                    }
                    $list[] = $entity;
                    break;
                }
            }

            if($isValid && count($list) < $spawner->getMaxNearbyEntities()){
                $newx = mt_rand(1, max(2, $spawner->getSpawnRange()));
                $newz = mt_rand(1, max(2, $spawner->getSpawnRange()));
                $pos = $spawner->getPos()->asPosition();
                $pos->x += mt_rand(0, 1) ? $newx : -$newx;
                $pos->z += mt_rand(0, 1) ? $newz : -$newz;
                //$pos->y = $this->calculateYPos($pos->x, $pos->z);
                $nbt = EntityFactory::createBaseNBT($pos);
                $nbt->setString("identifier", $spawner->getEntityId());
                $entity = EntityFactory::createFromData($spawner->getPos()->getWorld(), $nbt);
                if($entity !== null){
                    $entity->spawnToAll();
                }
            }
        }
    }

    public function calculateYPos(int $x, int $y, int $z) : int{
        //TODO: 몬스터가 박히지 않을 최소한의 Y좌표
        return 0;
    }

}