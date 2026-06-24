<?php

declare(strict_types=1);

namespace erase\hub\feature\npc;

use LogicException;
use pocketmine\scheduler\ClosureTask;
use erase\hub\feature\npc\behavior\TickableNPC;
use erase\hub\Hub;

final class NPCManager
{
	/** @var array<string, NPC> */
	private array $npcs = [];

	public function __construct(private readonly Hub $plugin)
	{
		$this->plugin->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(fn () => $this->onTick()),
			20
		);
	}

	public function register(NPC $npc) : void
	{
		$name = $npc->getInternalName();

		if (isset($this->npcs[$name])) {
			throw new LogicException("NPC $name already exists");
		}

		$this->npcs[$name] = $npc;
		$npc->spawnToAll();
	}

	public function unregister(string $name) : void
	{
		if (!isset($this->npcs[$name])) {
			return;
		}

		$this->npcs[$name]->close();
		unset($this->npcs[$name]);
	}

	/**
	 * @return array<string, NPC>
	 */
	public function getAll() : array
	{
		return $this->npcs;
	}

	private function onTick() : void
	{
		foreach ($this->npcs as $npc) {
			if ($npc instanceof TickableNPC) {
				$npc->onTick();
			}
		}
	}

	public function close() : void
	{
		foreach ($this->npcs as $npc) {
			if (!$npc->isClosed()) {
				$npc->close();
			}
		}
		$this->npcs = [];
	}
}
