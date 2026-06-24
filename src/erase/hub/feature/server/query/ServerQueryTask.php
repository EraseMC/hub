<?php

declare(strict_types=1);

namespace erase\hub\feature\server\query;

use pocketmine\scheduler\AsyncTask;
use erase\hub\Hub;
use function explode;
use function fclose;
use function fread;
use function fwrite;
use function gethostbyname;
use function is_array;
use function json_decode;
use function json_encode;
use function microtime;
use function ord;
use function pack;
use function random_int;
use function stream_set_blocking;
use function stream_set_timeout;
use function stream_socket_client;
use function strlen;
use function substr;
use function unpack;
use const PHP_INT_MAX;
use const STREAM_CLIENT_CONNECT;

final class ServerQueryTask extends AsyncTask
{
	private const string MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
	private const int ID_UNCONNECTED_PING = 0x01;
	private const int ID_UNCONNECTED_PONG = 0x1c;
	private const float TIMEOUT_SECONDS = 1.0;

	private string $targets;

	/**
	 * @param array<string, array{0: string, 1: int}> $targets id => [address, port]
	 */
	public function __construct(array $targets)
	{
		$this->targets = json_encode($targets);
	}

	public function onRun() : void
	{
		$targets = json_decode($this->targets, true);
		if (!is_array($targets)) {
			$this->setResult(json_encode([]));
			return;
		}

		$results = [];
		foreach ($targets as $id => $target) {
			$results[(string) $id] = self::ping((string) $target[0], (int) $target[1]);
		}

		$this->setResult(json_encode($results));
	}

	private static function ping(string $address, int $port) : ?int
	{
		$ip = gethostbyname($address);

		$socket = @stream_socket_client("udp://$ip:$port", $errno, $errstr, self::TIMEOUT_SECONDS, STREAM_CLIENT_CONNECT);
		if ($socket === false) {
			return null;
		}

		stream_set_blocking($socket, true);
		stream_set_timeout($socket, 1);

		$packet = pack("C", self::ID_UNCONNECTED_PING)
			. pack("J", (int) (microtime(true) * 1000))
			. self::MAGIC
			. pack("J", random_int(0, PHP_INT_MAX));

		@fwrite($socket, $packet);

		$response = @fread($socket, 65535);
		@fclose($socket);

		if ($response === false || strlen($response) < 35) {
			return null;
		}

		if (ord($response[0]) !== self::ID_UNCONNECTED_PONG) {
			return null;
		}

		$offset = 33;
		$length = unpack("n", substr($response, $offset, 2))[1] ?? 0;
		if ($length <= 0) {
			return null;
		}

		$info = substr($response, $offset + 2, $length);
		$parts = explode(";", $info);

		if (!isset($parts[4]) || $parts[4] === "") {
			return null;
		}

		return (int) $parts[4];
	}

	public function onCompletion() : void
	{
		$result = $this->getResult();
		if (!is_string($result)) {
			return;
		}

		$results = json_decode($result, true);
		if (!is_array($results)) {
			return;
		}

		Hub::getInstance()->getServerManager()->applyResults($results);
	}
}
