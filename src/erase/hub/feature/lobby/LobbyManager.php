<?php

declare(strict_types=1);

namespace erase\hub\feature\lobby;

use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\world\sound\XpCollectSound;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\feature\item\HubItems;
use erase\hub\feature\player\HubPlayer;
use erase\hub\Hub;

final class LobbyManager
{
	public function __construct(private readonly Hub $plugin)
	{
	}

	public function getSpawnLocation() : ?Location
	{
		$config = $this->plugin->getConfiguration()->spawn;
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($config->world);
		if ($world === null) {
			return null;
		}

		return new Location($config->x, $config->y, $config->z, $world, $config->yaw, $config->pitch);
	}

	public function prepare(HubPlayer $player) : void
	{
		if (!$player->isConnected()) {
			return;
		}

		$player->setGamemode(GameMode::ADVENTURE());
		$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		$player->getHungerManager()->setEnabled(false);
		$player->setHealth($player->getMaxHealth());
		$player->getEffects()->clear();
		$player->setFlying(false);
		$player->setAllowFlight(false);
		$player->extinguish();

		$spawn = $this->getSpawnLocation();
		if ($spawn !== null) {
			$player->teleport($spawn, $spawn->yaw, $spawn->pitch);
		}

		$this->plugin->getProvider()->recordJoin($player);

		$this->giveItems($player);
		$this->setupScoreboard($player);
		$this->sendWelcome($player);

		$player->getWorld()->addSound($player->getPosition(), new XpCollectSound(), [$player]);

		$player->sendTitle(
			Translator::translate(KnownTranslationKeys::JOIN_TITLE, $player),
			Translator::translate(KnownTranslationKeys::JOIN_SUBTITLE, $player, $player->getName())
		);
	}

	public function giveItems(HubPlayer $player) : void
	{
		$inventory = $player->getInventory();
		$inventory->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();

		$slot = $this->plugin->getConfiguration()->general->hubItemSlot;
		$inventory->setItem($slot, HubItems::selector($player));
	}

	public function setupScoreboard(HubPlayer $player) : void
	{
		$scoreboard = new LobbyScoreboard($player);
		$player->setScoreboard($scoreboard);
		$scoreboard->setup();
		$scoreboard->sendObjective();
		$scoreboard->flushUpdates();
	}

	public function sendWelcome(HubPlayer $player) : void
	{
		$serverManager = $this->plugin->getServerManager();

		$message = Translator::translate(KnownTranslationKeys::JOIN_WELCOME_HEADER, $player);
		foreach ($serverManager->getServers() as $server) {
			$online = $serverManager->getOnline($server->id);
			$message .= "\n" . Translator::translate(
				KnownTranslationKeys::JOIN_WELCOME_SERVER_LINE,
				$player,
				$server->shortName,
				$online !== null ? (string) $online : Translator::translate(KnownTranslationKeys::STATUS_OFFLINE, $player)
			);
		}

		$player->sendMessage($message);
	}
}
