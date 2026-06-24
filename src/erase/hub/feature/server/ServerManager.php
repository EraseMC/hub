<?php

declare(strict_types=1);

namespace erase\hub\feature\server;

use pocketmine\scheduler\ClosureTask;
use erase\hub\core\config\dto\ServerConfig;
use erase\hub\feature\server\query\ServerQueryTask;
use erase\hub\Hub;
use function array_key_exists;
use function is_int;
use function max;

final class ServerManager
{
	/** @var array<string, ServerConfig> */
	private array $servers;

	/** @var array<string, int|null> id => online count (null = offline / unreachable) */
	private array $online = [];

	public function __construct(private readonly Hub $plugin)
	{
		$this->servers = $plugin->getConfiguration()->servers;
		foreach ($this->servers as $id => $_) {
			$this->online[$id] = null;
		}

		$interval = max(1, $plugin->getConfiguration()->general->queryIntervalSeconds);
		$plugin->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(fn () => $this->query()),
			20 * $interval
		);

		$this->query();
	}

	private function query() : void
	{
		$targets = [];
		foreach ($this->servers as $id => $server) {
			$targets[$id] = [$server->address, $server->port];
		}

		if ($targets === []) {
			return;
		}

		$this->plugin->getServer()->getAsyncPool()->submitTask(new ServerQueryTask($targets));
	}

	/**
	 * @param array<string, int|null> $results
	 */
	public function applyResults(array $results) : void
	{
		foreach ($results as $id => $online) {
			if (array_key_exists($id, $this->online)) {
				$this->online[$id] = is_int($online) ? $online : null;
			}
		}
	}

	/**
	 * @return array<string, ServerConfig>
	 */
	public function getServers() : array
	{
		return $this->servers;
	}

	public function getServer(string $id) : ?ServerConfig
	{
		return $this->servers[$id] ?? null;
	}

	public function getOnline(string $id) : ?int
	{
		return $this->online[$id] ?? null;
	}

	public function isOnline(string $id) : bool
	{
		return ($this->online[$id] ?? null) !== null;
	}
}
