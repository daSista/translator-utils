<?php

namespace Translator\Import;

use Translator\Storage\StorageInterface;
use Translator\String;

class Process
{
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct($storage)
    {
        $this->storage = $storage;
    }

    public function run($source)
    {
        foreach ($source as $keyWithNamespace => $translation) {
            $this->storage->registerString(String::create($keyWithNamespace, $translation));
        }
    }
}