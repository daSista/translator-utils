<?php

namespace Translator\SourceCode\TranslateIterator;

interface TranslateIteratorInterface
{
    /**
     * @param string $filePath
     * @return array [I18N_KEY => [MESSAGE_ARGUMENTS]|null]
     */
    public function select($filePath);
}