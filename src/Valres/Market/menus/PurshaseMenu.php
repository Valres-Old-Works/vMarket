<?php

namespace Valres\Market\menus;

use onebone\economyapi\EconomyAPI;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\Server;
use Valres\Market\libs\muqsit\invmenu\InvMenu;
use Valres\Market\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use Valres\Market\libs\muqsit\invmenu\type\InvMenuTypeIds;
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
        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($marketManager, $menu, $config, $marketItem): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();

            switch($slot){
                case 0:
                case 1:
                    if(EconomyAPI::getInstance()->myMoney($player) < $marketItem->getPrice()){
                        $menu->onClose($player);
                        $player->sendMessage($config->get("no-money-message"));
                        return;
                    }

                    $marketManager->removeMarketItem($marketItem->getId());
                    if($player->getInventory()->canAddItem($marketItem->getItem())){
                        $player->getInventory()->addItem($marketItem->getItem());
                    } else $player->getWorld()->dropItem($player->getPosition(), $marketItem->getItem());
                    EconomyAPI::getInstance()->reduceMoney($player, $marketItem->getPrice());
                    EconomyAPI::getInstance()->addMoney($marketItem->getSellerName(), $marketItem->getPrice());
                    $seller = Server::getInstance()->getPlayerExact($marketItem->getSellerName());
                    if($seller instanceof Player){
                        $seller->sendMessage($config->get("buying-item-message"));
                    }
                    $player->sendMessage(str_replace(
                        ["{count}", "{item}", "{price}"],
                        [$marketItem->getItem()->getCount(), $marketItem->getItem()->getName(), $marketItem->getPrice()],
                        $config->get("buy-item-message")
                    ));
                    break;
                case 3:
                case 4:
                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    ListingMenu::makeMenu($player, $menu, 1);
                    $menu->send($player);
                    break;
            }
        }));
    }
}
