<?php

declare(strict_types=1);

namespace erase\hub\core\utils;

use pocketmine\entity\Skin;
use Ramsey\Uuid\Uuid;
use function chr;
use function str_repeat;

final class SkinFactory
{
	private const int WIDTH = 64;
	private const int HEIGHT = 64;

	public static function solid(int $r, int $g, int $b) : Skin
	{
		$pixel = chr($r & 0xff) . chr($g & 0xff) . chr($b & 0xff) . chr(0xff);
		$data = str_repeat($pixel, self::WIDTH * self::HEIGHT);

		return new Skin(
			Uuid::uuid4()->toString(),
			$data,
			'',
			'',
			''
		);
	}
}
