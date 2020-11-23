<?php

namespace skh6075\shopkeeper;

use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use skh6075\shopkeeper\command\DisplayKeeperItemCommand;
use skh6075\shopkeeper\command\SpawnKeeperCommand;
use skh6075\shopkeeper\entity\KeeperEntity;
use skh6075\shopkeeper\lang\PluginLang;
use skh6075\shopkeeper\listener\EventListener;

class Loader extends PluginBase{

    /** @var ?Loader */
    private static $instance = null;

    /** @var ?PluginLang */
    private $lang = null;

    /** @var ShopKeeper[] */
    private static $keepers = [];


    public static function getInstance(): ?Loader{
        return self::$instance;
    }

    public function onLoad(): void{
        if (self::$instance === null) {
            self::$instance = $this;
        }
        Entity::registerEntity(KeeperEntity::class, true, ["KeeperEntity"]);
    }

    public function onEnable(): void{
        $this->saveResource("lang/kor.yml");
        $this->saveResource("lang/eng.yml");
        $this->saveResource("keepers.json");
        $json = json_decode(file_get_contents($this->getDataFolder() . "keepers.json"), true);
        foreach ($json as $key => $data) {
            self::$keepers[$key] = ShopKeeper::data($data);
        }
        $this->lang = new PluginLang();
        $this->lang
            ->setLang(($lang = strtolower($this->getServer()->getLanguage()->getLang())))
            ->setTranslates(yaml_parse(file_get_contents($this->getDataFolder() . "lang/" . $lang . ".yml")));

        $this->getServer()->getCommandMap()->registerAll(strtolower($this->getName()), [
            new SpawnKeeperCommand($this),
            new DisplayKeeperItemCommand($this)
        ]);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable(): void{
        $data = [];
        foreach (self::$keepers as $name => $class) {
            $data[$name] = $class->jsonSerialize();
        }
        file_put_contents($this->getDataFolder() . "keepers.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function getBaseLang(): PluginLang{
        return $this->lang;
    }

    public function getKeeper(string $name): ?ShopKeeper{
        return self::$keepers[$name] ?? null;
    }

    public function addKeeper(string $name): void{
        self::$keepers[$name] = ShopKeeper::data([
            "name" => $name,
            "items" => []
        ]);
    }
}