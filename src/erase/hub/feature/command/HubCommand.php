<?php

declare(strict_types=1);

namespace erase\hub\feature\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\feature\player\HubPlayer;
use erase\hub\Hub;

final class HubCommand extends Command implements PluginOwned
{
	public function __construct(private readonly Hub $plugin)
	{
		parent::__construct('hub', 'Teleport to the hub spawn', '/hub', ['lobby', 'spawn']);
		$this->setPermission('hub.command.hub');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
	{
		if (!$sender instanceof HubPlayer) {
			$sender->sendMessage(Translator::translate(KnownTranslationKeys::COMMAND_ONLY_PLAYER, $sender));
			return true;
		}

		if (!$this->testPermission($sender)) {
			return true;
		}

		$this->plugin->getLobbyManager()->prepare($sender);
		$sender->sendMessage(Translator::translate(KnownTranslationKeys::COMMAND_HUB_SUCCESS, $sender));

		return true;
	}

	public function getOwningPlugin() : Hub
	{
		return $this->plugin;
	}
}
