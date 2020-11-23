<?php


namespace skh6075\shopkeeper\listener;


use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use skh6075\shopkeeper\entity\KeeperEntity;
use skh6075\shopkeeper\Loader;
use skh6075\shopkeeper\PluginQueue;

class EventListener implements Listener{

    /** @var Loader */
    protected $plugin;


    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void{
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof ActorEventPacket) {
            if ($packet->event === $packet::COMPLETE_TRADE) {
                if (!isset (PluginQueue::$queue[strtolower($player->getName())])) {
                    return;
                }
                /** @var KeeperEntity $entity */
                $entity = PluginQueue::$queue[strtolower($player->getName())]["entity"];
                $trades = $entity->getCompoundTag()->getListTag("Recipes")->get($packet->data);
                if ($trades instanceof CompoundTag) {
                    $buyItem = Item::nbtDeserialize($trades->getCompoundTag("buyA"));
                    $sellItem = Item::nbtDeserialize($trades->getCompoundTag("sell"));
                    if (!$player->getInventory()->canAddItem($sellItem)) {
                        $event->setCancelled(true);
                    }
                    if (!$player->getInventory()->contains($buyItem)) {
                        $event->setCancelled(true);
                    }
                    $player->getInventory()->removeItem($buyItem);
                    $player->getInventory()->addItem($sellItem);
                    unset(PluginQueue::$queue[strtolower($player->getName())]);
                }
            }
        } else if ($packet instanceof InventoryTransactionPacket) {
            if ($packet->transactionType === $packet::TYPE_NORMAL) {
                foreach ($packet->actions as $action) {
                    if ($action instanceof NetworkInventoryAction) {
                        if (!isset(PluginQueue::$queue[strtolower($player->getName())])) {
                            return;
                        }
                        if ($action->windowId === PluginQueue::$queue[strtolower($player->getName())] ["windowId"]) {
                            continue;
                        }
                        $player->getInventory()->addItem($action->oldItem);
                        $player->getInventory()->removeItem($action->newItem);
                        unset(PluginQueue::$queue[strtolower($player->getName())]);
                        break;
                    }
                }
            }
        }
    }
}