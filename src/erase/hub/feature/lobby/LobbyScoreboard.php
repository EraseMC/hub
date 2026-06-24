<?php

declare(strict_types=1);

namespace erase\hub\feature\lobby;

use pocketmine\utils\TextFormat;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\core\scoreboard\HubScoreboard;
use erase\hub\Hub;

final class LobbyScoreboard extends HubScoreboard
{
	private const string TOP_SEPARATOR = TextFormat::DARK_GRAY . TextFormat::STRIKETHROUGH . "                ";
	private const string BOTTOM_SEPARATOR = TextFormat::DARK_GRAY . TextFormat::STRIKETHROUGH . "                " . TextFormat::RESET;
	private const string BLANK = TextFormat::DARK_AQUA . " ";

	public function setup() : void
	{
		$this->setLines($this->buildLines());
	}

	public function onUpdate() : void
	{
		foreach ($this->buildLines() as $lineNumber => $message) {
			$this->setLine($lineNumber, $message);
		}
	}

	/**
	 * @return array<int, string>
	 */
	private function buildLines() : array
	{
		$serverManager = Hub::getInstance()->getServerManager();

		$lines = [];
		$line = 1;

		$lines[$line++] = self::TOP_SEPARATOR;

		foreach ($serverManager->getServers() as $server) {
			$online = $serverManager->getOnline($server->id) ?? 0;
			$lines[$line++] = Translator::translate(
				KnownTranslationKeys::SCOREBOARD_SERVER_LINE,
				$this->player,
				$server->shortName,
				(string) $online
			);
		}

		$lines[$line++] = self::BLANK;
		$lines[$line++] = Translator::translate(KnownTranslationKeys::SCOREBOARD_DOMAIN, $this->player);
		$lines[$line] = self::BOTTOM_SEPARATOR;

		return $lines;
	}
}
