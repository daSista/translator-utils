<?php

namespace Translator\SourceCode\TranslateIterator;

class JsonView implements TranslateIteratorInterface
{
    private $translations = array();

    public function select($filePath)
    {
        $jsonData = json_decode(file_get_contents($filePath), true);
        $translations = array();

        array_walk_recursive(
            $jsonData,

            function ($jsonValue, $jsonKey) use (&$translations)
            {
                if ('i18nKey' == $jsonKey) {
                    $translations[$jsonValue] = null;
                }
            }
        );

        $this->translations = $translations;
        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->translations);
    }
}
