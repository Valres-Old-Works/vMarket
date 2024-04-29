<?php

namespace Valres\Market\trait;

use pocketmine\player\Player;
use Valres\Market\Market;

trait PermissionsTrait
{
    /**
     * @param Player $player
     * @return int
     */
    public function getTaxe(Player $player): int
    {
        $permissions = Market::getInstance()->getConfig();
        $defaultTaxe = $permissions->get("permissions")["default"]["taxe"];
        $taxe = $defaultTaxe;

        foreach($permissions->get("permissions") as $permission => $data){
            if($player->hasPermission($permission)) {
                $taxe = $data["taxe"];
                break;
            }
        }
        return $taxe;
    }


    /**
     * @param Player $player
     * @return int
     */
    public function getMaxSlot(Player $player): int
    {
        $permissions = Market::getInstance()->getConfig();
        $defaultMaxItem = $permissions->get("permissions")["default"]["max-item"];
        $maxItem = $defaultMaxItem;

        foreach($permissions->get("permissions") as $permission => $data){
            if($player->hasPermission($permission)){
                $maxItem = $data["max-items"];
                break;
            }
        }
        return $maxItem;
    }
}
