<?php

declare(strict_types=1);

namespace Valres\Market\libs\muqsit\invmenu\type;

use Valres\Market\libs\muqsit\invmenu\InvMenu;
use Valres\Market\libs\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType
{

    public function createGraphic(InvMenu $menu, Player $player): ?InvMenuGraphic;

    public function createInventory(): Inventory;
}