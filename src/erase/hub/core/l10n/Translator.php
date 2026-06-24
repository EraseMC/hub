<?php

declare(strict_types=1);

namespace erase\hub\core\l10n;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use erase\hub\Hub;
use Symfony\Component\Filesystem\Path;
use function array_map;
use function is_string;
use function parse_ini_file;
use function sprintf;
use const INI_SCANNER_RAW;

final class Translator
{
	public const string DEFAULT_LOCALE = self::ENGLISH;
	public const string ENGLISH = 'en_US';
	public const string RUSSIAN = 'ru_RU';

	public const array LOCALES = [
		self::ENGLISH,
		self::RUSSIAN,
	];

	/** @var string[][] */
	private static array $translations = [];

	public static function init(Hub $plugin) : void
	{
		foreach (self::LOCALES as $locale) {
			$plugin->saveResource($path = Path::join('l10n', $locale . '.ini'), true);
			self::$translations[$locale] = array_map(
				'\stripcslashes',
				parse_ini_file(Path::join($plugin->getDataFolder(), $path), false, INI_SCANNER_RAW)
			);
		}
	}

	public static function translate(string|KnownTranslationKeys $key, CommandSender|string $locale = self::DEFAULT_LOCALE, mixed ...$args) : string
	{
		$key = $key instanceof KnownTranslationKeys ? $key->value : $key;

		if ($locale instanceof Player) {
			$locale = $locale->getLocale();
		} elseif (!is_string($locale)) {
			$locale = self::DEFAULT_LOCALE;
		}

		if (!isset(self::$translations[$locale])) {
			$locale = self::DEFAULT_LOCALE;
		}

		$translation = self::$translations[$locale][$key] ?? self::$translations[self::DEFAULT_LOCALE][$key] ?? $key;

		$translated = [] === $args ? $translation : sprintf($translation, ...$args);
		return TextFormat::colorize($translated);
	}
}
