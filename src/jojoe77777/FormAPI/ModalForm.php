<?php

declare(strict_types=1);

namespace jojoe77777\FormAPI;

class ModalForm extends Form
{
	public function __construct(?callable $callable)
	{
		parent::__construct($callable);
		$this->data["type"] = "modal";
		$this->data["title"] = "";
		$this->data["content"] = "";
		$this->data["button1"] = "";
		$this->data["button2"] = "";
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

	public function getButton1() : string
	{
		return $this->data["button1"];
	}

	public function setButton1(string $button1) : void
	{
		$this->data["button1"] = $button1;
	}

	public function getButton2() : string
	{
		return $this->data["button2"];
	}

	public function setButton2(string $button2) : void
	{
		$this->data["button2"] = $button2;
	}
}
