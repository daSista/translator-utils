<?php
namespace Translator;

use \CouchDB\Http\ClientInterface;

class CouchDbStorage
{

    /**
     * @var \CouchDB\Connection
     */
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function registerTranslation($key, $pageId, $language)
    {
        $this->createDatabaseIfNeeded($language);

        try {
            $doc = $this->db->selectDatabase($language)->find(md5($key));
        } catch (\RuntimeException $e) {
            $doc = self::newDoc($key);
        }
        if (!array_key_exists($pageId, $doc['pageTranslations'])) {
            $doc['pageTranslations'][$pageId] = null;
        }

        if (isset($doc['_rev'])) {
            $this->db->selectDatabase($language)->update($doc['_id'], $doc);
        } else {
            $this->db->selectDatabase($language)->insert($doc);
        }
    }

    public function readTranslations($pageId, $language)
    {
        $translations = array();

        if ($this->db->hasDatabase($language)) {
            $view = $this->db->selectDatabase($language)
                ->find('_design/main/_view/by_page_id?key="' . $pageId . '"');

            foreach ($view['rows'] as $record) {
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

        }

        return $translations;
    }

//--------------------------------------------------------------------------------------------------

    private function createDatabaseIfNeeded($language)
    {
        if (!$this->db->hasDatabase($language)) {
            $this->db->createDatabase($language)->insert(self::dbSchema());
        }
    }

    private static function dbSchema()
    {
        return array(
            '_id' => '_design/main',
            'language' => 'javascript',
            'views' => array(
                "all_page_ids" => array(
                    "map" => self::mapPageIds(),
                    "reduce" => 'function (keys, values) {return null;}'
                ),
                'by_page_id' => array(
                    "map" => self::mapDocumentsByPageId()
                )
            )
        );
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

    private static function mapDocumentsByPageId()
    {
        return <<<'CouchJS'
function (doc) {
    var pageId;
    if (doc.pageTranslations) {
        for (pageId in doc.pageTranslations) {
            if (doc.pageTranslations.hasOwnProperty(pageId)) {
                emit(pageId, doc);
            }
        }
    }
}
CouchJS;
    }

    private static function mapPageIds()
    {
        return <<<'CouchJS'
function (doc) {
    var pageId;
    if (doc.pageTranslations) {
        for (pageId in doc.pageTranslations) {
            if (doc.pageTranslations.hasOwnProperty(pageId)) {
                emit(pageId, null);
            }
        }
    }
}
CouchJS;

    }
}
