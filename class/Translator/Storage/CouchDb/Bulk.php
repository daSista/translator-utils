<?php

namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\CouchDBClient;
use Translator\Storage\CouchDb;

class Bulk extends CouchDb
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var CouchDBClient
     */
    private $db;

    private $stringsStack = array(
        self::BEHAVIOR_RESPECT_DATABASE_CONTENTS => array(),
        self::BEHAVIOR_OVERWRITE_DATABASE_CONTENTS => array(),
    );

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
        throw new \RuntimeException('Not implemented');
    }

    public function commit()
    {
        $documents = $this->createDocuments($this->stringsStack[self::BEHAVIOR_RESPECT_DATABASE_CONTENTS]);

        $bulkUpdater = $this->db->createBulkUpdater();

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

    private function createDocuments($strings)
    {
        $query = $this->db->createViewQuery('main', 'find');
        $query->setKeys(array_map(function ($string) { return $string->hash();}, $strings));




        return array();
    }
}