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
            '@{{#' . $tagQuoted . '}}([^{]+){{/' . $tagQuoted . '}}@is',
            $template, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $group) {
            $key = $group[1];
            $translations[$key] = null;
        }

        return $translations;
    }
}
