<?php

namespace Translator\Test;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\HTTP\SocketClient as HttpClient;
use Translator\Storage\CouchDb;
use Translator\String;

abstract class CouchDbTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setup()
    {
        exec('translator-couch ' . TEST_COUCHDB_PREFIX . ' ' . TEST_COUCHDB_LOCALE);
    }

    protected function tearDown()
    {
        self::db()->deleteDatabase(TEST_COUCHDB_NAME);
    }

//--------------------------------------------------------------------------------------------------

    protected static function db()
    {
        return new CouchDBClient(new HttpClient(), TEST_COUCHDB_NAME);
    }

    protected static function fillInStorage()
    {
        self::storage()->setTranslationValue(String::create('validation:email', 'Email'));
        self::storage()->setTranslationValue(String::create('validation/error:notEmpty', 'Should be not empty'));
        self::storage()->setTranslationValue(String::create('validation/error:emailFormat', 'Email format is incorrect'));
        self::storage()->setTranslationValue(String::create('pager:pageXFromY', 'Page %d from $d'));
        self::storage()->setTranslationValue(String::create('pager:totalAmountOfPages', 'Total %d page(s)'));
        self::storage()->setTranslationValue(String::create('yes', 'Yes'));
    }

    protected static function storage()
    {
        return new CouchDb(self::db());
    }

    protected static function bulkStorage()
    {
        return new CouchDb\Bulk(self::db());
    }
}
