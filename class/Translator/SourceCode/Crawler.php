<?php

namespace Translator\SourceCode;

use Translator\Storage\StorageInterface;
use Translator\String;

class Crawler
{
    /**
     * @var array
     */
    private $contextDescription;

    /**
     * @var array
     */
    private $translations;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var TranslateIterator\TranslateIteratorInterface
     */
    private $translateFinder;

    /**
     * @param StorageInterface $storage
     * @param TranslateIterator\TranslateIteratorInterface $translateFinder
     * @param array $translations translations can be provided here
     * @param array $contextDescription context can be described here
     */
    public function __construct($storage, $translateFinder, $translations = array(), $contextDescription = array())
    {
        $this->storage = $storage;
        $this->translateFinder = $translateFinder;
        $this->translations = $translations;
        $this->contextDescription = $contextDescription;
    }

    public function collectTranslations(array $pathsToSearchIn, $fileExt)
    {
        foreach ($pathsToSearchIn as $path) {
            foreach ($this->readDir($path) as $filename) {
                if (preg_match('/' . preg_quote($fileExt) . '$/', $filename)) {
                    $this->registerAllTranslations(
                        $this->translations,
                        $this->contextDescription,
                        $filename
                    );
                }
            }
        }
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param array $translations
     * @param array $contextDescriptions
     * @param $path
     */
    private function registerAllTranslations(array $translations, array $contextDescriptions, $path)
    {
        foreach ($this->translateFinder->select($path) as $keyWithNamespace => $parameters) {
            $this->storage->registerString(
                String::find($keyWithNamespace, $translations, $contextDescriptions),
                StorageInterface::BEHAVIOR_RESPECT_DATABASE_CONTENTS
            );
        }
    }

    private function readDir($path) {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $result = array();
        foreach ($iterator as $filename) {
            if (is_file($filename)) {
                $result[] = $filename;
            }
        }
        return $result;
    }
}
