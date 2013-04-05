<?php

namespace Translator\SourceCode\TranslateIterator;

interface TranslateIteratorInterface extends \IteratorAggregate
{
    /**
     * @param string $filePath
     * @return self
     */
    public function select($filePath);
}