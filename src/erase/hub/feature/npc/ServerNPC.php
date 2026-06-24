<?php

declare(strict_types=1);

namespace erase\hub\feature\npc;

use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use erase\hub\core\config\dto\ServerConfig;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\feature\menu\ServerForms;
use erase\hub\feature\npc\behavior\InteractableNPC;
use erase\hub\feature\npc\behavior\TickableNPC;
use erase\hub\feature\player\HubPlayer;

final class ServerNPC extends NPC implements InteractableNPC, TickableNPC
{
	public function __construct(
		Location $location,
		Skin $skin,
		private readonly ServerConfig $server
	) {
		parent::__construct($location, $skin, 'server.' . $server->id);
	}

	public function handlePlayerInteract(HubPlayer $player) : void
	{
		ServerForms::sendServer($player, $this->server);
	}

	public function onTick() : void
	{
		$nameTag = $this->buildNameTag(Translator::DEFAULT_LOCALE);
		$this->setNameTag($nameTag);

		foreach ($this->getViewers() as $viewer) {
			if ($viewer instanceof HubPlayer) {
				$this->setFakeNameTag($viewer, $this->buildNameTag($viewer));
			}
		}
	}

	public function spawnTo(Player $player) : void
	{
		parent::spawnTo($player);

		if ($player instanceof HubPlayer) {
			$this->setFakeNameTag($player, $this->buildNameTag($player));
		}
	}

	private function buildNameTag(HubPlayer|string $locale) : string
	{
		return Translator::translate(
			KnownTranslationKeys::NPC_NAMETAG,
			$locale,
			$this->server->displayName,
			ServerForms::onlineText($locale, $this->server)
		);
	}
}
