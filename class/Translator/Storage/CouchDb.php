<?php
namespace Translator\Storage;

use Doctrine\CouchDB\CouchDBClient;

class CouchDb
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
        $translations = array();

        if ($this->databaseExists()) {

            $query = $this->db->createViewQuery('main', 'by_namespace');
            $query->setKey($namespace);

            foreach ($query->execute() as $record) {
                $doc = $record['value'];
                $translations[$doc['key']] = $doc['translation'];
            }
        }

        return $translations;
    }

//----------------------------------------------------------------------------------------------------------------------

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
            'namespace' => $namespace,
        );
    }

    private static function uniqueDbId($key, $namespace)
    {
        return md5($key . $namespace);
    }
}
