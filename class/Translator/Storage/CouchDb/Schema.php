<?php
namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\View\DesignDocument;

class Schema implements DesignDocument
{
    /**
     * Get design doc code
     *
     * Return the view (or general design doc) code, which should be
     * committed to the database, which should be structured like:
     *
     * <code>
     *  array(
     *    "views" => array(
     *      "name" => array(
     *          "map"     => "code",
     *          ["reduce" => "code"],
     *      ),
     *      ...
     *    )
     *  )
     * </code>
     */
    public function getData()
    {
        return array(
            "lib" => array(
                "messageformat" => file_get_contents(__DIR__ . '/lib/messageformat/messageformat.js'),
                "locale" => file_get_contents(__DIR__ . '/lib/messageformat/locale/en.js')
            ),
            'views' => array(
                "all_namespaces" => array(
                    "map" => self::mapNamespaces(),
                    "reduce" => 'function (keys, values) {return null;}'
                ),
                'translations' => array(
                    "map" => self::mapDocumentsByNamespace()
                )
            ),
            'lists' => array(
                'compiled' => self::jsCompilationFunc()
            )
        );
    }

    private static function mapDocumentsByNamespace()
    {
        return <<<'CouchJS'
function (doc) {
    var i, combinedNs;
    if (doc.namespace) {
        combinedNs = '';
        for (i = 0; i < doc.namespace.length; i++) {
            combinedNs = combinedNs + doc.namespace[i];
            emit(combinedNs, doc);
            combinedNs = combinedNs + '/'
        }
    }
    emit('', doc);
}
CouchJS;
    }

    private static function mapNamespaces()
    {
        return <<<'CouchJS'
function (doc) {
    var i, combinedNs;
    if (doc.namespace) {
        combinedNs = '';
        for (i = 0; i < doc.namespace.length; i++) {
            combinedNs = combinedNs + doc.namespace[i];
            emit(combinedNs, null);
            combinedNs = combinedNs + '/'
        }
    }
}
CouchJS;

    }

    private static function jsCompilationFunc()
    {
        return <<<'CouchJS'
function(doc, req) {
    provides("js", function() {
        var MessageFormat = require('lib/messageformat'), js = '', mf = new MessageFormat('en'), string,
            declaredNamespaces = {}, declaredStrings = {};

        while (row = getRow()) {
            string = row.value;

            if (string.key && !declaredStrings[string._id]) {
                declaredStrings[string._id] = 1;

                if (string.namespace && string.namespace.length && !declaredNamespaces[string.namespace.join('/')]) {
                    js = js + 'g.i18n[\'' + string.namespace.join('/') + '\'] = {};\n';
                    declaredNamespaces[string.namespace.join('/')] = 1;
                }

                js = js + 'g.i18n';

                if (string.namespace && string.namespace.length) {
                    js = js + '[\'' + string.namespace.join('/') + '\']';
                }
                js = js + '[\'' + string.key + '\'] = '
                    + mf.precompile(mf.parse(string.translation))
                    + ';\n';
            }
        }

        return  '(function(g){' + 'g.i18n = {};\n' + js + '})(window);';
    });
}
CouchJS;

    }
}
