<?php

namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\CouchDBClient;
use Translator\Storage\CouchDb;
use Translator\String;

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
        $this->createDatabaseIfNeeded();

        $documents = array_merge(
            $this->createDocuments($this->stringsStack[self::BEHAVIOR_RESPECT_DATABASE_CONTENTS],
                self::BEHAVIOR_RESPECT_DATABASE_CONTENTS),
            $this->createDocuments($this->stringsStack[self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS],
                self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS)
        );

        $bulkUpdater = $this->db->createBulkUpdater();

        foreach ($documents as $doc) {
            $bulkUpdater->updateDocument($doc);
        }

        $bulkUpdater->execute();
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param String $string
     * @param $behavior
     */
    private function registerString($string, $behavior)
    {
        $this->stringsStack[$behavior][] = $string;
    }

    private function createDocuments($strings, $behavior)
    {
        $query = $this->db->createViewQuery('main', 'find');
        $query->setKeys(array_map(function ($string) { return $string->hash();}, $strings));

        $existingStrings = array();
        foreach ($query->execute() as $record) {
            $doc = $record['value'];
            $string = String::create(
                ($doc['namespace'] ? join('/', $doc['namespace']) . ':' : '') . $doc['key'],
                $doc['translation'],
                array_key_exists('description', $doc) ? $doc['description'] : null,
                array_key_exists('source', $doc) ? $doc['source'] : null
            );

            $existingStrings[$string->hash()] = $doc;
        }

        $merged = array();
        foreach ($strings as $string) {
            $doc = array_key_exists($string->hash(), $existingStrings) ?
                self::mergeStrings($existingStrings[$string->hash()], $string->asDocument(), $behavior)
                :
                $string->asDocument();

            $merged[] = $doc;
        }

        return $merged;
    }
}