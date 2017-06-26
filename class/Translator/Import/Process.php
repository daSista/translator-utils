<?php

namespace Translator\Import;

use Translator\Storage\StorageInterface;
use Translator\MultiString;

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
        $count = 0;
        foreach ($source as $keyWithNamespace => $info) {
            list($translation, $description) = array_merge($info, array(null, null));
            $this->storage->setTranslationValue(MultiString::create($keyWithNamespace, $translation, $description));
            $count++;
        }
        return $count;
    }
}
