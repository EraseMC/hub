<?php

declare(strict_types=1);

namespace erase\hub\core\scoreboard;

use pocketmine\scheduler\Task;
use erase\hub\feature\player\HubPlayer;
use erase\hub\Hub;

final class ScoreboardUpdateTask extends Task
{
	public function onRun() : void
	{
		foreach (Hub::getInstance()->getServer()->getOnlinePlayers() as $player) {
			if (!$player instanceof HubPlayer || !$player->isConnected()) {
				continue;
			}

			$scoreboard = $player->getScoreboard();
			if ($scoreboard !== null) {
				$scoreboard->onUpdate();
				$scoreboard->flushUpdates();
			}
		}
	}
}
