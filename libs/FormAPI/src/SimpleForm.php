<?php

declare(strict_types=1);

namespace jojoe77777\FormAPI;

use function count;

class SimpleForm extends Form
{
	public const IMAGE_TYPE_PATH = 0;
	public const IMAGE_TYPE_URL = 1;

	/** @var array<int, mixed> */
	private array $labelMap = [];

	public function __construct(?callable $callable)
	{
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = "";
		$this->data["buttons"] = [];
	}

	public function processData(mixed &$data) : void
	{
		if ($data !== null && isset($this->labelMap[$data])) {
			$data = $this->labelMap[$data];
		}
	}

	public function getTitle() : string
	{
		return $this->data["title"];
	}

	public function setTitle(string $title) : void
	{
		$this->data["title"] = $title;
	}

	public function getContent() : string
	{
		return $this->data["content"];
	}

	public function setContent(string $content) : void
	{
		$this->data["content"] = $content;
	}

	/**
	 * @param mixed $label value passed to the callable when this button is pressed
	 */
	public function addButton(string $text, int $imageType = -1, string $imagePath = "", mixed $label = null) : void
	{
		$content = ["text" => $text];
		if ($imageType !== -1) {
			$content["image"]["type"] = $imageType === self::IMAGE_TYPE_PATH ? "path" : "url";
			$content["image"]["data"] = $imagePath;
		}
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}
}
