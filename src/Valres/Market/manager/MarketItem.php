<?php

namespace Valres\Market\manager;

use pocketmine\item\Item;
use Valres\Market\utils\Serializer;

class MarketItem
{
    public function __construct(
        protected string $id,
        protected string $itemSerialized,
        protected int $price,
        protected int $expiredTime,
        protected string $sellerName
    ){}

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getItemSerialized(): string
    {
        return $this->itemSerialized;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return Serializer::unserializeItem($this->itemSerialized);
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getExpiredTime(): int
    {
        return $this->expiredTime;
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        return ($this->expiredTime - time() <= 0);
    }

    /**
     * @return string
     */
    public function getSellerName(): string
    {
        return $this->sellerName;
    }
}
