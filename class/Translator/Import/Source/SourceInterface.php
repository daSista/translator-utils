<?php

namespace Translator\Import\Source;

interface SourceInterface extends \IteratorAggregate
{
    /**
     * @param string $filePath
     * @return self
     */
    public function select($filePath);

}