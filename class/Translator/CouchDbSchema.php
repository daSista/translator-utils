<?php
namespace Translator;

use Doctrine\CouchDB\View\DesignDocument;

class CouchDbSchema implements DesignDocument
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
