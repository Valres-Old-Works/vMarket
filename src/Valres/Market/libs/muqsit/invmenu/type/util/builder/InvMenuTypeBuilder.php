<?php

declare(strict_types=1);

namespace Valres\Market\libs\muqsit\invmenu\type\util\builder;

use Valres\Market\libs\muqsit\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder
{

    public function build(): InvMenuType;
}