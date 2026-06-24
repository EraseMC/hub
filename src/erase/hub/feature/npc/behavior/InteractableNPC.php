<?php

declare(strict_types=1);

namespace erase\hub\feature\npc\behavior;

use erase\hub\feature\player\HubPlayer;

interface InteractableNPC
{
	public function handlePlayerInteract(HubPlayer $player) : void;
}
