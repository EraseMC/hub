<?php

declare(strict_types=1);

namespace erase\hub\core\scoreboard;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\utils\TextFormat;
use erase\hub\feature\player\HubPlayer;
use function array_keys;
use function count;

abstract class Scoreboard
{
	/** @var array<int, string> */
	private array $lines = [];
	/** @var array<int, int> */
	private array $dirtyLines = [];
	/** @var array<int, int> */
	private array $removedLines = [];

	public function __construct(
		protected readonly HubPlayer $player
	) {
	}

	final public function sendObjective() : void
	{
		if (!$this->player->isConnected()) {
			return;
		}

		$this->remove();
		$this->player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(
			SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR,
			'dummy',
			$this->title(),
			'dummy',
			SetDisplayObjectivePacket::SORT_ORDER_ASCENDING
		));

		foreach (array_keys($this->lines) as $lineNumber) {
			$this->dirtyLines[$lineNumber] = $lineNumber;
		}
	}

	final public function remove() : void
	{
		if (!$this->player->isConnected()) {
			return;
		}

		$this->player->getNetworkSession()->sendDataPacket(RemoveObjectivePacket::create('dummy'));
	}

	abstract public function setup() : void;

	public function getLine(int $lineNumber) : ?string
	{
		return $this->lines[$lineNumber] ?? null;
	}

	public function setLine(int $lineNumber, string $message, bool $immediate = false) : void
	{
		if ($lineNumber < 1 || $lineNumber > 15) {
			throw new InvalidArgumentException('Line ' . $lineNumber . ' out of bounds');
		}
		if (($this->lines[$lineNumber] ?? null) === $message) {
			return;
		}
		$this->lines[$lineNumber] = $message;
		$this->dirtyLines[$lineNumber] = $lineNumber;
		if ($immediate) {
			$this->flushUpdates();
		}
	}

	/**
	 * @param array<int, string|null> $lines
	 */
	public function setLines(array $lines, bool $immediate = false) : void
	{
		$empty = TextFormat::BLACK;
		$knownLines = [];
		foreach ($lines as $lineNumber => $message) {
			$knownLines[$lineNumber] = true;
			$this->setLine($lineNumber, $message === null ? $empty : $message);
			if ($message === null) {
				$empty .= $empty;
			}
		}

		foreach (array_keys($this->lines) as $lineNumber) {
			if (!isset($knownLines[$lineNumber])) {
				unset($this->lines[$lineNumber], $this->dirtyLines[$lineNumber]);
				$this->removedLines[$lineNumber] = $lineNumber;
			}
		}

		if ($immediate) {
			$this->flushUpdates();
		}
	}

	final public function flushUpdates() : void
	{
		if (!$this->player->isConnected()) {
			$this->dirtyLines = [];
			$this->removedLines = [];
			return;
		}

		$removedEntries = [];
		foreach ($this->removedLines as $lineNumber) {
			$entry = new ScorePacketEntry();
			$entry->scoreboardId = $lineNumber;
			$entry->objectiveName = 'dummy';
			$entry->score = $lineNumber;
			$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
			$entry->customName = '';
			$removedEntries[] = $entry;
		}

		if (count($removedEntries) > 0) {
			$this->player->getNetworkSession()->sendDataPacket(SetScorePacket::create(
				SetScorePacket::TYPE_REMOVE,
				$removedEntries
			));
		}

		$entries = [];
		foreach ($this->dirtyLines as $lineNumber) {
			$entry = new ScorePacketEntry();
			$entry->scoreboardId = $lineNumber;
			$entry->objectiveName = 'dummy';
			$entry->score = $lineNumber;
			$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
			$entry->customName = $this->lines[$lineNumber];
			$entries[] = $entry;
		}

		if (count($entries) > 0) {
			$this->player->getNetworkSession()->sendDataPacket(SetScorePacket::create(
				SetScorePacket::TYPE_REMOVE,
				$entries
			));
			$this->player->getNetworkSession()->sendDataPacket(SetScorePacket::create(
				SetScorePacket::TYPE_CHANGE,
				$entries
			));
		}

		$this->dirtyLines = [];
		$this->removedLines = [];
	}

	public function __destruct()
	{
		if ($this->player->isConnected()) {
			$this->remove();
		}
	}

	abstract protected function title() : string;

	abstract public function onUpdate() : void;
}
