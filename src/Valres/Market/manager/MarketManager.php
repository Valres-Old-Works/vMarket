<?php

namespace Valres\Market\manager;

use JsonException;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
use Valres\Market\command\MarketCommand;
use Valres\Market\Market;
use Valres\Market\utils\Serializer;

class MarketManager
{
    /** @var MarketItem[] */
    private array $market = [];

    private Config $marketDatas;

    /** @var Item[] */
    private array $blacklistItems = [];

    public function __construct(Market $plugin)
    {
        $this->marketDatas = new Config($plugin->getDataFolder() . "data.yml", Config::YAML);
        $plugin->getServer()->getCommandMap()->register("market", new MarketCommand($plugin, "market", "Ouvre l'interface du market", ["ah"]));
        $this->loadMarket();
        foreach(Market::getInstance()->getConfig()->get("blacklist-items") as $item){
            $this->blacklistItems[] = StringToItemParser::getInstance()->parse($item);
        }
    }

    /**
     * @param string $id
     * @return MarketItem|null
     */
    public function getMarketItem(string $id): ?MarketItem
    {
        return $this->market[$id] ?? null;
    }

    /**
     * @param bool $expired
     * @return MarketItem[]
     */
    public function getAllMarketItems(bool $expired): array
    {
        $result = [];
        foreach($this->market as $marketItem){
            if($marketItem->hasExpired() and !$expired) continue;
            $result[] = $marketItem;
        }
        return $result;
    }

    /**
     * @return MarketItem[]
     */
    public function sortMarketItems(string $sellerName, bool $expired): array
    {
        $results = [];
        foreach($this->getAllMarketItems($expired) as $marketItem){
            if($marketItem->getSellerName() !== $sellerName) continue;
            $results[] = $marketItem;
        }
        return $results;
    }

    /**
     * @param string $id
     * @param Item $item
     * @param int $price
     * @param int $expiredTime
     * @param string $sellerName
     * @return void
     */
    public function addMarketItem(string $id, Item $item, int $price, int $expiredTime, string $sellerName): void
    {
        $this->market[$id] = new MarketItem($id, Serializer::serilizeItem($item), $price, $expiredTime, $sellerName);
    }

    /**
     * @param string $id
     * @return void
     */
    public function removeMarketItem(string $id): void
    {
        unset($this->market[$id]);
    }

    /**
     * @return void
     */
    public function loadMarket(): void
    {
        $marketData = $this->marketDatas;
        foreach($marketData->getAll() as $id => ["id" => $id, "itemSerialized" => $itemSerialized, "price" => $price, "expiredTime" => $expiredTime, "sellerName" => $sellerName]){
            $this->addMarketItem($id, Serializer::unserializeItem($itemSerialized), $price, $expiredTime, $sellerName);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function saveMarket(): void
    {
        $marketData = $this->marketDatas;
        $marketData->setAll([]);
        foreach($this->market as $marketItem){
            $marketData->set($marketItem->getId(), [
                "id" => $marketItem->getId(),
                "itemSerialized" => $marketItem->getItemSerialized(),
                "price" => $marketItem->getPrice(),
                "expiredTime" => $marketItem->getExpiredTime(),
                "sellerName" => $marketItem->getSellerName()
            ]);
        }
        $marketData->save();
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isBlacklistItem(Item $item): bool
    {
        return in_array($item, $this->blacklistItems);
    }
}
