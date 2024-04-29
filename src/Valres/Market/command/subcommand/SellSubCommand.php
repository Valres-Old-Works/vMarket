<?php

namespace Valres\Market\command\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use Valres\Market\libs\CortexPE\Commando\args\FloatArgument;
use Valres\Market\libs\CortexPE\Commando\BaseSubCommand;
use Valres\Market\libs\CortexPE\Commando\exception\ArgumentOrderException;
use Valres\Market\Market;
use Valres\Market\utils\Code;

class SellSubCommand extends BaseSubCommand
{
    /**
     * @return void
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission(DefaultPermissions::ROOT_USER);
        $this->registerArgument(0, new FloatArgument("price", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if(!$sender instanceof Player) return;
        $config = Market::getInstance()->getConfig();
        $marketManager = Market::getInstance()->marketManager;

        if(!isset($args["price"])){
            $sender->sendMessage($config->get("no-price-message"));
            return;
        }

        if(!is_numeric($args["price"])){
            $sender->sendMessage($config->get("not-numeric-message"));
            return;
        }

        $price = (int)$args["price"];
        if($price <= 0){
            $sender->sendMessage($config->get("not-positif-message"));
            return;
        }

        $itemToSell = $sender->getInventory()->getItemInHand();
        if($itemToSell->equals(VanillaItems::AIR()) or $marketManager->isBlacklistItem($itemToSell)){
            $sender->sendMessage($config->get("blacklist-item-message"));
            return;
        }

        $id = Code::generate();
        $itemToSell->getNamedTag()->setShort("id", $id);
        $marketManager->addMarketItem($id, $itemToSell, $price, time() + $config->get("expired-time"), $sender->getName());
        $sender->getInventory()->setItemInHand(VanillaItems::AIR());

        $sender->sendMessage(str_replace(
            ["{count}", "{item}", "{price}"],
            [$itemToSell->getCount(), $itemToSell->getName(), $price],
            $config->get("sell-item-message")
        ));

        if($config->get("new-item-message")["enabled"]){
            Server::getInstance()->broadcastMessage(str_replace(
                ["{player}", "{count}", "{item}", "{price}"],
                [$sender->getName(), $itemToSell->getCount(), $itemToSell->getName(), $price],
                $config->get("new-item-message")["message"]
            ));
        }
    }
}
