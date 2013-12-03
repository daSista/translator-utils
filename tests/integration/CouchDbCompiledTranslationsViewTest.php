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
        self::storage()->setTranslationValue(String::create('validation:email', 'Email'));
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

    public function testSurvivesInvalidICUExpressions()
    {
        self::storage()->setTranslationValue(String::create('validation:email', "{,,incorrect 'ICU'}"));
        $http = new HttpClient();

        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/js/translations', null, true);

        $this->assertEquals(
            <<<'JS'
(function(g){g.i18n = {};
g.i18n['validation'] = {};
g.i18n['validation']['email'] = function(d){ return '{,,incorrect \'ICU\'}'; };
})(window);
JS
            ,
            $response->body
        );

    }

    public function testLoadsCorrectLocaleSettings()
    {
        $ruStorage = new CouchDb(self::db(), 'ru_RU');

        $ruStorage->setTranslationValue(String::create('num', '{NUM, plural, one: {одна овца} few {# овцы} other {# овец}}'));
        $http = new HttpClient();

        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/js/translations', null, true);

        $this->assertContains('MessageFormat.locale["ru"]', $response->body);

    }

    public function testCompilesPOTextFile()
    {
        self::fillInStorage();
        self::storage()->setTranslationValue(String::create('toBeEnquoted', 'The "String" \\Here'));
        self::storage()->setTranslationValue(String::create('multiline', <<<TEXT
first line
second line

fourth line
TEXT
        ));

        $http = new HttpClient();
        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/po/translations', null, true);

        $this->assertThat(
            $response->body,
            $this->logicalAnd(
                $this->stringContains(
                    <<<'PO'

msgid "multiline"
msgstr ""
"first line\n"
"second line\n"
"\n"
"fourth line"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgctxt "pager"
msgid "pageXFromY"
msgstr "Page %d from $d"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgctxt "pager"
msgid "totalAmountOfPages"
msgstr "Total %d page(s)"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgid "toBeEnquoted"
msgstr "The \"String\" \\Here"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgctxt "validation/error"
msgid "emailFormat"
msgstr "Email format is incorrect"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgctxt "validation/error"
msgid "notEmpty"
msgstr "Should be not empty"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgctxt "validation"
msgid "email"
msgstr "Email"

PO
                ),
                $this->stringContains(
                    <<<'PO'

msgid "yes"
msgstr "Yes"

PO
                )
            )
        );

    }

    public function testIncludesDescriptionIntoPOComments()
    {
        self::storage()->setTranslationValue(
            String::create('Unknown system error', 'Error desconegut del sistema', <<<'DESC'
this string needed
for special case
DESC
            )
        );
        $http = new HttpClient();
        $response = $http->request('GET', '/' . TEST_COUCHDB_NAME . '/_design/main/_list/po/translations', null, true);

        $this->assertContains(<<<'PO'

#. this string needed
#. for special case
msgid "Unknown system error"
msgstr "Error desconegut del sistema"

PO
            ,
            $response->body
        );
    }
}
