<?php

namespace Valres\Market\menus;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use Valres\Market\libs\muqsit\invmenu\InvMenu;
use Valres\Market\libs\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use Valres\Market\libs\muqsit\invmenu\type\InvMenuTypeIds;
use Valres\Market\manager\MarketItem;
use Valres\Market\Market;
use Valres\Market\utils\TimeHelper;

class ListingMenu
{
    public static function makeMenu(Player $player, InvMenu $menu, int $page): void
    {
        $marketManager = Market::getInstance()->marketManager;
        $config = Market::getInstance()->getConfig();
        $menu->getInventory()->clearAll();

        $menu->getInventory()->setItem(45, StringToItemParser::getInstance()->parse($config->get("expired-menu")["item"])->setCustomName($config->get("expired-menu")["name"]));
        $menu->getInventory()->setItem(48, StringToItemParser::getInstance()->parse($config->get("previous-page-menu")["item"])->setCustomName(str_replace("{page}", ($page <= 1 ? 1 : $page - 1), $config->get("previous-page-menu")["name"])));
        $menu->getInventory()->setItem(50, StringToItemParser::getInstance()->parse($config->get("next-page-menu")["item"])->setCustomName(str_replace("{page}", ($page + 1), $config->get("next-page-menu")["name"])));
        $menu->getInventory()->setItem(53, StringToItemParser::getInstance()->parse($config->get("my-item-menu")["item"])->setCustomName($config->get("my-item-menu")["name"]));

        $i = 0;
        $marketItems = [];
        foreach($marketManager->getMarketSales($marketManager->getAllMarketItems(false), ($page - 1)) as $marketItem){
            $marketItems[$marketItem->getId()] = $marketItem;
        }

        foreach($marketItems as $id => $marketItem){
            $marketSale = $marketManager->getMarketItem($id);
            if(is_null($marketSale)) continue;
            $item = $marketSale->getItem();
            $item->getNamedTag()->setShort("id", $id);
            $lore = [];
            foreach($item->getLore() as $line){
                $lore[] = $line;
            }
            foreach($config->get("lore-item-menu") as $line){
                $lore[] = str_replace(
                    ["{seller}", "{price}", "{expired-time}"],
                    [$marketSale->getSellerName(), $marketSale->getPrice(), TimeHelper::timeToString($marketSale->getExpiredTime())],
                    $line
                );
            }
            $item->setLore($lore);
            $menu->getInventory()->setItem($i, $item);
            $i++;
        }
        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($marketManager, $page, $menu, $config): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            $item = $menu->getInventory()->getItem($slot);

            switch($slot){
                case 45:
                    //Vente expirés
                    break;
                case 48:
                    if($page <= 1) break;

                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    self::makeMenu($player, $menu, ($page - 1));
                    $menu->send($player);
                    break;
                case 50:
                    $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
                    if(count($marketManager->getMarketSales($marketManager->getAllMarketItems(false), ($page + 1))) >= 1){
                        self::makeMenu($player, $menu, ($page + 1));
                        $menu->send($player);
                    }
                    break;
                case 53:
                    //Mes ventes
                    break;
                default:
                    break;
            }
            if($slot >= 0 and $slot <= 35){
                if(is_null($item->getNamedTag()->getTag("id"))){
                    $menu->onClose($player);
                    $player->sendMessage($config->get("not-existed-message"));
                    return;
                }

                $marketSale = $marketManager->getMarketItem($item->getNamedTag()->getShort("id"));
                if(!$marketSale instanceof MarketItem){
                    $menu->onClose($player);
                    $player->sendMessage($config->get("not-existed-message"));
                    return;
                }

                if($marketSale->getSellerName() === $player->getName()){
                    $menu->onClose($player);
                    $player->sendMessage($config->get("your-item-message"));
                    return;
                }

                $menu->onClose($player);
                $purshaseMenu = InvMenu::create(InvMenuTypeIds::TYPE_HOPPER);
                $purshaseMenu->setName("Valider l'achat :");
                PurshaseMenu::makeMenu($player, $purshaseMenu, $marketSale);
                $purshaseMenu->send($player);
            }
        }));
    }
}
