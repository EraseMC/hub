<?php

declare(strict_types=1);

namespace erase\hub\feature\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\Config;
use erase\hub\core\config\dto\SpawnConfig;
use erase\hub\core\l10n\KnownTranslationKeys;
use erase\hub\core\l10n\Translator;
use erase\hub\feature\player\HubPlayer;
use erase\hub\Hub;

final class SetSpawnCommand extends Command implements PluginOwned
{
	public function __construct(private readonly Hub $plugin)
	{
		parent::__construct('sethubspawn', 'Set the hub spawn to your current location', '/sethubspawn');
		$this->setPermission('hub.command.setspawn');
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

		$location = $sender->getLocation();
		$world = $location->getWorld()->getFolderName();

		$config = new Config($this->plugin->getDataFolder() . 'config.yml', Config::YAML);
		$config->setNested('spawn.world', $world);
		$config->setNested('spawn.x', $location->x);
		$config->setNested('spawn.y', $location->y);
		$config->setNested('spawn.z', $location->z);
		$config->setNested('spawn.yaw', $location->yaw);
		$config->setNested('spawn.pitch', $location->pitch);
		$config->save();

		$this->plugin->getConfiguration()->spawn = new SpawnConfig(
			$world,
			$location->x,
			$location->y,
			$location->z,
			$location->yaw,
			$location->pitch
		);

		$sender->sendMessage(Translator::translate(KnownTranslationKeys::COMMAND_SETSPAWN_SUCCESS, $sender));

		return true;
	}

	public function getOwningPlugin() : Hub
	{
		return $this->plugin;
	}
}
