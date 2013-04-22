<?php

namespace Translator\SourceCode\TranslateIterator;

class AngularView implements TranslateIteratorInterface
{
    /**
     * @param string $filePath
     * @return array [I18N_KEY => [MESSAGE_ARGUMENTS]|null]
     */
    public function select($filePath)
    {
        $template = file_get_contents($filePath);
        $translations = array();

        preg_match_all("/{{\\s*'([^']+)'\\s*\\|\\s*i18n\\s*:?\\s*([^}]*)}}/", $template, $matches, PREG_SET_ORDER);

        foreach ($matches as $group) {
            $translations[$group[1]] = self::enumerateParameters($group[2]) ?: null;
        }

        return $translations;
    }

    private static function enumerateParameters($colonSeparatedString)
    {
        return preg_split('/\\s*:\\s*/', trim($colonSeparatedString, ' '), -1, PREG_SPLIT_NO_EMPTY);
    }
}
