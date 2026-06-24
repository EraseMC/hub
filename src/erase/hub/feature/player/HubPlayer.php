<?php

declare(strict_types=1);

namespace erase\hub\feature\player;

use pocketmine\player\Player;
use erase\hub\core\scoreboard\Scoreboard;

class HubPlayer extends Player
{
	private ?Scoreboard $scoreboard = null;

	public function getScoreboard() : ?Scoreboard
	{
		return $this->scoreboard;
	}

	public function setScoreboard(?Scoreboard $scoreboard) : void
	{
		$this->scoreboard = $scoreboard;
	}
}
