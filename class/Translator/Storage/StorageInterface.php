<?php

namespace Translator\Storage;

use Translator\String;

interface StorageInterface
{
    const BEHAVIOR_RESPECT_DATABASE_CONTENTS = 'BEHAVIOR_RESPECT_DATABASE_CONTENTS';
    const BEHAVIOR_OVERWRITE_DATABASE_CONTENTS = 'BEHAVIOR_OVERWRITE_DATABASE_CONTENTS';

    /**
     * @param String $string
     * @return void
     */
    public function ensurePresence($string);

    /**
     * @param String $string
     * @return void
     */
    public function setTranslationValue($string);

    /**
     * @param null|string $namespace
     * @return array key to value map
     */
    public function mappedTranslations($namespace = null);
}