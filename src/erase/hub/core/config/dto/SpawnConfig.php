<?php

declare(strict_types=1);

namespace erase\hub\core\config\dto;

final readonly class SpawnConfig
{
	public function __construct(
		public string $world = "world",
		public float $x = 0.5,
		public float $y = 100.0,
		public float $z = 0.5,
		public float $yaw = 0.0,
		public float $pitch = 0.0,
	) {
	}
}
