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
            'views' => array(
                "all_namespaces" => array(
                    "map" => self::mapNamespaces(),
                    "reduce" => 'function (keys, values) {return null;}'
                ),
                'by_namespace' => array(
                    "map" => self::mapDocumentsByNamespace()
                )
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

}
