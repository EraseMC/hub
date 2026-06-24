<?php

declare(strict_types=1);

namespace jojoe77777\FormAPI;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

abstract class Form implements IForm
{
	/** @var array<string, mixed> */
	protected array $data = [];

	/** @var (callable(Player, mixed): void)|null */
	private $callable;

	/**
	 * @param (callable(Player, mixed): void)|null $callable
	 */
	public function __construct(?callable $callable)
	{
		$this->callable = $callable;
	}

	/**
	 * @return (callable(Player, mixed): void)|null
	 */
	public function getCallable() : ?callable
	{
		return $this->callable;
	}

	/**
	 * @param (callable(Player, mixed): void)|null $callable
	 */
	public function setCallable(?callable $callable) : void
	{
		$this->callable = $callable;
	}

	public function handleResponse(Player $player, $data) : void
	{
		$this->processData($data);
		$callable = $this->getCallable();
		if ($callable !== null) {
			$callable($player, $data);
		}
	}

	/**
	 * Called before the callable, gives subclasses a chance to remap the raw response.
	 *
	 * @param mixed $data
	 */
	public function processData(mixed &$data) : void
	{
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize() : array
	{
		return $this->data;
	}
}
