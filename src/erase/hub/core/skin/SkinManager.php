<?php

declare(strict_types=1);

namespace erase\hub\core\skin;

use pocketmine\entity\Skin;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Path;
use erase\hub\Hub;
use function chr;
use function getimagesize;
use function imagealphablending;
use function imagecolorat;
use function imagecreatefrompng;
use function imagedestroy;
use function imagesavealpha;
use function in_array;
use function is_file;
use function round;
use const IMAGETYPE_PNG;

/**
 * Loads NPC skins from PNG files. Default skins ship in resources/skins and are
 * copied into the data folder on first run, so server owners can replace them
 * with their own textures.
 */
final class SkinManager
{
	private const array VALID_SIZES = [
		[64, 32],
		[64, 64],
		[128, 128],
	];

	/** @var array<string, Skin> */
	private array $cache = [];

	public function __construct(private readonly Hub $plugin)
	{
	}

	public function load(string $file) : ?Skin
	{
		if (isset($this->cache[$file])) {
			return $this->cache[$file];
		}

		$resourcePath = Path::join('skins', $file);
		$this->plugin->saveResource($resourcePath, false);

		$full = Path::join($this->plugin->getDataFolder(), $resourcePath);
		if (!is_file($full)) {
			return null;
		}

		$data = self::convertToSkinData($full);
		if ($data === null) {
			return null;
		}

		return $this->cache[$file] = new Skin(Uuid::uuid4()->toString(), $data, '', '', '');
	}

	private static function convertToSkinData(string $path) : ?string
	{
		$size = @getimagesize($path);
		if ($size === false || $size[2] !== IMAGETYPE_PNG) {
			return null;
		}

		$width = $size[0];
		$height = $size[1];
		if (!in_array([$width, $height], self::VALID_SIZES, true)) {
			return null;
		}

		$image = @imagecreatefrompng($path);
		if ($image === false) {
			return null;
		}

		imagealphablending($image, false);
		imagesavealpha($image, true);

		$data = '';
		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {
				$argb = imagecolorat($image, $x, $y);
				$a = (int) round((127 - (($argb >> 24) & 0x7f)) / 127 * 255);
				$r = ($argb >> 16) & 0xff;
				$g = ($argb >> 8) & 0xff;
				$b = $argb & 0xff;
				$data .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}

		imagedestroy($image);

		return $data;
	}
}
