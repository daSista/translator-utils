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
     * @param string $behavior
     */
    public function registerString($string, $behavior = self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS)
    {
        $this->createDatabaseIfNeeded();

        /** @var $response \Doctrine\CouchDB\HTTP\Response */
        $response = $this->db->findDocument($string->id());

        $doc = (
            $response->status === 404 ?
            $string->asDocument() :
            self::mergeStrings($response->body, $string->asDocument(), $behavior)
        );

        if (isset($doc['_rev'])) {
            $this->db->putDocument($doc, $doc['_id']);
        } else {
            $this->db->postDocument($doc);
        }
    }

    public function mappedTranslations($namespace = null)
    {
        if ($this->databaseExists()) {
            return $this->queryView($namespace ?: '');
        }

        return array();
    }

//--------------------------------------------------------------------------------------------------

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
            (
                $doc['namespace'] ?
                join('/', $doc['namespace']) . ':'
                : ''
            ) . $doc['key'] => $doc['translation']
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

    private static function mergeStrings($existing, $new, $behavior)
    {
        if ($behavior === self::BEHAVIOR_RESPECT_DATABASE_CONTENTS) {
            if (array_key_exists('description', $existing) && !strlen($existing['description'])) {
                unset($existing['description']);
            }
            return array_merge($new, $existing);
        } else {
            return array_merge($existing, $new);
        }
    }
}
