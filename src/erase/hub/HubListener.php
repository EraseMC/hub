<?php

declare(strict_types=1);

namespace erase\hub;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use erase\hub\feature\item\HubItems;
use erase\hub\feature\menu\ServerForms;
use erase\hub\feature\player\HubPlayer;
use function microtime;

final class HubListener implements Listener
{
	private const string BYPASS_PERMISSION = 'hub.bypass.protection';

	/** @var array<int, float> player id => last selector-open timestamp */
	private array $lastSelectorUse = [];

	public function __construct(private readonly Hub $plugin)
	{
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onJoin(PlayerJoinEvent $event) : void
	{
		$player = $event->getPlayer();
		if ($player instanceof HubPlayer) {
			$this->plugin->getLobbyManager()->prepare($player);
		}
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onQuit(PlayerQuitEvent $event) : void
	{
		$player = $event->getPlayer();
		if ($player instanceof HubPlayer) {
			$scoreboard = $player->getScoreboard();
			if ($scoreboard !== null) {
				$scoreboard->remove();
				$player->setScoreboard(null);
			}
			unset($this->lastSelectorUse[$player->getId()]);
		}
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onItemUse(PlayerItemUseEvent $event) : void
	{
		$this->handleSelectorItem($event->getPlayer(), $event->getItem());
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onInteract(PlayerInteractEvent $event) : void
	{
		$this->handleSelectorItem($event->getPlayer(), $event->getItem());
	}

	private function handleSelectorItem(\pocketmine\player\Player $player, Item $item) : void
	{
		if (!$player instanceof HubPlayer || !HubItems::isSelector($item)) {
			return;
		}

		// Right-clicking a block can fire both PlayerInteractEvent and
		// PlayerItemUseEvent, so debounce to avoid opening the form twice.
		$now = microtime(true);
		$id = $player->getId();
		if (isset($this->lastSelectorUse[$id]) && ($now - $this->lastSelectorUse[$id]) < 0.5) {
			return;
		}
		$this->lastSelectorUse[$id] = $now;

		ServerForms::sendSelector($player);
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void
	{
		if ($this->shouldProtect($event->getPlayer())) {
			$event->cancel();
		}
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void
	{
		if ($this->shouldProtect($event->getPlayer())) {
			$event->cancel();
		}
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onDropItem(PlayerDropItemEvent $event) : void
	{
		if ($this->shouldProtect($event->getPlayer())) {
			$event->cancel();
		}
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onDamage(EntityDamageEvent $event) : void
	{
		if ($event->getEntity() instanceof HubPlayer) {
			$event->cancel();
		}
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function onExhaust(PlayerExhaustEvent $event) : void
	{
		if ($event->getPlayer() instanceof HubPlayer) {
			$event->cancel();
		}
	}

	private function shouldProtect(\pocketmine\player\Player $player) : bool
	{
		return $player instanceof HubPlayer && !$player->hasPermission(self::BYPASS_PERMISSION);
	}
}
