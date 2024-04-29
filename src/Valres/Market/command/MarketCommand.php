<?php

namespace Valres\Market\command;

use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use Valres\Market\command\subcommand\SellSubCommand;
use Valres\Market\libs\CortexPE\Commando\BaseCommand;
use Valres\Market\Market;

class MarketCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->setPermission(DefaultPermissions::ROOT_USER);
        $this->registerSubCommand(new SellSubCommand(Market::getInstance(), "sell", "Vendre un item dans le market"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if(!$sender instanceof Player) return;
    }
}
