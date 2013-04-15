<?php

use Translator\Storage\CouchDb;
use Translator\String;
use Translator\Test\CouchDbTestCase;
use Translator\Storage\CouchDb\Schema;
use Doctrine\CouchDB\HTTP\SocketClient as HttpClient;

class CouchDbCompiledTranslationsViewTest extends CouchDbTestCase
{
    public function testReturnsCompiledJavascript()
    {
        self::storage()->registerString(String::create('validation:email', 'Email'));
        $http = new HttpClient();

        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/js/translations', null, true);

        $this->assertEquals(
            <<<'JS'
(function(g){g.i18n = {};
g.i18n['validation'] = {};
g.i18n['validation']['email'] = function(d){
var r = "";
r += "Email";
return r;
};
})(window);
JS
            ,
            $response->body
        );
    }

    public function testLoadsCorrectLocaleSettings()
    {
        $ruStorage = new CouchDb(self::db(), 'ru_RU');

        $ruStorage->registerString(String::create('num', '{NUM, plural, one: {одна овца} few {# овцы} other {# овец}}'));
        $http = new HttpClient();

        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/js/translations', null, true);

        $this->assertContains('MessageFormat.locale["ru"]', $response->body);

    }

    public function testCompilesPOTextFile()
    {
        self::fillInStorage();
        self::storage()->registerString(String::create('toBeEnquoted', 'The "String" \\Here'));
        self::storage()->registerString(String::create('multiline', <<<TEXT
first line
second line

fourth line
TEXT
        ));

        $http = new HttpClient();
        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/po/translations', null, true);

        $this->assertEquals(
            <<<'PO'

msgid "toBeEnquoted"
msgstr "The \"String\" \\Here"

msgctxt "validation"
msgid "email"
msgstr "Email"

msgctxt "pager"
msgid "totalAmountOfPages"
msgstr "Total %d page(s)"

msgctxt "validation/error"
msgid "emailFormat"
msgstr "Email format is incorrect"

msgctxt "validation/error"
msgid "notEmpty"
msgstr "Should be not empty"

msgctxt "pager"
msgid "pageXFromY"
msgstr "Page %d from $d"

msgid "yes"
msgstr "Yes"

msgid "multiline"
msgstr ""
"first line\n"
"second line\n"
"\n"
"fourth line"

PO
            ,
            $response->body
        );
    }
}