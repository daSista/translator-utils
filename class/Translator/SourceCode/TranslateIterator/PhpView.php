<?php

namespace Translator\SourceCode\TranslateIterator;

class PhpView implements TranslateIteratorInterface
{
    /**
     * @param string $filePath
     * @return array [I18N_KEY => [MESSAGE_ARGUMENTS]|null]
     */
    public function select($filePath)
    {
        $template = file_get_contents($filePath);
        $translations = array();

        preg_match_all(
            "/\\$[a-zA-Z_][a-zA-Z0-9_]*\\->translate\\(\\s*('[^']+'|\"[^\"]+\")\\s*([^\\)]*)\\s*\\)/is",
            $template, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $group) {
            $key = substr($group[1], 1, -1);
            $translations[$key] = self::enumerateParameters($group[2]) ?: null;
        }

        return $translations;

    }

    private static function enumerateParameters($str)
    {
        preg_match_all('/\\->([a-zA-Z_][a-zA-Z0-9_]*)/si', $str, $matches);
        return $matches[1] ?: null;
    }
}
