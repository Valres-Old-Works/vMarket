<?php

namespace Valres\Market;

use JsonException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Valres\Market\libs\DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use Valres\Market\libs\DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use Valres\Market\libs\DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use Valres\Market\libs\DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use Valres\Market\manager\MarketManager;

class Market extends PluginBase
{
    public MarketManager $marketManager;
    public EconomyProvider $economy;

    use SingletonTrait;

    /**
     * @throws UnknownProviderException
     * @throws MissingProviderDependencyException
     */
    protected function onEnable(): void
    {
        $this->getLogger()->info("by Valres est lancé !");
        $this->marketManager = new MarketManager($this);
        $this->saveDefaultConfig();
        $this->saveResource("data.yml");

        libPiggyEconomy::init();
        $this->economy = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));
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
