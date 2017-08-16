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
            '@{{#\\s?' . $tagQuoted . '\\s?}}(.+?){{/\\s?' . $tagQuoted . '\\s?}}@is',
            $template, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $group) {
            if (preg_match('/^([^\\s{]+)\\s+(.+)$/im', $group[1], $attrMatches)) {
                $key = $attrMatches[1];
                $parameters = self::collectParameters($attrMatches[2]);
            } else {
                $key = $group[1];
                $parameters = null;
            }

            if (!preg_match('/{{.*}}/', $key)) {
                $translations[$key] = $parameters;
            }

        }

        return $translations;
    }

    /**
     * @param string $theTail
     * @return array
     */
    private static function collectParameters($theTail)
    {
        $attrValuesMatches = array();
        if (preg_match_all('/([^\\s=]+)=(?:"([^"]*)"|\'([^\']*)\')/i', $theTail, $attrValuesMatches, PREG_SET_ORDER)) {
            $params = array();
            foreach ($attrValuesMatches as $attrValuesMatch) {
                $params[] = $attrValuesMatch[1];
            }
            return $params;
        }
        return null;
    }

}
