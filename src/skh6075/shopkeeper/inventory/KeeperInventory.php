<?php


namespace skh6075\shopkeeper\inventory;


use pocketmine\inventory\BaseInventory;
use pocketmine\inventory\ContainerInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\Player;
use skh6075\shopkeeper\entity\KeeperEntity;
use skh6075\shopkeeper\PluginQueue;

class KeeperInventory extends ContainerInventory{

    /** @var KeeperEntity */
    private $entity;


    public function __construct(KeeperEntity $entity) {
        parent::__construct(new Vector3(), [], 27);
        $this->entity = $entity;
    }

    public function getName(): string{
        return "KeeperInventory";
    }

    public function getDefaultSize(): int{
        return 3;
    }

    public function getNetworkType(): int{
        return WindowTypes::TRADING;
    }

    /**
     * @param Player $who
     */
    public function onOpen(Player $who): void {
        BaseInventory::onOpen($who);

        $pk = new UpdateTradePacket();
        $pk->displayName = $this->entity->getName();
        $pk->windowId = $id = $who->getWindowId($this);
        $pk->isWilling = false;
        $pk->isV2Trading = false;
        $pk->tradeTier = 1;
        $pk->playerEid = $who->getId();
        $pk->traderEid = $this->entity->getId();
        $pk->offers = (new NetworkLittleEndianNBTStream())->write($this->entity->getCompoundTag());
        $who->sendDataPacket($pk);

        PluginQueue::$queue[strtolower($who->getName())] = [
            "windowId" => $id,
            "entity" => $this->entity
        ];
    }

    /**
     * @param Player $who
     */
    public function onClose(Player $who): void{
        BaseInventory::onClose($who);
        unset(PluginQueue::$queue[strtolower($who->getName())]);
    }
}