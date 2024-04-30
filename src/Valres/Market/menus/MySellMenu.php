<?php

namespace Valres\Market\menus;

use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\Server;
use Valres\Market\libs\muqsit\invmenu\InvMenu;
use Valres\Market\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use Valres\Market\libs\muqsit\invmenu\type\InvMenuTypeIds;
use Valres\Market\manager\MarketItem;
use Valres\Market\Market;
use Valres\Market\utils\Serializer;
use Valres\Market\utils\TimeHelper;

class MySellMenu
{
    public static function makeMenu(Player $player, InvMenu $menu): void
    {
        $marketManager = Market::getInstance()->marketManager;
        $config = Market::getInstance()->getConfig();
        $menu->getInventory()->clearAll();

        $menu->getInventory()->setItem(45, StringToItemParser::getInstance()->parse($config->get("pick-all-menu")["item"])->setCustomName($config->get("pick-all-menu")["name"]));
        $menu->getInventory()->setItem(49, StringToItemParser::getInstance()->parse($config->get("back-menu")["item"])->setCustomName($config->get("back-menu")["name"]));

        $i = 0;
        foreach($marketManager->getMarketSales($marketManager->sortMarketItems($player->getName(), false), 0) as $marketItem){
            $item = Serializer::unserializeItem($marketItem->getItemSerialized());
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
            $menu->getInventory()->setItem($i, $item);
            $i++;
        }
        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($marketManager, $menu, $config): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $item = $menu->getInventory()->getItem($slot);

            switch($slot){
                case 45:
                    $player->removeCurrentWindow();
                    $count = 0;
                    foreach($marketManager->sortMarketItems($player->getName(), false) as $marketItem){
                        $item = Serializer::unserializeItem($marketItem->getItemSerialized());

                        if(!$player->getInventory()->canAddItem($item)){
                            $player->getWorld()->dropItem($player->getPosition(), $item);
                        } else $player->getInventory()->addItem($item);

                        $marketManager->removeMarketItem($marketItem->getId());
                        $count++;
                    }

                    if($count <= 0){
                        $player->sendMessage($config->get("no-pick-up-item-message"));
                        return;
                    }

                    $player->sendMessage($config->get("pick-all-items-message"));
                    break;
                case 49:
                    $player->removeCurrentWindow();
                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    ListingMenu::makeMenu($player, $menu, 1);
                    $menu->send($player);
                    break;
            }
            if($slot >= 0 and $slot <= 35){
                if(is_null($item->getNamedTag()->getTag("id"))) return;
                $marketSale = $marketManager->getMarketItem($item->getNamedTag()->getShort("id"));
                if(!$marketSale instanceof MarketItem){
                    $player->removeCurrentWindow();
                    $player->sendMessage($config->get("not-existed-message"));
                    return;
                }

                $item = Serializer::unserializeItem($marketSale->getItemSerialized());
                if(!$player->getInventory()->canAddItem($item)){
                    $player->getWorld()->dropItem($player->getPosition(), $item);
                } else $player->getInventory()->addItem($item);

                $marketManager->removeMarketItem($marketSale->getId());
                self::makeMenu($player, $menu);
                $menu->send($player);
            }
        }));
    }
}
