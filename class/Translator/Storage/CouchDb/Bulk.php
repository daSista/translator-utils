<?php

namespace Translator\Storage\CouchDb;

use Translator\Storage\CouchDb;
use Translator\MultiString;

class Bulk extends CouchDb
{
    private $stringsStack = array(
        self::BEHAVIOR_RESPECT_DATABASE_CONTENTS => array(),
        self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS => array(),
    );

    /**
     * @param String $string
     * @return void
     */
    public function ensurePresence($string)
    {
        $this->registerString($string, self::BEHAVIOR_RESPECT_DATABASE_CONTENTS);
    }

    /**
     * @param String $string
     * @return void
     */
    public function setTranslationValue($string)
    {
        $this->registerString($string, self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS);
    }

    public function commit()
    {
        foreach (array(
                     self::BEHAVIOR_RESPECT_DATABASE_CONTENTS,
                     self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS
                 ) as $behavior) {

            if (count($this->stringsStack[$behavior]) > 0) {

                $existingStrings = $this->loadExisting($this->stringsStack[$behavior]);
                $bulkUpdater = $this->db->createBulkUpdater();

                /** @var String $string */
                foreach ($this->stringsStack[$behavior] as $string) {
                    $existingStrings[$string->hash()] = $this->createDocument($string, $behavior, $existingStrings);
                }

                foreach ($existingStrings as $document) {
                    $bulkUpdater->updateDocument($document);
                }

                $bulkUpdater->execute();
                $this->stringsStack[$behavior] = array();
            }
        }
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param String $string
     * @param $behavior
     */
    private function registerString($string, $behavior)
    {
        if (count($this->stringsStack[$behavior]) >= 100) {
            $this->commit();
        }
        $this->stringsStack[$behavior][] = $string;
    }

    /**
     * @param String $string
     * @param string $behavior self::BEHAVIOR_*
     * @param array $existingStrings
     * @return array
     */
    private function createDocument($string, $behavior, $existingStrings)
    {
        return array_key_exists($string->hash(), $existingStrings) ?
            self::mergeStrings($existingStrings[$string->hash()], $string->asDocument(), $behavior)
            :
            $string->asDocument();
    }

    /**
     * @param $strings
     * @return array
     */
    private function loadExisting($strings)
    {
        $query = $this->db->createViewQuery('main', 'find');
        $query->setKeys(
            array_map(
                function ($string) {
                    return $string->hash();
                },
                $strings
            )
        );

        $existingStrings = array();
        foreach ($query->execute() as $record) {
            $doc = $record['value'];
            $string = MultiString::create(
                ($doc['namespace'] ? join('/', $doc['namespace']) . ':' : '') . $doc['key'],
                $doc['translation'],
                array_key_exists('description', $doc) ? $doc['description'] : null,
                array_key_exists('source', $doc) ? $doc['source'] : null
            );

            $existingStrings[$string->hash()] = $doc;
        }
        return $existingStrings;
    }
}
