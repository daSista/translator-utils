<?php
namespace Translator;

use \CouchDB\Http\ClientInterface;

class CouchDbStorage
{
    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * @var \CouchDB\Connection
     */
    private $db;

    public function __construct($dbConnection, $dbPrefix = 'i18n')
    {
        $this->db = $dbConnection;
        $this->dbPrefix = $dbPrefix;
    }

    public function registerTranslation($key, $pageId, $language)
    {
        $this->createDatabaseIfNeeded($language);

        try {
            $doc = $this->database($language)->find(md5($key));
        } catch (\RuntimeException $e) {
            $doc = self::newDoc($key);
        }
        if (!array_key_exists($pageId, $doc['pageTranslations'])) {
            $doc['pageTranslations'][$pageId] = null;
        }

        if (isset($doc['_rev'])) {
            $this->database($language)->update($doc['_id'], $doc);
        } else {
            $this->database($language)->insert($doc);
        }
    }

    public function readTranslations($pageId, $language)
    {
        $translations = array();

        if ($this->hasDatabase($language)) {
            $view = $this->database($language)
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
        if (!$this->hasDatabase($language)) {
            $this->createDatabase($language)->insert(self::dbSchema());
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

    /**
     * @param string $language
     * @return \CouchDB\Database
     */
    private function database($language)
    {
        return $this->db->selectDatabase($this->dbPrefix . '_' . $language);
    }

    private function hasDatabase($language)
    {
        return $this->db->hasDatabase($this->dbPrefix . '_' . $language);
    }

    private function createDatabase($language)
    {
        return $this->db->createDatabase($this->dbPrefix . '_' . $language);
    }
}
