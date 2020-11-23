<?php

namespace skh6075\shopkeeper\lang;


final class PluginLang{

    /** @var string */
    private $lang;

    /** @var array */
    private $translates = [];


    public function __construct() {
    }

    /**
     * @param string $lang
     *
     * @return $this
     */
    public function setLang(string $lang): self{
        $this->lang = $lang;
        return $this;
    }

    /**
     * @param array $translates
     *
     * @return $this
     */
    public function setTranslates(array $translates = []): self{
        $this->translates = $translates;
        return $this;
    }

    /**
     * @param string $key
     * @param array $replaces
     * @param bool $pushPrefix
     *
     * @return string
     */
    public function format(string $key, array $replaces = [], bool $pushPrefix = true): string{
        $format = $pushPrefix ? $this->translates["prefix"] : "";
        $format .= $this->translates[$key];

        foreach ($replaces as $old => $new) {
            $format = str_replace($old, $new, $format);
        }
        return $format;
    }
}