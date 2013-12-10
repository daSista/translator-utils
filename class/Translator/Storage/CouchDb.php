<?php
namespace Translator\Storage;

use Doctrine\CouchDB\CouchDBClient;
use Translator\String;

class CouchDb implements StorageInterface
{
    private $locale;

    /**
     * @var CouchDBClient
     */
    private $db;

    public function __construct($dbConnection, $locale)
    {
        $this->db = $dbConnection;
        $this->locale = $locale;
    }

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

    public function mappedTranslations($namespace = null)
    {
        if ($this->databaseExists()) {
            return $this->queryView($namespace ?: '');
        }

        return array();
    }

    /**
     * @param string $hash
     * @return array
     */
    public function findDocument($hash)
    {
        $query = $this->db->createViewQuery('main', 'find');
        $query->setKey($hash);
        $response = $query->execute();
        return count($response) ? $response[0]['value'] : array();
    }

//--------------------------------------------------------------------------------------------------

    /**
     * @param String $string
     * @param $behavior
     */
    private function registerString($string, $behavior)
    {
        $this->createDatabaseIfNeeded();

        $existingDoc = $this->findDocument($string->hash());

        $doc = empty($existingDoc) ?
            $string->asDocument() : self::mergeStrings($existingDoc, $string->asDocument(), $behavior);

        if (isset($doc['_id'])) {
            $this->db->putDocument($doc, $doc['_id']);
        } else {
            $this->db->postDocument($doc);
        }
    }

    private function queryView($namespace)
    {
        $translations = array();

        $query = $this->db->createViewQuery('main', 'translations');
        $query->setKey($namespace);

        foreach ($query->execute() as $record) {
            $doc = $record['value'];
            $translations = array_merge($translations, self::singleTranslation($doc));
        }

        return $translations;
    }

    /**
     * @param $doc
     * @return mixed
     */
    private static function singleTranslation($doc)
    {
        return array(
            ($doc['namespace'] ? join('/', $doc['namespace']) . ':' : '') . $doc['key'] => $doc['translation']
        );
    }

    private function createDatabaseIfNeeded()
    {
        if (!$this->databaseExists()) {
            $this->db->createDatabase($this->db->getDatabase());
            $this->db->createDesignDocument('main', new CouchDb\Schema($this->locale));
        }
    }

    private function databaseExists()
    {
        return in_array($this->db->getDatabase(), $this->db->getAllDatabases());
    }

    protected static function mergeStrings($existing, $new, $behavior)
    {
        if ($behavior === self::BEHAVIOR_RESPECT_DATABASE_CONTENTS) {
            $accSrc = self::accumulatedSource($existing, $new);

            if (array_key_exists('description', $existing) && !strlen($existing['description'])) {
                unset($existing['description']);
            }

            return array_merge($new, $existing, array('source' => $accSrc));
        } else {
            return array_merge($existing, $new);
        }
    }

    private static function accumulatedSource($doc1, $doc2)
    {
        $s1 = isset($doc1['source']) ? $doc1['source'] : array();
        $s2 = isset($doc2['source']) ? $doc2['source'] : array();

        return array_values(array_unique(array_merge($s1, $s2)));
    }
}
