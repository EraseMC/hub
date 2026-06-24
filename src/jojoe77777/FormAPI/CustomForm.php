<?php

declare(strict_types=1);

namespace jojoe77777\FormAPI;

use function count;
use function is_array;

class CustomForm extends Form
{
	/** @var array<int, mixed> */
	private array $labelMap = [];

	public function __construct(?callable $callable)
	{
		parent::__construct($callable);
		$this->data["type"] = "custom_form";
		$this->data["title"] = "";
		$this->data["content"] = [];
	}

	public function processData(mixed &$data) : void
	{
		if (is_array($data)) {
			$new = [];
			foreach ($data as $i => $v) {
				$new[$this->labelMap[$i] ?? $i] = $v;
			}
			$data = $new;
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

	public function addLabel(string $text, ?string $label = null) : void
	{
		$this->addContent(["type" => "label", "text" => $text]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addToggle(string $text, bool $default = false, ?string $label = null) : void
	{
		$this->addContent(["type" => "toggle", "text" => $text, "default" => $default]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addSlider(string $text, float $min, float $max, float $step = 1.0, float $default = -1.0, ?string $label = null) : void
	{
		$slider = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
		if ($step !== -1.0) {
			$slider["step"] = $step;
		}
		if ($default !== -1.0) {
			$slider["default"] = $default;
		}
		$this->addContent($slider);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	/**
	 * @param string[] $steps
	 */
	public function addStepSlider(string $text, array $steps, int $defaultIndex = -1, ?string $label = null) : void
	{
		$slider = ["type" => "step_slider", "text" => $text, "steps" => $steps];
		if ($defaultIndex !== -1) {
			$slider["default"] = $defaultIndex;
		}
		$this->addContent($slider);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	/**
	 * @param string[] $options
	 */
	public function addDropdown(string $text, array $options, int $default = 0, ?string $label = null) : void
	{
		$this->addContent(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	public function addInput(string $text, string $placeholder = "", string $default = "", ?string $label = null) : void
	{
		$this->addContent(["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default]);
		$this->labelMap[] = $label ?? count($this->labelMap);
	}

	/**
	 * @param array<string, mixed> $content
	 */
	private function addContent(array $content) : void
	{
		$this->data["content"][] = $content;
	}
}
