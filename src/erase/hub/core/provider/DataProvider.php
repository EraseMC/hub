<?php

declare(strict_types=1);

namespace erase\hub\core\provider;

use SQLite3;
use erase\hub\feature\player\HubPlayer;
use erase\hub\Hub;
use function time;

final class DataProvider
{
	private SQLite3 $db;

	public function __construct(Hub $plugin)
	{
		$this->db = new SQLite3($plugin->getDataFolder() . 'hub.sqlite');
		$this->db->busyTimeout(5000);
		$this->db->exec('PRAGMA journal_mode = WAL;');
		$this->db->exec(
			'CREATE TABLE IF NOT EXISTS players(
				uuid TEXT PRIMARY KEY,
				name TEXT NOT NULL,
				last_join INTEGER NOT NULL,
				joins INTEGER NOT NULL DEFAULT 0
			);'
		);
	}

	public function recordJoin(HubPlayer $player) : void
	{
		$stmt = $this->db->prepare(
			'INSERT INTO players(uuid, name, last_join, joins) VALUES(:uuid, :name, :time, 1)
			ON CONFLICT(uuid) DO UPDATE SET name = :name, last_join = :time, joins = joins + 1;'
		);
		if ($stmt === false) {
			return;
		}

		$stmt->bindValue(':uuid', $player->getUniqueId()->toString(), SQLITE3_TEXT);
		$stmt->bindValue(':name', $player->getName(), SQLITE3_TEXT);
		$stmt->bindValue(':time', time(), SQLITE3_INTEGER);
		$stmt->execute();
		$stmt->close();
	}

	public function getJoins(string $uuid) : int
	{
		$stmt = $this->db->prepare('SELECT joins FROM players WHERE uuid = :uuid;');
		if ($stmt === false) {
			return 0;
		}

		$stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
		$result = $stmt->execute();
		$joins = 0;
		if ($result !== false) {
			$row = $result->fetchArray(SQLITE3_ASSOC);
			if ($row !== false) {
				$joins = (int) $row['joins'];
			}
			$result->finalize();
		}
		$stmt->close();

		return $joins;
	}

	public function close() : void
	{
		$this->db->close();
	}
}
