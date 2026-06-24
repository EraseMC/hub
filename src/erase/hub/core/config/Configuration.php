<?php

declare(strict_types=1);

namespace erase\hub\core\config;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use erase\hub\core\config\dto\GeneralConfig;
use erase\hub\core\config\dto\NpcSpawnConfig;
use erase\hub\core\config\dto\ServerConfig;
use erase\hub\core\config\dto\SpawnConfig;
use erase\hub\Hub;
use function is_array;

final class Configuration
{
	use SingletonTrait;

	public GeneralConfig $general;
	public SpawnConfig $spawn;

	/** @var array<string, ServerConfig> */
	public array $servers = [];

	public function __construct(Hub $plugin)
	{
		$plugin->saveResource('config.yml');
		$config = new Config($plugin->getDataFolder() . 'config.yml', Config::YAML);
		$data = $config->getAll();

		$this->general = new GeneralConfig(
			(string) ($data['title'] ?? '&l&cERASE'),
			(string) ($data['prefix'] ?? '&l&cErase &r&7| &r'),
			(string) ($data['domain'] ?? 'erasemc.com'),
			(string) ($data['website'] ?? 'erasemc.com'),
			(int) ($data['hub-item-slot'] ?? 4),
			(int) ($data['query-interval'] ?? 5),
		);

		$spawn = is_array($data['spawn'] ?? null) ? $data['spawn'] : [];
		$this->spawn = new SpawnConfig(
			(string) ($spawn['world'] ?? 'world'),
			(float) ($spawn['x'] ?? 0.5),
			(float) ($spawn['y'] ?? 100.0),
			(float) ($spawn['z'] ?? 0.5),
			(float) ($spawn['yaw'] ?? 0.0),
			(float) ($spawn['pitch'] ?? 0.0),
		);

		$servers = is_array($data['servers'] ?? null) ? $data['servers'] : [];
		foreach ($servers as $id => $server) {
			if (!is_array($server)) {
				continue;
			}

			$npc = null;
			if (is_array($server['npc'] ?? null)) {
				$npcData = $server['npc'];
				$npc = new NpcSpawnConfig(
					(float) ($npcData['x'] ?? 0.5),
					(float) ($npcData['y'] ?? 100.0),
					(float) ($npcData['z'] ?? 0.5),
					(float) ($npcData['yaw'] ?? 180.0),
					(float) ($npcData['pitch'] ?? 0.0),
				);
			}

			$this->servers[(string) $id] = new ServerConfig(
				(string) $id,
				(string) ($server['display-name'] ?? $id),
				(string) ($server['short-name'] ?? $id),
				(string) ($server['address'] ?? '127.0.0.1'),
				(int) ($server['port'] ?? 19132),
				$npc,
			);
		}

		self::setInstance($this);
	}
}
