<?php
namespace Translator;

use Doctrine\CouchDB\CouchDBClient;

class CouchDbStorage
{
    /**
     * @var \Doctrine\CouchDB\CouchDBClient
     */
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function registerTranslation($key, $pageId)
    {
        $this->createDatabaseIfNeeded();
        $response = $this->db->findDocument(md5($key));
        $doc = $response->status === 404 ? self::newDoc($key) : $response->body;

        if (!array_key_exists($pageId, $doc['pageTranslations'])) {
            $doc['pageTranslations'][$pageId] = null;
        }

        if (isset($doc['_rev'])) {
            $this->db->putDocument($doc, $doc['_id']);
        } else {
            $this->db->postDocument($doc);
        }
    }

    public function readTranslations($pageId)
    {
        $translations = array();

        $query = $this->db->createViewQuery('main', 'by_page_id');
        $query->setKey($pageId);

        foreach ($query->execute() as $record) {
            $doc = $record['value'];
            $value = $doc['key'];
            if (isset($doc['defaultTranslation'])) {
                $value = $doc['defaultTranslation'];
            }
            if (isset($doc['pageTranslations'][$pageId])) {
                $value = $doc['pageTranslations'][$pageId];
            }
            $translations[$doc['key']] = $value;
        }

        return $translations;
    }

//--------------------------------------------------------------------------------------------------

    private function createDatabaseIfNeeded()
    {
        if (!in_array($this->db->getDatabase(), $this->db->getAllDatabases())) {
            $this->db->createDatabase($this->db->getDatabase());
            $this->db->createDesignDocument(
                'main',
                new CouchDbSchema()
            );
        }
    }

    private static function newDoc($key)
    {
        return array(
            '_id' => md5($key),
            'key' => $key,
            'defaultTranslation' => null,
            'pageTranslations' => array()
        );
    }
}
