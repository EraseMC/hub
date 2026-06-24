<?php

declare(strict_types=1);

namespace erase\hub;

use pocketmine\entity\Location;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use erase\hub\core\config\Configuration;
use erase\hub\core\l10n\Translator;
use erase\hub\core\provider\DataProvider;
use erase\hub\core\scoreboard\ScoreboardUpdateTask;
use erase\hub\core\skin\SkinManager;
use erase\hub\core\utils\SkinFactory;
use erase\hub\feature\command\HubCommand;
use erase\hub\feature\command\SetSpawnCommand;
use erase\hub\feature\lobby\LobbyManager;
use erase\hub\feature\npc\NPCManager;
use erase\hub\feature\npc\ServerNPC;
use erase\hub\feature\player\listener\HubPlayerListener;
use erase\hub\feature\server\ServerManager;
use Symfony\Component\Filesystem\Path;
use function count;
use function is_file;

final class Hub extends PluginBase
{
	use SingletonTrait;

	/** @var array<int, array{0: int, 1: int, 2: int}> */
	private const array NPC_COLORS = [
		[85, 255, 85],
		[85, 255, 255],
		[85, 85, 255],
		[255, 170, 0],
		[255, 85, 255],
	];

	private Configuration $configuration;
	private DataProvider $provider;
	private ServerManager $serverManager;
	private LobbyManager $lobbyManager;
	private NPCManager $npcManager;
	private SkinManager $skinManager;

	protected function onLoad() : void
	{
		$autoload = Path::join($this->getFile(), 'vendor', 'autoload.php');
		if (is_file($autoload)) {
			require_once $autoload;
		}
	}

	protected function onEnable() : void
	{
		self::setInstance($this);

		$this->getServer()->getWorldManager()->setAutoSave(false);

		$this->configuration = new Configuration($this);
		Translator::init($this);

		$this->provider = new DataProvider($this);
		$this->skinManager = new SkinManager($this);
		$this->serverManager = new ServerManager($this);
		$this->lobbyManager = new LobbyManager($this);
		$this->npcManager = new NPCManager($this);

		$this->registerListeners();
		$this->registerCommands();

		$this->getScheduler()->scheduleRepeatingTask(new ScoreboardUpdateTask(), 20);

		$this->setupNpcs();
	}

	private function registerListeners() : void
	{
		$pluginManager = $this->getServer()->getPluginManager();
		$pluginManager->registerEvents(new HubPlayerListener(), $this);
		$pluginManager->registerEvents(new HubListener($this), $this);
	}

	private function registerCommands() : void
	{
		$this->getServer()->getCommandMap()->registerAll('hub', [
			new HubCommand($this),
			new SetSpawnCommand($this),
		]);
	}

	private function setupNpcs() : void
	{
		$worldName = $this->configuration->spawn->world;
		$worldManager = $this->getServer()->getWorldManager();

		if (!$worldManager->isWorldLoaded($worldName) && !$worldManager->loadWorld($worldName)) {
			$this->getLogger()->warning("Hub world '$worldName' could not be loaded; NPCs were not spawned.");
			return;
		}

		$world = $worldManager->getWorldByName($worldName);
		if ($world === null) {
			return;
		}

		$index = 0;
		foreach ($this->serverManager->getServers() as $server) {
			if ($server->npc === null) {
				continue;
			}

			$location = new Location(
				$server->npc->x,
				$server->npc->y,
				$server->npc->z,
				$world,
				$server->npc->yaw,
				$server->npc->pitch
			);

			$skin = $server->skin !== '' ? $this->skinManager->load($server->skin) : null;
			if ($skin === null) {
				$color = self::NPC_COLORS[$index % count(self::NPC_COLORS)];
				$skin = SkinFactory::solid($color[0], $color[1], $color[2]);
			}

			$npc = new ServerNPC($location, $skin, $server);
			$this->npcManager->register($npc);
			$index++;
		}
	}

	public function getConfiguration() : Configuration
	{
		return $this->configuration;
	}

	public function getProvider() : DataProvider
	{
		return $this->provider;
	}

	public function getServerManager() : ServerManager
	{
		return $this->serverManager;
	}

	public function getLobbyManager() : LobbyManager
	{
		return $this->lobbyManager;
	}

	public function getNPCManager() : NPCManager
	{
		return $this->npcManager;
	}

	protected function onDisable() : void
	{
		if (isset($this->npcManager)) {
			$this->npcManager->close();
		}
		if (isset($this->provider)) {
			$this->provider->close();
		}
	}
}
