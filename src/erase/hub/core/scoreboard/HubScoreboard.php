<?php

declare(strict_types=1);

namespace erase\hub\core\scoreboard;

use pocketmine\utils\TextFormat;
use erase\hub\Hub;

abstract class HubScoreboard extends Scoreboard
{
	final protected function title() : string
	{
		return TextFormat::colorize(Hub::getInstance()->getConfiguration()->general->title);
	}
}
