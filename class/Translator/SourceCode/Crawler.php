<?php

namespace Translator\SourceCode;

use Translator\Storage\StorageInterface;

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
                foreach ($this->translateFinder->select($path . '/' . $relativePath) as $keyWithNamespace => $parameters) {
                    $this->storage->registerTranslation(
                        self::keyPart($keyWithNamespace),
                        self::translate($keyWithNamespace, $translations),
                        self::namespacePart($keyWithNamespace)
                    );
                }
            }
        }
    }

//----------------------------------------------------------------------------------------------------------------------

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

    private static function keyPart($keyWithNamespace)
    {
        return strrpos($keyWithNamespace, ':') !== false ?
            substr($keyWithNamespace, strrpos($keyWithNamespace, ':') + 1) : $keyWithNamespace;
    }

    private static function namespacePart($keyWithNamespace)
    {
        return strrpos($keyWithNamespace, ':') !== false ?
            substr($keyWithNamespace, 0, strrpos($keyWithNamespace, ':')) : null;
    }

    private static function translate($keyWithNamespace, $translations)
    {
        $readFrom = $translations;
        foreach (array_filter(explode('/', self::namespacePart($keyWithNamespace))) as $ns) {
            if (array_key_exists($ns, $readFrom)) {
                $readFrom = $readFrom[$ns];
            } else {
                return null;
            }
        }
        return array_key_exists(self::keyPart($keyWithNamespace), $readFrom) ?
            $readFrom[self::keyPart($keyWithNamespace)] : null;
    }
}