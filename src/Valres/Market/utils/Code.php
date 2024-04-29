<?php

namespace Valres\Market\utils;

use Valres\Market\Market;

class Code
{
    public static function generate(): int
    {
        while(true){
            $id = rand(1, 29999);
            if(Market::getInstance()->marketManager->getMarketItem($id) === null) break;
        }
        return $id;
    }
}
