<?php

namespace Translator\SourceCode\TranslateIterator;

class MustacheView implements TranslateIteratorInterface
{
    private $translationTagName;

    public function __construct($translationTagName)
    {
        $this->translationTagName = $translationTagName;
    }

    /**
     * @param string $filePath
     * @return array [I18N_KEY => [MESSAGE_ARGUMENTS]|null]
     */
    public function select($filePath)
    {
        $template = file_get_contents($filePath);
        $translations = array();
        $tagQuoted = preg_quote($this->translationTagName, '@');

        preg_match_all(
            '@{{#' . $tagQuoted . '}}(.+?){{/' . $tagQuoted . '}}@is',
            $template, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $group) {
            if (preg_match('/^([^\\s]+)\\s+(.+)$/im', $group[1], $attrMatches)) {
                $key = $attrMatches[1];
                $params = array();
                if (preg_match_all('/([^\\s=]+)=(?:"([^"]*)"|\'([^\']*)\')/i', $attrMatches[2], $attrValuesMatches, PREG_SET_ORDER))
                {
                    foreach ($attrValuesMatches as $attrValuesMatch) {
                        $params[] = $attrValuesMatch[1];
                    }
                }
                $translations[$key] = ($params ? $params : null);
            } else {
                $key = $group[1];
                $translations[$key] = null;
            }
        }

        return $translations;
    }
}
