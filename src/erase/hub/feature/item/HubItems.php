<?php

declare(strict_types=1);

namespace erase\hub\feature\item;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\feature\player\HubPlayer;

final class HubItems
{
	public const string TAG_SELECTOR = "hub_selector";

	public static function selector(HubPlayer $player) : Item
	{
		$item = VanillaItems::COMPASS();
		$item->setCustomName(Translator::translate(KnownTranslationKeys::ITEM_SELECTOR_NAME, $player));
		$item->getNamedTag()->setByte(self::TAG_SELECTOR, 1);

		return $item;
	}

	public static function isSelector(Item $item) : bool
	{
		return $item->getNamedTag()->getByte(self::TAG_SELECTOR, 0) === 1;
	}
}
