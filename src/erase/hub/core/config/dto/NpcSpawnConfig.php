<?php

declare(strict_types=1);

namespace erase\hub\core\config\dto;

final readonly class NpcSpawnConfig
{
	public function __construct(
		public float $x = 0.5,
		public float $y = 100.0,
		public float $z = 0.5,
		public float $yaw = 180.0,
		public float $pitch = 0.0,
	) {
	}
}
