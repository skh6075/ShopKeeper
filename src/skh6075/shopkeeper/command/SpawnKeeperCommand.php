<?php

namespace skh6075\shopkeeper\command;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use skh6075\shopkeeper\Loader;
use skh6075\shopkeeper\ShopKeeper;

use function array_shift;
use function deg2rad;
use function sin;
use function cos;

class SpawnKeeperCommand extends Command{

    /** @var Loader */
    protected $plugin;


    public function __construct(Loader $plugin) {
        parent::__construct(
            $plugin->getBaseLang()->format("spawnkeeper.command.name", [], false),
            $plugin->getBaseLang()->format("spawnkeeper.command.description", [], false)
        );
        $this->setPermission("spawnkeeper.command.permission");
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
        $radius = array_shift($args) ?? '';
        if (trim($name) === '' or trim($radius) === '' or !is_numeric($radius)) {
            $player->sendMessage($this->plugin->getBaseLang()->format("spawnkeeper.command.help"));
            return false;
        }
        if (!$this->plugin->getKeeper($name) instanceof ShopKeeper) {
            $this->plugin->addKeeper($name);
        }
        $yaw = deg2rad($player->getYaw());
        $pos = new Position($player->x + $radius * -sin($yaw), $player->y, $player->z + $radius * cos($yaw));
        $pos->y = $player->getLevel()->getHighestBlockAt($pos->x, $pos->z) + 1;

        $nbt = Entity::createBaseNBT($pos->asVector3(), null, 180.0, $player->pitch);
        $nbt->setTag(new CompoundTag("Skin", [
            new StringTag("Name", $player->getSkin()->getSkinId()),
            new ByteArrayTag("Data", $player->getSkin()->getSkinData()),
            new ByteArrayTag("CapeData", $player->getSkin()->getCapeData()),
            new StringTag("GeometryName", $player->getSkin()->getGeometryName()),
            new ByteArrayTag("GeometryData", $player->getSkin()->getGeometryData())
        ]));
        $nbt->setString("keeper", $name);
        $entity = Entity::createEntity("KeeperEntity", $player->level, $nbt);
        $entity->spawnToAll();
        $entity->setNameTag($name);
        $entity->setNameTagAlwaysVisible(true);
        $player->sendMessage($this->plugin->getBaseLang()->format("spawnkeeper.command.successed"));
        return true;
    }
}