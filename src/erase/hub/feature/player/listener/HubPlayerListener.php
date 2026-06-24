<?php

declare(strict_types=1);

namespace erase\hub\feature\player\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use erase\hub\feature\player\HubPlayer;

final class HubPlayerListener implements Listener
{
	/**
	 * @noinspection PhpUnused
	 */
	public function handlePlayerCreation(PlayerCreationEvent $event) : void
	{
		$event->setPlayerClass(HubPlayer::class);
	}
}
