<?php

declare(strict_types=1);

namespace erase\hub\core\config\dto;

final readonly class GeneralConfig
{
	public function __construct(
		public string $title = "&l&cERASE",
		public string $prefix = "&l&cErase &r&7| &r",
		public string $domain = "erasemc.com",
		public string $website = "erasemc.com",
		public int $hubItemSlot = 4,
		public int $queryIntervalSeconds = 5,
	) {
	}
}
