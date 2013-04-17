<?php

namespace Translator\Import\Source;

class PortableObject implements SourceInterface
{
    private $translations = array();

    public function getIterator()
    {
        return new \ArrayIterator($this->translations);
    }

    /**
     * @param string $filePath
     * @return self
     */
    public function select($filePath)
    {
        $this->translations = array();

        $f = fopen($filePath, 'r');

        $buffer = '';
        while (($str = fgets($f)) !== false) {
            if (strlen(trim($str))) {
                $buffer .= $str;
            } else {
                $this->translations = array_merge(
                    $this->translations,
                    self::interpret($buffer)
                );
                $buffer = '';
            }
        }
        $this->translations = array_merge(
            $this->translations,
            self::interpret($buffer)
        );
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function interpret($buffer)
    {
        list($buffer, $msgid) = self::readId($buffer);
        list($buffer, $msgstr) = self::readStr($buffer);
        list($buffer, $msgctxt) = self::readContext($buffer);
        list($buffer, $comment) = self::readComment($buffer);
        $info = array($msgstr);
        if (!is_null($comment)) {
            $info[] = $comment;
        }
        return $msgid ? array($msgctxt ? $msgctxt . ':' . $msgid : $msgid => $info) : array();
    }

    private static function readId($buffer)
    {
        $lines = explode("\n", $buffer);
        foreach ($lines as $idx => $line) {
            if (preg_match('/^\s*msgid\s+"(.*)"\s*$/i', $line, $matches)) {
                unset($lines[$idx]);
                return array(join("\n", $lines), stripslashes($matches[1]));
            }
        }
        return array($buffer, null);
    }

    private static function readStr($buffer)
    {
        $lines = explode("\n", $buffer);

        // skip to msgstr definition
        $msgStartIndex = null;
        foreach ($lines as $idx => $line) {
            if (stripos($line, 'msgstr "') !== false) {
                $msgStartIndex = $idx;
                break;
            }
        }
        if (!is_null($msgStartIndex)) {
            // read first line
            $string = preg_match('/^\s*msgstr\s+"(.*)"\s*$/i', $lines[$msgStartIndex], $matches) ?
                stripslashes($matches[1]) : "";
            unset($lines[$msgStartIndex]);

            // read and concatenate the rest
            for($i = $msgStartIndex + 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (!strlen($line) || (strpos($line, '"') !== 0)) {
                    break;
                } else {
                    $string .= stripslashes(str_replace('\\n', "\n", trim($line, '"')));
                }
            }
        } else {
            $string = null;
        }

        return array(join("\n", $lines), $string);
    }

    private static function readContext($buffer)
    {
        $lines = explode("\n", $buffer);
        foreach ($lines as $idx => $line) {
            if (preg_match('/^\s*msgctxt\s+"(.*)"\s*$/i', $line, $matches)) {
                unset($lines[$idx]);
                return array(join("\n", $lines), stripslashes($matches[1]));
            }
        }

        return array(join("\n", $lines), null);
    }

    private static function readComment($buffer) {
        $lines = explode("\n", $buffer);
        $commentLines = array();
        foreach ($lines as $idx => $line) {
            if (preg_match('/^\s*#\. (.*)$/i', $line, $matches)) {
                unset($lines[$idx]);
                $commentLines[] = $matches[1];
            }
        }

        return array(join("\n", $lines), count($commentLines) ? join("\n", $commentLines) : null);
    }
}