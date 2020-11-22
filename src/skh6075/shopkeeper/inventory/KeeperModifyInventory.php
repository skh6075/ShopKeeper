<?php


namespace skh6075\shopkeeper\inventory;


use pocketmine\block\BlockIds;
use pocketmine\inventory\BaseInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\tile\Spawnable;
use skh6075\shopkeeper\ShopKeeper;

class KeeperModifyInventory extends BaseInventory{

    /** @var ?Vector3 */
    protected $vector = null;

    /** @var ShopKeeper */
    protected $keeper;


    public function __construct(ShopKeeper $keeper) {
        parent::__construct([], 27);
        $this->keeper = $keeper;
    }

    public function getName(): string{
        return "KeeperModifyInventory";
    }

    public function getDefaultSize(): int{
        return 27;
    }

    public function onOpen(Player $who): void{
        parent::onOpen($who);

        $this->vector = $who->add(0, 5)->floor();

        $x = $this->vector->x;
        $y = $this->vector->y;
        $z = $this->vector->z;

        $packet = new UpdateBlockPacket();
        $packet->x = $x;
        $packet->y = $y;
        $packet->z = $z;
        $packet->blockRuntimeId = RuntimeBlockMapping::toStaticRuntimeId(BlockIds::CHEST);
        $packet->flags = UpdateBlockPacket::FLAG_ALL_PRIORITY;
        $who->sendDataPacket($packet);

        $packet = new BlockActorDataPacket();
        $packet->x = $x;
        $packet->y = $y;
        $packet->z = $z;
        $packet->namedtag = (new NetworkLittleEndianNBTStream())->write(new CompoundTag("", [
            new StringTag("id", "Chest"),
            new IntTag("x", $x),
            new IntTag("y", $y),
            new IntTag("z", $z),
            new StringTag("CustomName", $this->keeper->getName())
        ]));
        $who->sendDataPacket($packet);

        $packet = new ContainerOpenPacket();
        $packet->x = $x;
        $packet->y = $y;
        $packet->z = $z;
        $packet->windowId = $who->getWindowId($this);

        $this->sendContents($who);
    }

    public function onClose(Player $who): void{
        parent::onClose($who);

        $x = $this->vector->x;
        $y = $this->vector->y;
        $z = $this->vector->z;

        $block = $who->level->getBlock($this->vector);

        $packet = new UpdateBlockPacket();
        $packet->x = $x;
        $packet->y = $y;
        $packet->z = $z;
        $packet->blockRuntimeId = RuntimeBlockMapping::toStaticRuntimeId($block->getId(), $block->getDamage());
        $packet->flags = UpdateBlockPacket::FLAG_ALL_PRIORITY;
        $who->sendDataPacket($packet);

        if (($tile = $who->getLevel()->getBlock($this->vector)) instanceof  Spawnable) {
            /** @var Spawnable $tile */
            $who->sendDataPacket($tile->createSpawnPacket());
        } else {
            $packet = new BlockActorDataPacket();
            $packet->x = $x;
            $packet->y = $y;
            $packet->z = $z;
            $packet->namedtag = (new NetworkLittleEndianNBTStream())->write(new CompoundTag());
            $who->sendDataPacket($packet);
        }
    }
}