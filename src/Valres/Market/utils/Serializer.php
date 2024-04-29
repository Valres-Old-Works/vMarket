<?php

namespace Valres\Market\utils;

use pocketmine\item\Item;

class Serializer
{
    public static function serilizeItem(Item $item): string
    {
        return base64_encode(serialize($item->nbtSerialize()));
    }

    public static function unserializeItem(string $serialize): Item
    {
        return Item::nbtDeserialize(unserialize(base64_decode($serialize)));
    }
}
