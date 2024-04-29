<?php

declare(strict_types=1);

namespace Valres\Market\libs\muqsit\invmenu\session\network\handler;

use Closure;
use Valres\Market\libs\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler
{

    public function createNetworkStackLatencyEntry(Closure $then): NetworkStackLatencyEntry;
}