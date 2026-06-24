<?php

declare(strict_types=1);

namespace erase\hub\feature\npc;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use erase\hub\feature\npc\behavior\InteractableNPC;
use erase\hub\feature\player\HubPlayer;

abstract class NPC extends Human implements ChunkLoader
{
	public function __construct(
		Location $location,
		Skin $skin,
		private readonly string $internalName
	) {
		parent::__construct($location, $skin);

		$this->setNoClientPredictions();
		$this->setNameTagAlwaysVisible();
		$this->setScale(1.0);

		$location->getWorld()->registerChunkLoader(
			$this,
			$location->getFloorX() >> Chunk::COORD_BIT_SIZE,
			$location->getFloorZ() >> Chunk::COORD_BIT_SIZE
		);
	}

	protected function initEntity(CompoundTag $nbt) : void
	{
		parent::initEntity($nbt);
		$this->setNameTag($this->internalName);
		$this->setNameTagAlwaysVisible();
	}

	public static function getNetworkTypeId() : string
	{
		return EntityIds::NPC;
	}

	public function getInternalName() : string
	{
		return $this->internalName;
	}

	public function canSaveWithChunk() : bool
	{
		return false;
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if ($player instanceof HubPlayer && $this instanceof InteractableNPC) {
			$this->handlePlayerInteract($player);
		}
		return true;
	}

	public function attack(EntityDamageEvent $source) : void
	{
		$source->cancel();

		if ($source instanceof EntityDamageByEntityEvent) {
			$damager = $source->getDamager();
			if ($damager instanceof HubPlayer && $this instanceof InteractableNPC) {
				$this->handlePlayerInteract($damager);
			}
		}
	}

	public function setFakeNameTag(HubPlayer $player, string $nameTag) : void
	{
		if (!$player->isConnected()) {
			return;
		}

		$properties = clone $this->getNetworkProperties();
		$properties->setString(EntityMetadataProperties::NAMETAG, $nameTag);
		$pk = SetActorDataPacket::create(
			$this->getId(),
			$properties->getAll(),
			new PropertySyncData([], []),
			0
		);

		$player->getNetworkSession()->sendDataPacket($pk);
	}
}
