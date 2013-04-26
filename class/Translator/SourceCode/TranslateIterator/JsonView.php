<?php

namespace Translator\SourceCode\TranslateIterator;

class JsonView implements TranslateIteratorInterface
{
    /**
     * @param string $filePath
     * @return array [I18N_KEY => [MESSAGE_ARGUMENTS]|null]
     */
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

        return $translations;
    }
}
