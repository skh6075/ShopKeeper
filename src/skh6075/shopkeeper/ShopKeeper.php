<?php


namespace skh6075\shopkeeper;


class ShopKeeper implements \JsonSerializable{

    protected $name;

    protected $items = [];


    public function __construct (string $name, array $items = []) {
        $this->name = $name;
        $this->items = $items;
    }

    public static function data(array $data): self{
        return new ShopKeeper(
            (string) $data["name"],
            (array) $data["items"]
        );
    }

    public function jsonSerialize(): array{
        return [
            "name" => $this->name,
            "items" => $this->items
        ];
    }

    public function getName(): string{
        return $this->name;
    }

    public function getItmes(): array{
        return $this->items;
    }

    public function setItems(int $index, array $item): void{
        $this->items[$index] = $item;
    }
}