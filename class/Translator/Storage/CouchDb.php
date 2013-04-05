<?php
namespace Translator\Storage;

use Doctrine\CouchDB\CouchDBClient;

class CouchDb implements StorageInterface
{
    /**
     * @var \Doctrine\CouchDB\CouchDBClient
     */
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function registerTranslation($key, $translation, $namespace = null)
    {
        $this->createDatabaseIfNeeded();

        /** @var $response \Doctrine\CouchDB\HTTP\Response */
        $response = $this->db->findDocument(self::uniqueDbId($key, $namespace));
        $doc = $response->status === 404 ? self::newDoc($key, $translation, $namespace) : $response->body;

        if (isset($doc['_rev'])) {
            $this->db->putDocument($doc, $doc['_id']);
        } else {
            $this->db->postDocument($doc);
        }
    }

    public function readTranslations($namespace = null)
    {
        if ($this->databaseExists()) {
            return $namespace ? $this->queryView($namespace) :  $this->queryAllDocs();
        }

        return array();
    }

//----------------------------------------------------------------------------------------------------------------------

    private function queryView($namespace)
    {
        $translations = array();

        $query = $this->db->createViewQuery('main', 'by_namespace');
        $query->setKey($namespace);

        foreach ($query->execute() as $record) {
            $doc = $record['value'];
            $translations = array_merge($translations, self::singleTranslation($doc));
        }

        return $translations;
    }

    private function queryAllDocs()
    {
        $translations = array();

        /** @var $response \Doctrine\CouchDB\HTTP\Response */
        $response = $this->db->allDocs();
        if ($response->status === 200) {
            foreach ($response->body['rows'] as $row) {
                if (array_key_exists('translation', $row['doc'])) {
                    $translations = array_merge($translations, self::singleTranslation($row['doc']));
                }
            }
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
            $this->db->createDesignDocument('main', new CouchDb\Schema());
        }
    }

    private function databaseExists()
    {
        return in_array($this->db->getDatabase(), $this->db->getAllDatabases());
    }

    private static function newDoc($key, $translation, $namespace)
    {
        return array(
            '_id' => self::uniqueDbId($key, $namespace),
            'key' => $key,
            'translation' => $translation,
            'namespace' => array_filter(explode('/', $namespace)) ?: null,
        );
    }

    private static function uniqueDbId($key, $namespace)
    {
        return md5($key . $namespace);
    }
}
