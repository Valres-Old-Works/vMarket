<?php

namespace Valres\Market;

use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Valres\Market\libs\muqsit\invmenu\InvMenuHandler;
use Valres\Market\manager\MarketManager;

class Market extends PluginBase
{
    public MarketManager $marketManager;

    use SingletonTrait;

    protected function onEnable(): void
    {
        $this->getLogger()->info("by Valres est lancé !");
        $this->marketManager = new MarketManager($this);
        $this->saveDefaultConfig();
        $this->saveResource("data.yml");

        if(!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        if(is_null($this->getServer()->getPluginManager()->getPlugin("EconomyAPI"))){
            $this->getLogger()->warning("EconomyAPI n'est pas sur le serveur, le plugin risque de ne pas fonctionnr correctement.");
        }
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    /**
     * @throws JsonException
     */
    protected function onDisable(): void
    {
        $this->marketManager->saveMarket();
    }
}
