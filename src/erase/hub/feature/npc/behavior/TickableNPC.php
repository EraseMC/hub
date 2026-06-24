<?php

declare(strict_types=1);

namespace erase\hub\feature\npc\behavior;

interface TickableNPC
{
	public function onTick() : void;
}
