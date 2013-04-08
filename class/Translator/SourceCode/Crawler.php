<?php

namespace Translator\SourceCode;

use Translator\Storage\StorageInterface;
use Translator\String;

class Crawler
{
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
     */
    public function __construct($storage, $translateFinder)
    {
        $this->storage = $storage;
        $this->translateFinder = $translateFinder;
    }

    public function collectTranslations(array $pathsToSearchIn, array $translations, $fileExt)
    {
        foreach ($pathsToSearchIn as $path) {
            foreach ($this->readDir($path) as $relativePath) {
                if (preg_match('/' . preg_quote($fileExt) . '$/', $relativePath)) {
                    $this->registerAllTranslations($translations, $path . '/' . $relativePath);
                }
            }
        }
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param array $translations
     * @param $path
     */
    private function registerAllTranslations(array $translations, $path)
    {
        foreach ($this->translateFinder->select($path) as $keyWithNamespace => $parameters) {
            $this->storage->registerString(String::find($keyWithNamespace, $translations));
        }
    }

    private function readDir($path, $exclude = ".|..", $relativePart = '.') {
        $path = rtrim($path, "/") . "/";
        $folder_handle = opendir($path);
        $exclude_array = explode("|", $exclude);
        $result = array();
        while(false !== ($filename = readdir($folder_handle))) {
            if(!in_array(strtolower($filename), $exclude_array)) {
                if(is_dir($path . $filename . "/")) {
                    // Need to include full "path" or it's an infinite loop
                    $result = array_merge(
                        $result,
                        $this->readDir($path . $filename . "/", $exclude, $relativePart . '/' . $filename)
                    );
                } else {
                    $result[] = $relativePart . '/' . $filename;
                }
            }
        }
        return $result;
    }
}