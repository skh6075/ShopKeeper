<?php


namespace skh6075\shopkeeper\command;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use skh6075\shopkeeper\entity\KeeperEntity;
use skh6075\shopkeeper\Loader;

class DisplayKeeperItemCommand extends Command{

    protected $plugin;


    public function __construct(Loader $plugin) {
        parent::__construct(
            $plugin->getBaseLang()->format("displaykeeper.command.name", [], false),
            $plugin->getBaseLang()->format("displaykeeper.command.description", [], false)
        );
        $this->setPermission("displaykeeper.command.permission");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $player, string $label, array $args): bool{
        if (!$player->hasPermission($this->getPermission())) {
            $player->sendMessage($this->plugin->getBaseLang()->format("command.use.not.permission"));
            return false;
        }
        if (!$player instanceof Player) {
            $player->sendMessage($this->plugin->getBaseLang()->format("command.use.only.ingame"));
            return false;
        }
        $name = array_shift($args) ?? '';
        if (trim($name) === '') {
            $player->sendMessage($this->plugin->getBaseLang()->format("displaykeeper.command.help"));
            return false;
        }
        if (!$this->plugin->getKeeper($name) instanceof KeeperEntity) {
            $player->sendMessage($this->plugin->getBaseLang()->format("displaykeeper.command.failed"));
            return false;
        }

    }
}