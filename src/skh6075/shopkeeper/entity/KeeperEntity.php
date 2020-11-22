<?php

namespace skh6075\shopkeeper\entity;


use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use skh6075\shopkeeper\inventory\KeeperInventory;
use skh6075\shopkeeper\Loader;
use skh6075\shopkeeper\ShopKeeper;

class KeeperEntity extends Human{

    /** @var string */
    private $name;


    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
    }

    public function initEntity(): void{
        parent::initEntity();
        if (!$this->namedtag->hasTag("keeper", StringTag::class)) {
            $this->close();
            return;
        }
        $this->name = $this->namedtag->getString("keeper");
        $this->setNameTagAlwaysVisible(true);
    }

    public function saveNBT(): void{
        parent::saveNBT();
        $this->namedtag->setString("keeper", $this->name);
    }

    public function getName(): string{
        return $this->name;
    }

    public function getCompoundTag(): CompoundTag{
        $nbt = new CompoundTag("Offers", [
            new ListTag("Recipes", [])
        ]);

        if (($keeper = Loader::getInstance()->getKeeper($this->getName())) instanceof ShopKeeper) {
            foreach ($keeper->getItmes() as $items) {
                $buy = Item::jsonDeserialize($items[0]);
                $sell = Item::jsonDeserialize($items[1]);

                $nbt->getListTag("Recipes")
                    ->push(new CompoundTag("", [
                        $buy->nbtSerialize(-1, "buyA"),
                        new IntTag("maxUses", 32767),
                        new ByteTag("rewardExp", 0),
                        $sell->nbtSerialize(-1, "sell"),
                        new IntTag("uses", 0),
                        new StringTag("label", "shopkeeper")
                    ]));
            }
        }
        return $nbt;
    }

    public function attack(EntityDamageEvent $source): void{
        $source->setCancelled(true);
        if ($source instanceof EntityDamageByEntityEvent) {
            if (($player = $source->getDamager()) instanceof Player) {
                /** @var Player $player */
                if ($player->isOp() and $player->isSneaking()) {
                    $this->close();
                    return;
                }
                $this->sendKeeperInventory($player);
            }
        }
    }

    public function sendKeeperInventory(Player $player): void{
        $player->addWindow(new KeeperInventory($this));
    }
}