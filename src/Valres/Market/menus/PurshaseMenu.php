<?php

namespace Valres\Market\menus;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use Valres\Market\libs\muqsit\invmenu\InvMenu;
use Valres\Market\manager\MarketItem;
use Valres\Market\Market;
use Valres\Market\utils\TimeHelper;

class PurshaseMenu
{
    public static function makeMenu(Player $player, InvMenu $menu, MarketItem $marketItem): void
    {
        $marketManager = Market::getInstance()->marketManager;
        $config = Market::getInstance()->getConfig();
        $menu->getInventory()->clearAll();

        $menu->getInventory()->setItem(0, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName($config->get("accept-purshase-menu")["name"]));
        $menu->getInventory()->setItem(1, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName($config->get("accept-purshase-menu")["name"]));
        $menu->getInventory()->setItem(3, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName($config->get("decline-purshase-menu")["name"]));
        $menu->getInventory()->setItem(4, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName($config->get("decline-purshase-menu")["name"]));

        $item = $marketItem->getItem();
        $item->getNamedTag()->setShort("id", $marketItem->getId());
        $lore = [];
        foreach($item->getLore() as $line){
            $lore[] = $line;
        }
        foreach($config->get("lore-item-menu") as $line){
            $lore[] = str_replace(
                ["{seller}", "{price}", "{expired-time}"],
                [$marketItem->getSellerName(), $marketItem->getPrice(), TimeHelper::timeToString($marketItem->getExpiredTime())],
                $line
            );
        }
        $item->setLore($lore);
        $menu->getInventory()->setItem(2, $item);
    }
}
