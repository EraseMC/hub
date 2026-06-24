<?php

declare(strict_types=1);

namespace erase\hub\core\config\dto;

final readonly class ServerConfig
{
	public function __construct(
		public string $id,
		public string $displayName,
		public string $shortName,
		public string $address,
		public int $port,
		public ?NpcSpawnConfig $npc = null,
	) {
	}
}
