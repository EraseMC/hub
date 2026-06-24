<?php

declare(strict_types=1);

namespace erase\hub\feature\menu;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat;
use erase\hub\core\config\dto\ServerConfig;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\feature\player\HubPlayer;
use erase\hub\Hub;
use function array_values;

final class ServerForms
{
	public static function sendSelector(HubPlayer $player) : void
	{
		$serverManager = Hub::getInstance()->getServerManager();
		$servers = array_values($serverManager->getServers());

		$form = new SimpleForm(static function (HubPlayer $player, ?int $data) use ($servers) : void {
			if ($data === null || !isset($servers[$data])) {
				return;
			}
			self::connect($player, $servers[$data]);
		});

		$form->setTitle(Translator::translate(KnownTranslationKeys::FORM_SELECTOR_TITLE, $player));
		$form->setContent(Translator::translate(KnownTranslationKeys::FORM_SELECTOR_CONTENT, $player));

		foreach ($servers as $server) {
			$form->addButton(Translator::translate(
				KnownTranslationKeys::FORM_SELECTOR_BUTTON,
				$player,
				$server->displayName,
				self::onlineText($player, $server)
			));
		}

		$player->sendForm($form);
	}

	public static function sendServer(HubPlayer $player, ServerConfig $server) : void
	{
		$form = new SimpleForm(static function (HubPlayer $player, ?int $data) use ($server) : void {
			if ($data === 0) {
				self::connect($player, $server);
			}
		});

		$form->setTitle(Translator::translate(KnownTranslationKeys::FORM_SERVER_TITLE, $player, $server->displayName));
		$form->setContent(Translator::translate(
			KnownTranslationKeys::FORM_SERVER_CONTENT,
			$player,
			$server->displayName,
			self::onlineText($player, $server)
		));
		$form->addButton(Translator::translate(KnownTranslationKeys::FORM_BUTTON_CONNECT, $player));
		$form->addButton(Translator::translate(KnownTranslationKeys::FORM_BUTTON_CLOSE, $player));

		$player->sendForm($form);
	}

	public static function connect(HubPlayer $player, ServerConfig $server) : void
	{
		if (!$player->isConnected()) {
			return;
		}

		$serverManager = Hub::getInstance()->getServerManager();
		$prefix = TextFormat::colorize(Hub::getInstance()->getConfiguration()->general->prefix);

		if (!$serverManager->isOnline($server->id)) {
			$player->sendMessage($prefix . Translator::translate(KnownTranslationKeys::CONNECT_OFFLINE, $player));
			return;
		}

		$player->sendMessage($prefix . Translator::translate(KnownTranslationKeys::CONNECTING, $player, $server->displayName));
		$player->transfer(
			$server->address,
			$server->port,
			Translator::translate(KnownTranslationKeys::CONNECT_TRANSFER_MESSAGE, $player)
		);
	}

	public static function onlineText(HubPlayer|string $locale, ServerConfig $server) : string
	{
		$online = Hub::getInstance()->getServerManager()->getOnline($server->id);

		return $online !== null
			? (string) $online
			: Translator::translate(KnownTranslationKeys::STATUS_OFFLINE, $locale);
	}
}
